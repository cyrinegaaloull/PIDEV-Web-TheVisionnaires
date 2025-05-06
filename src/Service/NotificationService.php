<?php

namespace App\Service;

class NotificationService
{
    public function __construct() {}

    public function sendEmail(string $to, string $subject, string $htmlMessage, ?string $attachmentPath = null): void
    {
        $apiKey = 're_BEGyvgrA_PzK2uHZmX7a9Wu4VGiDeSapy';
        $url = 'https://api.resend.com/emails';
    
        $data = [
            'from' => 'LocalLens <onboarding@resend.dev>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlMessage,
        ];
    
        if ($attachmentPath && file_exists($attachmentPath)) {
            $attachmentContent = base64_encode(file_get_contents($attachmentPath));
            $filename = basename($attachmentPath);
            $data['attachments'] = [
                [
                    'filename' => $filename,
                    'content' => $attachmentContent,
                ]
            ];
        }
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        if (curl_errno($ch)) {
            file_put_contents(__DIR__ . '/../../var/log/mail_error.log', "cURL Error: " . curl_error($ch) . "\n", FILE_APPEND);
        } elseif ($httpCode !== 200) {
            file_put_contents(__DIR__ . '/../../var/log/mail_error.log', "HTTP Error $httpCode: " . $response . "\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . '/../../var/log/mail_success.log', $response . "\n", FILE_APPEND);
        }
    
        curl_close($ch);
    }
    public function sendEmailWithAttachment(string $to, string $subject, string $htmlMessage, string $attachmentContent): void
    {
        $apiKey = 're_BEGyvgrA_PzK2uHZmX7a9Wu4VGiDeSapy';
        $url = 'https://api.resend.com/emails';

        $data = [
            'from' => 'LocalLens <onboarding@resend.dev>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlMessage,
            'attachments' => [
                [
                    'filename' => 'qrcode.png',
                    'content' => base64_encode($attachmentContent),
                    'content_type' => 'image/png'
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            file_put_contents(__DIR__ . '/../../var/log/mail_error.log', "cURL Error (with attachment): $error\n", FILE_APPEND);
            curl_close($ch);
            throw new \Exception("Failed to send email with attachment: cURL error - $error");
        }

        if ($httpCode !== 200) {
            file_put_contents(__DIR__ . '/../../var/log/mail_error.log', "HTTP Error $httpCode (with attachment): $response\n", FILE_APPEND);
            curl_close($ch);
            throw new \Exception("Failed to send email with attachment: HTTP $httpCode - $response");
        }

        file_put_contents(__DIR__ . '/../../var/log/mail_success.log', "Email with attachment sent successfully: $response\n", FILE_APPEND);
        curl_close($ch);
    }
}