<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SightengineService
{
    private string $apiUser;
    private string $apiSecret;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->apiUser = $_ENV['SIGHTENGINE_API_USER'];
        $this->apiSecret = $_ENV['SIGHTENGINE_API_SECRET'];
        $this->client = $client;
    }

    public function isImageSafe(string $imagePath): bool
    {
        $boundary = uniqid();
        $body = '';

        // Add API credentials and models
        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"models\"\r\n\r\n";
        $body .= "nudity,wad,offensive,gore,violence,scam,spam,hate-symbols\r\n";

        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"api_user\"\r\n\r\n";
        $body .= $this->apiUser . "\r\n";

        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"api_secret\"\r\n\r\n";
        $body .= $this->apiSecret . "\r\n";

        // Add the file
        $fileContent = file_get_contents($imagePath);
        $filename = basename($imagePath);

        $body .= "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"media\"; filename=\"$filename\"\r\n";
        $body .= "Content-Type: image/jpeg\r\n\r\n";
        $body .= $fileContent . "\r\n";
        $body .= "--$boundary--\r\n";

        // Make request
        $response = $this->client->request('POST', 'https://api.sightengine.com/1.0/check.json', [
            'headers' => [
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            ],
            'body' => $body,
        ]);

        $data = $response->toArray(false);

        if (isset($data['status']) && $data['status'] === 'success') {
            // Analyze nudity, weapon, alcohol, drugs, offensive, gore, violence, hate
            if (
                ($data['nudity']['raw'] ?? 0) > 0.5 ||
                ($data['nudity']['partial'] ?? 0) > 0.5 ||
                ($data['weapon'] ?? 0) > 0.5 ||
                ($data['alcohol'] ?? 0) > 0.5 ||
                ($data['drugs'] ?? 0) > 0.5 ||
                ($data['offensive']['prob'] ?? 0) > 0.5 ||
                ($data['gore']['prob'] ?? 0) > 0.5 ||
                ($data['violence']['prob'] ?? 0) > 0.5 ||
                ($data['hate-symbols']['prob'] ?? 0) > 0.5
            ) {
                return false;
            }

            return true;
        }

        return false;
    }
}
