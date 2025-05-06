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

    // Use valid model name: "offensive-2.0"
    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"models\"\r\n\r\n";
    $body .= "offensive-2.0\r\n";

    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"api_user\"\r\n\r\n";
    $body .= $this->apiUser . "\r\n";

    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"api_secret\"\r\n\r\n";
    $body .= $this->apiSecret . "\r\n";

    $fileContent = file_get_contents($imagePath);
    $filename = basename($imagePath);

    $body .= "--$boundary\r\n";
    $body .= "Content-Disposition: form-data; name=\"media\"; filename=\"$filename\"\r\n";
    $body .= "Content-Type: image/jpeg\r\n\r\n";
    $body .= $fileContent . "\r\n";
    $body .= "--$boundary--\r\n";

    try {
        $response = $this->client->request('POST', 'https://api.sightengine.com/1.0/check.json', [
            'headers' => [
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            ],
            'body' => $body,
        ]);

        $data = $response->toArray(false);

        // Log raw result for review
        file_put_contents(
            __DIR__ . '/../../var/log/sightengine.log',
            "[" . date('Y-m-d H:i:s') . "] " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );

        if ($data['status'] === 'success' && isset($data['offensive'])) {
            $offensive = $data['offensive'];
            $flags = ['nazi', 'asian_swastika', 'confederate', 'supremacist', 'terrorist', 'middle_finger'];

            foreach ($flags as $flag) {
                if (($offensive[$flag] ?? 0) > 0.7) {
                    return false;
                }
            }
            return true;
        }

    } catch (\Exception $e) {
        error_log('Sightengine API error: ' . $e->getMessage());
    }

    return true;
}



}