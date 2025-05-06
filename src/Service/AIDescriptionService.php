<?php

namespace App\Service;

class AIDescriptionService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = 'AIzaSyBFKSe8RkSkVgtx-EG_WRj__yrwHQc5bxw'; 
    }

    public function generateDescription(string $lieuName): ?string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Génère une description attrayante pour le lieu nommé \"$lieuName\". (3 à 5 lignes maximum, style engageant, français)"]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }

        return null;
    }

    
}
