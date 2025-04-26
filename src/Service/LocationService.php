<?php
// src/Service/LocationService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LocationService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function searchLocation(string $query)
    {
        $url = 'https://nominatim.openstreetmap.org/search';
        
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'limit' => 5
            ],
            'headers' => [
                'User-Agent' => 'Symfony Route Finder Application'
            ]
        ]);

        return $response->toArray();
    }

    public function getCurrentLocation()
    {
        $url = 'http://ip-api.com/json/?fields=status,message,lat,lon,city,regionName,country';
        
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'User-Agent' => 'Symfony Route Finder Application'
            ]
        ]);

        return $response->toArray();
    }
}