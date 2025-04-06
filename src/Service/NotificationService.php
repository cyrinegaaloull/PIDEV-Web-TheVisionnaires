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
        $this->twilio->messages->create("whatsapp:" . $to, [
            'from' => $this->whatsappNumber,
            'body' => $message,
        ]);
    }

    public function sendEmail(string $to, string $subject, string $message): void
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($to)
            ->subject($subject)
            ->text($message);

        $this->mailer->send($email);
    }
}
