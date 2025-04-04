<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class NeutrinoService
{
    private $client;
    private $userId = 'cyrine';
    private $apiKey = 'BNg0o9TdgxLEZJKexTReIVuDmgxPrEkjxK9aGRcoTwyh76Oo';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function isClean(string $text): bool
    {
        $response = $this->client->request('POST', 'https://neutrinoapi.net/bad-word-filter', [
            'body' => [
                'user-id' => $this->userId,
                'api-key' => $this->apiKey,
                'content' => $text
            ]
        ]);

        $data = $response->toArray();
        return !$data['is-bad']; // true if clean
    }
}
