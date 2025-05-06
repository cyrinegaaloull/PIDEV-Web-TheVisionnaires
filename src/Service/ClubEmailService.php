<?php

namespace App\Service;

class ClubEmailService
{
    private string $apiKey = 're_TUFRnZ6j_D2aP6KUVXLWMsss1sqpJVYre';
    private string $fromEmail = 'LocalLens <onboarding@resend.dev>';

    public function send(
        string $to,
        string $subject,
        string $htmlMessage,
        ?string $replyTo = null
    ): void {
        $url = 'https://api.resend.com/emails';

        $data = [
            'from' => $this->fromEmail,
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlMessage,
        ];

        if ($replyTo) {
            $data['reply_to'] = $replyTo;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            file_put_contents(__DIR__ . '/../../var/log/mail_error.log', "cURL Error: " . curl_error($ch) . "\n", FILE_APPEND);
        } elseif ($httpCode !== 200 && $httpCode !== 202) {
            file_put_contents(__DIR__ . '/../../var/log/mail_error.log', "HTTP Error $httpCode: " . $response . "\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . '/../../var/log/mail_success.log', $response . "\n", FILE_APPEND);
        }

        curl_close($ch);
    }
}
