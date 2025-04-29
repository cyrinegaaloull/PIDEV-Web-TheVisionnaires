<?php
namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
    
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $showSignup = $request->query->has('show_signup') || $request->query->has('registration');
    
        return $this->render('front_office/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'show_signup' => $showSignup,
            'form_data' => [],
            'signup_errors' => [],
            'recaptcha_site_key' => $_ENV['EWZ_RECAPTCHA_SITE_KEY'], // ADD THIS!
        ]);
    }
    

    #[Route('/login_check', name: 'app_login_check')]
    public function check()
    {
        // This will never be executed
        throw new \LogicException('This code should never be reached');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be empty, as it will never be executed
        // The logout is handled by Symfony's security system
        throw new \LogicException('This method should not be reached!');
    }
}