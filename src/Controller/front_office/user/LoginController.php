<?php
namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If the user is already authenticated, redirect to the home page
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front_office/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
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