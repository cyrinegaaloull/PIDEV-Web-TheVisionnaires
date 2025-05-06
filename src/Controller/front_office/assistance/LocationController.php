<?php

namespace App\Controller\front_office\assistance;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Psr\Cache\CacheItemPoolInterface;

class LocationController extends AbstractController
{
    private $httpClient;
    private $validator;
    private $logger;
    private $cache;
    private $foursquareApiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        CacheItemPoolInterface $cache,
        string $foursquareApiKey
    ) {
        $this->httpClient = $httpClient;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->foursquareApiKey = $foursquareApiKey;
    }

    /**
     * Display and handle location search
     */
    #[Route('/locations', name: 'locations')]
    public function index(Request $request): Response
    {
        $location = '';
        $locationsData = [];
        $errorMessage = '';

        if ($request->isMethod('POST')) {
            $location = trim($request->request->get('location', ''));
            $location = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');

            // Validate input
            $constraint = new Assert\Collection([
                'location' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 100]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\s,.-]+$/',
                        'message' => 'Location can only contain letters, numbers, spaces, commas, periods, and hyphens.',
                    ]),
                ],
            ]);

            $errors = $this->validator->validate(['location' => $location], $constraint);
            if (count($errors) > 0) {
                $errorMessage = 'Please enter a valid location (2-100 characters).';
            } else {
                // Check cache
                $cacheKey = 'foursquare_search_' . md5($location . '_categories_10000_13000');
                $cachedItem = $this->cache->getItem($cacheKey);

                if ($cachedItem->isHit()) {
                    $locationsData = $cachedItem->get();
                } else {
                    // Foursquare API request
                    $url = sprintf(
                        'https://api.foursquare.com/v3/places/search?near=%s&limit=5',
                        urlencode($location)
                    );


                    try {
                        $response = $this->httpClient->request('GET', $url, [
                            'headers' => [
                                'Authorization' => $this->foursquareApiKey,
                                'accept' => 'application/json',

                            ],
                        ]);

                        $statusCode = $response->getStatusCode();
                        if ($statusCode === 200) {
                            $data = $response->toArray();
                            $locationsData = $data['results'] ?? [];
                            if (empty($locationsData)) {
                                $errorMessage = 'No locations found for "' . htmlspecialchars($location) . '".';
                            } else {
                                // Save to cache
                                $cachedItem->set($locationsData);
                                $this->cache->save($cachedItem);
                            }
                        } else {
                            $this->logger->error('Foursquare API error. Status Code: ' . $statusCode);
                            $errorMessage = 'Failed to retrieve location data. (API error ' . $statusCode . ')';
                        }
                    } catch (\Exception $e) {
                        $this->logger->error('Foursquare API request failed: ' . $e->getMessage());
                        $errorMessage = 'Failed to retrieve location data. Please try again later.';
                    }
                }

                // Fetch related places for recommendations
                if (!empty($locationsData)) {
                    foreach ($locationsData as &$locationData) {
                        $fsqId = $locationData['fsq_id'] ?? null;
                        if ($fsqId) {
                            $relatedCacheKey = 'foursquare_related_' . $fsqId;
                            $relatedCachedItem = $this->cache->getItem($relatedCacheKey);

                            if ($relatedCachedItem->isHit()) {
                                $locationData['related'] = $relatedCachedItem->get();
                            } else {
                                try {
                                    $relatedUrl = sprintf(
                                        'https://api.foursquare.com/v3/places/%s/related?limit=3',
                                        $fsqId
                                    );
                                    $relatedResponse = $this->httpClient->request('GET', $relatedUrl, [
                                        'headers' => [
                                            'Authorization' => $this->foursquareApiKey,
                                            'accept' => 'application/json',

                                        ],
                                    ]);

                                    if ($relatedResponse->getStatusCode() === 200) {
                                        $relatedData = $relatedResponse->toArray();
                                        $locationData['related'] = $relatedData ?? [];
                                        $relatedCachedItem->set($locationData['related'])->expiresAfter(3600);
                                        $this->cache->save($relatedCachedItem);
                                    }
                                } catch (\Exception $e) {
                                    $this->logger->error('Foursquare related places API exception', [
                                        'message' => $e->getMessage(),
                                        'fsq_id' => $fsqId,
                                    ]);
                                    $locationData['related'] = [];
                                }
                            }
                        } else {
                            $locationData['related'] = [];
                        }
                    }
                }
            }
        }

        return $this->render('front_office/assistance/location/index.html.twig', [
            'locationsData' => $locationsData,
            'location' => $location,
            'errorMessage' => $errorMessage,
            'user' => $this->getUser(), // Pass the authenticated user
        ]);
    }
}
