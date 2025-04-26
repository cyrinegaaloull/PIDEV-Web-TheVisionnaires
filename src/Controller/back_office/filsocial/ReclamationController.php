<?php

namespace App\Controller\back_office\filsocial;

use App\Entity\Reclamation;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    /**
     * List all reclamations in the admin panel.
     */
    #[Route('/admin/reclamations', name: 'admin_reclamations_list', methods: ['GET'])]
    public function listReclamations(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the status filter from the query parameter
        $filterStatus = $request->query->get('status', 'all');

        // Fetch reclamations based on the filter
        $reclamationRepository = $entityManager->getRepository(Reclamation::class);
        if ($filterStatus === 'all') {
            $reclamations = $reclamationRepository->findAll();
        } else {
            $reclamations = $reclamationRepository->findBy(['status' => $filterStatus]);
        }

        return $this->render('back_office/filsocial/list.html.twig', [
            'reclamations' => $reclamations,
            'filterStatus' => $filterStatus,
        ]);
    }

    /**
     * Resolve a reclamation by deleting the associated post and marking the reclamation as resolved.
     */
    #[Route('/admin/reclamation/{id}/resolve', name: 'admin_reclamation_resolve', methods: ['POST'])]
    public function resolveReclamation(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('resolve-reclamation' . $reclamation->getReclamationId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_reclamations_list');
        }

        // Get the associated post ID
        $postId = $reclamation->getPostId();

        // Find the associated post
        $post = $entityManager->getRepository(Post::class)->find($postId);
        if ($post) {
            // Delete the post
            $entityManager->remove($post);
        } else {
            // If the post doesn't exist, add a warning flash message
            $this->addFlash('warning', 'The associated post was not found.');
        }

        // Update the reclamation status to "Resolved"
        $reclamation->setStatus('Resolved');
        $entityManager->flush();

        // Add success flash message
        $this->addFlash('success', 'Reclamation resolved and associated post deleted.');

        return $this->redirectToRoute('admin_reclamations_list');
    }

    /**
     * Decline a reclamation by marking it as declined.
     */
    #[Route('/admin/reclamation/{id}/decline', name: 'admin_reclamation_decline', methods: ['POST'])]
    public function declineReclamation(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('decline-reclamation' . $reclamation->getReclamationId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_reclamations_list');
        }

        // Update the reclamation status to "Declined"
        $reclamation->setStatus('Declined');
        $entityManager->flush();

        // Add success flash message
        $this->addFlash('success', 'Reclamation declined.');

        return $this->redirectToRoute('admin_reclamations_list');
    }

    /**
     * Show details of a specific reclamation and allow resolving or declining it.
     */
    #[Route('/admin/reclamation/{id}/details', name: 'admin_reclamation_details', methods: ['GET', 'POST'])]
    public function detailsReclamation(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {
        // Handle POST requests for resolving or declining the reclamation
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            if ($action === 'resolve') {
                return $this->resolveReclamation($request, $entityManager, $reclamation);
            } elseif ($action === 'decline') {
                return $this->declineReclamation($request, $entityManager, $reclamation);
            }
        }

        // Render the details page for GET requests
        return $this->render('back_office/filsocial/details.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
}