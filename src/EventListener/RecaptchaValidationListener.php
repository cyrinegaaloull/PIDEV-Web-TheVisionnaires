<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Psr\Log\LoggerInterface;

class RecaptchaValidationListener implements EventSubscriberInterface
{
    private string $secretKey;
    private RequestStack $requestStack;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        string $secretKey, 
        RequestStack $requestStack, 
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->secretKey = $secretKey;
        $this->requestStack = $requestStack;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'onCheckPassport',
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        // Only validate on login form submission
        if ($request->attributes->get('_route') !== 'app_login_check') {
            return;
        }

        $recaptchaResponse = $request->request->get('g-recaptcha-response');
        
        $this->logger->info('Validating reCAPTCHA', [
            'response_exists' => !empty($recaptchaResponse)
        ]);

        if (!$recaptchaResponse) {
            throw new CustomUserMessageAuthenticationException('Please complete the reCAPTCHA.');
        }

        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $recaptchaResponse,
                ],
            ]);

            $content = $response->toArray();
            
            $this->logger->info('reCAPTCHA verification result', [
                'success' => $content['success'] ?? false,
                'error_codes' => $content['error-codes'] ?? []
            ]);

            if (!($content['success'] ?? false)) {
                throw new CustomUserMessageAuthenticationException('reCAPTCHA verification failed.');
            }
        } catch (\Exception $e) {
            $this->logger->error('reCAPTCHA verification error', [
                'error' => $e->getMessage()
            ]);
            throw new CustomUserMessageAuthenticationException('Error validating reCAPTCHA. Please try again.');
        }
    }
}