<?php

namespace App\Controller\front_office;

use App\Entity\Reclamation;
use App\Entity\Post;

use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    #[Route('/post/{postId}/report', name: 'report_post', methods: ['GET', 'POST'])]
    public function reportPost(Request $request, EntityManagerInterface $entityManager, int $postId): Response
    {
        /** @var \App\Entity\Users $user */
        $user = $this->getUser();
        // Fetch the Post entity based on postId
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('Publication non trouvée.');
        }

        // Ensure the user is logged in
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the currently authenticated user
        $user = $this->getUser();

        // Create a new Reclamation entity
        $reclamation = new Reclamation();
        $reclamation->setUserId($user->getUserId());
        $reclamation->setPostId($postId);

        $form = $this->createForm(\App\Form\ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reclamation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre signalement a été soumis avec succès.');
            return $this->redirectToRoute('app_post_show', ['postId' => $postId]);
        }

        return $this->render('front_office/reclamation/report_post.html.twig', [
            'form' => $form->createView(),
            'postId' => $postId,
            'user' => $user,

        ]);
    }
}