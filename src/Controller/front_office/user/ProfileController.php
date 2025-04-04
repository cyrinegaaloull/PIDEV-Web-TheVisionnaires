<?php
namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        // Retrieve the current logged-in user
        $user = $this->getUser();

        // If no user is logged in, redirect to the login page
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('front_office/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
