<?php
namespace App\Controller\front_office;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontOfficeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        // false pour user déconnecté true pour connecté
        $simulateUser = true;

        $user = null;
        if ($simulateUser) {
            $user = [
                'username' => 'John Doe',
                'profile_picture' => 'default_profile_pic.jpg',
            ];
        }

        return $this->render('/front_office/homePage.html.twig', [
            'user' => $user,
        ]);
    }
}
