<?php

namespace App\Service;

use Twilio\Rest\Client;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private $twilio;
    private $whatsappNumber;
    private $mailer;
    private $senderEmail;

    public function __construct(string $twilioSid, string $twilioToken, string $whatsappNumber, MailerInterface $mailer, string $senderEmail)
    {
        $this->twilio = new Client($twilioSid, $twilioToken);
        $this->whatsappNumber = $whatsappNumber;
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
    }

    public function sendWhatsApp(string $to, string $message): void
{
    try {
        $this->twilio->messages->create("whatsapp:" . $to, [
            'from' => $this->whatsappNumber,
            'body' => $message,
        ]);
    } catch (\Exception $e) {
        file_put_contents('twilio_error.log', $e->getMessage(), FILE_APPEND);
        throw $e; // Or remove this if you want to fail silently
    }
}

public function sendEmail(string $to, string $subject, string $message): void
{
    try {
        $email = (new Email())
            ->from("cyrinegaaloul6@gmail.com")
            ->to($to)
            ->subject($subject)
            ->text($message);

        $this->mailer->send($email);
    } catch (\Exception $e) {
        file_put_contents('mail_error.log', $e->getMessage(), FILE_APPEND);
        throw $e;
    }
}

}
