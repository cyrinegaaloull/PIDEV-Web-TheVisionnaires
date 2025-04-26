<?php

namespace App\Controller\back_office\filsocial;

use App\Entity\Reclamation;
use App\Entity\Post;
use App\Entity\Users; // Fixed: Users entity with 's'
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/admin/reclamations', name: 'admin_reclamations_list', methods: ['GET'])]
    public function listReclamations(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filterStatus = $request->query->get('status', 'all');
        $reclamationRepository = $entityManager->getRepository(Reclamation::class);

        $reclamations = $filterStatus === 'all' ?
            $reclamationRepository->findAll() :
            $reclamationRepository->findBy(['status' => $filterStatus]);

        return $this->render('back_office/filsocial/list.html.twig', [
            'reclamations' => $reclamations,
            'filterStatus' => $filterStatus,
        ]);
    }

    #[Route('/admin/reclamation/{id}/resolve', name: 'admin_reclamation_resolve', methods: ['POST'])]
    public function resolveReclamation(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {
        if (!$this->isCsrfTokenValid('resolve-reclamation' . $reclamation->getReclamationId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_reclamations_list');
        }

        $postId = $reclamation->getPostId();
        $post = $entityManager->getRepository(Post::class)->find($postId);

        if ($post) {
            $entityManager->remove($post);
        } else {
            $this->addFlash('warning', 'The associated post was not found.');
        }

        $reclamation->setStatus('Resolved');
        $entityManager->flush();

        $userRepository = $entityManager->getRepository(Users::class);
        $user = $userRepository->find($reclamation->getUserId());

        if ($user) {
            $userEmail = $user->getEmail();
            $subject = 'Your Reclamation Has Been Resolved';
            $content = $this->renderView('emails/reclamation_resolved.html.twig', [
                'user' => $user,
                'reclamation' => $reclamation,
            ]);
            $this->emailService->sendEmail($userEmail, $subject, $content);
        }

        $this->addFlash('success', 'Reclamation resolved and associated post deleted.');
        return $this->redirectToRoute('admin_reclamations_list');
    }

    #[Route('/admin/reclamation/{id}/decline', name: 'admin_reclamation_decline', methods: ['POST'])]
    public function declineReclamation(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {
        if (!$this->isCsrfTokenValid('decline-reclamation' . $reclamation->getReclamationId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_reclamations_list');
        }

        $reclamation->setStatus('Declined');
        $entityManager->flush();

        $userRepository = $entityManager->getRepository(Users::class);
        $user = $userRepository->find($reclamation->getUserId());

        if ($user) {
            $userEmail = $user->getEmail();
            $subject = 'Your Reclamation Has Been Declined';
            $content = $this->renderView('emails/reclamation_declined.html.twig', [
                'user' => $user,
                'reclamation' => $reclamation,
            ]);
            $this->emailService->sendEmail($userEmail, $subject, $content);
        }

        $this->addFlash('success', 'Reclamation declined.');
        return $this->redirectToRoute('admin_reclamations_list');
    }

    #[Route('/admin/reclamation/{id}/details', name: 'admin_reclamation_details', methods: ['GET', 'POST'])]
    public function detailsReclamation(Request $request, EntityManagerInterface $entityManager, Reclamation $reclamation): Response
    {
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            if ($action === 'resolve') {
                return $this->resolveReclamation($request, $entityManager, $reclamation);
            } elseif ($action === 'decline') {
                return $this->declineReclamation($request, $entityManager, $reclamation);
            }
        }

        return $this->render('back_office/filsocial/details.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
}
