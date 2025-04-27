<?php

namespace App\Controller\back_office;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('back_office/dashboard.html.twig');
    }

    #[Route('/admin/flux-social', name: 'admin_flux_social')]
    public function fluxSocial(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->render('back_office/flux_social.html.twig', [
            'posts' => $posts,
        ]);
    }
}