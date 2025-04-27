<?php
namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route(path: '/connect/google', name: 'connect_google_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        // redirects to Google!
        return $clientRegistry
            ->getClient('google')         // key used in knpu_oauth2_client.yaml
            ->redirect([], []);          // pass scopes or options here
    }

    #[Route(path: '/connect/google/check', name: 'connect_google_check')]
    public function connectCheck()
    {
        // This will never be executed, as the authenticator intercepts it
        throw new \Exception('Authentication failed or was intercepted before reaching this point.');
    }
}
