<?php
namespace App\EventListener;

use App\Service\EmailService;
use App\Entity\Users;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Psr\Log\LoggerInterface;

class LoginSuccessListener
{
    public function __construct(
        private EmailService $emailService,
        private LoggerInterface $logger
    ) {}

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if (!$user instanceof Users) {
            $this->logger->warning('User is not an instance of App\Entity\Users');
            return;
        }
    
        $this->logger->info('LoginSuccessListener triggered', ['user' => $user->getEmail()]);
        
        try {
            $this->emailService->sendLoginNotification($user); // Pass the Users object, not just the email
            $this->logger->info('Email sent successfully');
        } catch (\Exception $e) {
            $this->logger->error('Email sending failed', ['error' => $e->getMessage()]);
            throw $e; // Re-throw to see error in dev environment
        }
    }
}