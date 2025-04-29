<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Club;
use App\Form\clubs\ClubType;
use App\Form\clubs\ClubEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Service\SightengineService;


class CreateClub extends AbstractController
{


    private SightengineService $vision;

public function __construct(SightengineService $vision)
{
    $this->vision = $vision;
}



    #[Route('/admin/clubs/new', name: 'admin_club_new')]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger,
    ValidatorInterface $validator
): Response {
    $club = new Club();
    $form = $this->createForm(ClubType::class, $club);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Handle AJAX request
        if ($request->isXmlHttpRequest()) {
            return $this->handleAjaxFormSubmission($form, $club, $entityManager, $slugger);
        }

        // Handle regular form submission
        if ($form->isValid()) {
            try {
                $this->handleFileUploads($form, $club, $slugger);
                $club->setMemberscount(0);

                $entityManager->persist($club);
                $entityManager->flush();
                
                $this->addFlash('success', 'Club créé avec succès !');
                return $this->redirectToRoute('admin_club_list');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('clubName')->addError(new \Symfony\Component\Form\FormError('Ce nom de club est déjà utilisé.'));
            } catch (\RuntimeException $e) {
                // Catch the RuntimeException thrown by handleFileUploads
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                // Log any other exceptions
                error_log('Error creating club: ' . $e->getMessage());
                $this->addFlash('error', 'Une erreur est survenue lors de la création du club.');
            }
        }
    }

    return $this->render('back_office/clubs/create-club.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/admin/clubs/edit/{id}', name: 'admin_club_edit')]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $club = $em->getRepository(Club::class)->find($id);

        if (!$club) {
            return $this->json(['success' => false, 'message' => 'Club introuvable.'], 404);
        }

        $originalName = $club->getClubname();
        $form = $this->createForm(ClubEditType::class, $club, [
            'action' => $this->generateUrl('admin_club_edit', ['id' => $id]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->isXmlHttpRequest()) {
                // Debug: Log form data
                error_log('Form data: ' . print_r($request->request->all(), true));
                error_log('Files: ' . print_r($request->files->all(), true));

                return $this->handleAjaxEditFormSubmission($form, $club, $em, $slugger, $originalName);
            }

            // Handle regular form submission
            if ($form->isValid()) {
                error_log('Processing regular form submission');
                // Check if name changed
                if ($club->getClubname() !== $originalName) {
                    $existing = $em->getRepository(Club::class)->findOneBy(['clubname' => $club->getClubname()]);
                    if ($existing) {
                        $form->get('clubName')->addError(new \Symfony\Component\Form\FormError('Ce nom de club est déjà utilisé.'));
                        return $this->render('back_office/clubs/edit-club.html.twig', [
                            'form' => $form->createView(),
                            'club' => $club,
                        ]);
                    }
                }

                $this->handleFileUploads($form, $club, $slugger);

                try {
                    $em->flush();
                    $this->addFlash('success', 'Club mis à jour avec succès.');
                    return $this->redirectToRoute('admin_club_list');
                } catch (UniqueConstraintViolationException $e) {
                    $form->get('clubName')->addError(new \Symfony\Component\Form\FormError('Ce nom de club est déjà utilisé.'));
                }
            } else {
                error_log('Form is invalid');
                // Debug form errors
                foreach ($form->getErrors(true) as $error) {
                    error_log('Form error: ' . $error->getMessage());
                }
            }
        }

        return $this->render('back_office/clubs/edit-club.html.twig', [
            'form' => $form->createView(),
            'club' => $club,
        ]);
    }

    #[Route('/admin/clubs/delete/{id}', name: 'admin_club_delete', methods: ['POST'])]
    public function delete(Request $request, Club $club, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete'.$club->getClubid(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Jeton CSRF invalide.'], 400);
        }

        try {
            $em->remove($club);
            $em->flush();
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'La suppression a échoué : ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Handles AJAX form submission for creating clubs
     */
    private function handleAjaxFormSubmission($form, $club, $entityManager, $slugger): JsonResponse
{
    $response = ['success' => false, 'errors' => []];
    
    if ($form->isValid()) {
        $this->handleFileUploads($form, $club, $slugger);

        // Vérifie après upload si le formulaire a maintenant des erreurs
        if (count($form->getErrors(true)) > 0) {
            foreach ($form->getErrors(true) as $error) {
                $field = $error->getOrigin()->getName();
                $response['errors'][$field] = $error->getMessage();
            }
            return new JsonResponse($response, 400);
        }

        try {
            $club->setMemberscount(0);
            $entityManager->persist($club);
            $entityManager->flush();
            
            $response['success'] = true;
            $response['message'] = 'Club créé avec succès !';
            $response['redirect'] = $this->generateUrl('admin_club_list');
        } catch (UniqueConstraintViolationException $e) {
            $response['errors']['clubName'] = 'Ce nom de club est déjà utilisé.';
        }
    } else {
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $response['errors'][$field] = $error->getMessage();
        }
    }
    
    return new JsonResponse($response);
}


    /**
     * Handles AJAX form submission for editing clubs
     */
    private function handleAjaxEditFormSubmission($form, $club, $entityManager, $slugger, $originalName): JsonResponse
    {
        $response = ['success' => false, 'errors' => []];
        
        // First validate the form
        if ($form->isValid()) {
            try {
                // Handle file uploads
                $logoFile = $form->get('clubLogo')->getData();
                $bannerFile = $form->get('bannerImage')->getData();
                
                if ($logoFile instanceof UploadedFile) {
                    $logoFilename = $this->uploadImage($logoFile, $slugger);
                    $club->setClublogo($logoFilename);
                }
                
                if ($bannerFile instanceof UploadedFile) {
                    $bannerFilename = $this->uploadImage($bannerFile, $slugger);
                    $club->setBannerimage($bannerFilename);
                }
                
                // Check for duplicate name if changed
                if ($club->getClubname() !== $originalName) {
                    $existing = $entityManager->getRepository(Club::class)
                        ->findOneBy(['clubname' => $club->getClubname()]);
                    if ($existing) {
                        $response['errors']['clubName'] = 'Ce nom de club est déjà utilisé.';
                        return new JsonResponse($response, 400);
                    }
                }
                
                $entityManager->flush();
                
                $response = [
                    'success' => true,
                    'message' => 'Club mis à jour avec succès!',
                    'redirect' => $this->generateUrl('admin_club_list')
                ];
                
                return new JsonResponse($response);
                
            } catch (\Exception $e) {
                error_log('Error saving club: ' . $e->getMessage());
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Erreur technique: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Collect form errors
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $response['errors'][$field] = $error->getMessage();
        }
        
        return new JsonResponse($response, 400);
    }

    private function handleFileUploads($form, $club, $slugger): void
    {
        /** @var UploadedFile|null $logoFile */
        $logoFile = $form->get('clubLogo')->getData();
        /** @var UploadedFile|null $bannerFile */
        $bannerFile = $form->get('bannerImage')->getData();

        if ($logoFile instanceof UploadedFile) {
            $logoFilename = $this->uploadImage($logoFile, $slugger);
            $fullPath = $this->getParameter('uploads_directory') . '/' . $logoFilename;

            try {
                if (!$this->vision->isImageSafe($fullPath)) {
                    unlink($fullPath);
                    $form->get('clubLogo')->addError(new \Symfony\Component\Form\FormError('Le logo contient un contenu inapproprié.'));
                } else {
                    $club->setClublogo($logoFilename);
                }
            } catch (\Exception $e) {
                unlink($fullPath);
                $form->get('clubLogo')->addError(new \Symfony\Component\Form\FormError('Erreur lors de l’analyse du logo.'));
            }
        }

        if ($bannerFile instanceof UploadedFile) {
            $bannerFilename = $this->uploadImage($bannerFile, $slugger);
            $fullPath = $this->getParameter('uploads_directory') . '/' . $bannerFilename;

            try {
                if (!$this->vision->isImageSafe($fullPath)) {
                    unlink($fullPath);
                    $form->get('bannerImage')->addError(new \Symfony\Component\Form\FormError('La bannière contient un contenu inapproprié.'));
                } else {
                    $club->setBannerimage($bannerFilename);
                }
            } catch (\Exception $e) {
                unlink($fullPath);
                $form->get('bannerImage')->addError(new \Symfony\Component\Form\FormError('Erreur lors de l’analyse de la bannière.'));
            }
        }
    }



    private function uploadImage(UploadedFile $file, SluggerInterface $slugger): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getParameter('uploads_directory'),
                $newFilename
            );
        } catch (FileException $e) {
            error_log('File upload error: ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors du téléchargement du fichier.');
        }

        return $newFilename;
    }


}