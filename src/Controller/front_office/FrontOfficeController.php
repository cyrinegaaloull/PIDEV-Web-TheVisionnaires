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
        // Use Symfony's security token to get the logged-in user
        $user = $this->getUser();
        return $this->render('front_office/homePage.html.twig', [
            'user' => $user,
        ]);
    }
}
