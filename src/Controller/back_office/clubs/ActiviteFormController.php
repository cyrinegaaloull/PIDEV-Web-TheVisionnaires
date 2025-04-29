<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Activite;
use App\Form\clubs\ActiviteType;
use App\Form\clubs\ActiviteEditType;
use App\Service\SightengineService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ActiviteFormController extends AbstractController
{
    private SightengineService $vision;

    public function __construct(SightengineService $vision)
    {
        $this->vision = $vision;
    }

    #[Route('/admin/activites/new', name: 'admin_activite_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->isXmlHttpRequest()) {
                $response = ['success' => false, 'errors' => []];
        
                // ✅ Step 1: Collect normal validation errors first
                if (!$form->isValid()) {
                    foreach ($form->getErrors(true) as $error) {
                        $field = $error->getOrigin()->getName();
                        $response['errors'][$field] = $error->getMessage();
                    }
                    return new JsonResponse($response, 400);
                }
        
                try {
                    // ✅ Step 2: No field errors -> now handle file upload
                    $this->handleFileUpload($form, $activite, $slugger);
        
                    // ✅ Step 3: If uploading created errors (bad image)
                    if (count($form->getErrors(true)) > 0) {
                        foreach ($form->getErrors(true) as $error) {
                            $field = $error->getOrigin()->getName();
                            $response['errors'][$field] = $error->getMessage();
                        }
                        return new JsonResponse($response, 400);
                    }
        
                    // ✅ Step 4: Save to database
                    $entityManager->persist($activite);
                    $entityManager->flush();
        
                    $response['success'] = true;
                    $response['message'] = 'Activity created successfully!';
                    $response['redirect'] = $this->generateUrl('admin_activite_list');
                } catch (\Exception $e) {
                    error_log('Error: ' . $e->getMessage());
                    $response['message'] = 'An unexpected error occurred.';
                }
        
                return new JsonResponse($response, $response['success'] ? 200 : 400);
            }
        }
        

        return $this->render('back_office/clubs/create-activite.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/activite/modifier/{id}', name: 'admin_activite_edit')]
    public function edit(Activite $activite, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ActiviteEditType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->isXmlHttpRequest()) {
                return $this->handleAjaxEditFormSubmission($form, $activite, $em, $slugger);
            }

            if ($form->isValid()) {
                $this->handleFileUpload($form, $activite, $slugger);

                try {
                    $em->flush();
                    $this->addFlash('success', 'Activité mise à jour avec succès !');
                    return $this->redirectToRoute('admin_activite_list');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'activité.');
                }
            }
        }

        return $this->render('back_office/clubs/edit-activite.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true,
            'activite' => $activite
        ]);
    }

    #[Route('/admin/activites/delete/{id}', name: 'admin_activite_delete', methods: ['POST'])]
    public function delete(Request $request, Activite $activite, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete' . $activite->getActiviteid(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Jeton CSRF invalide'], 400);
        }

        try {
            $em->remove($activite);
            $em->flush();
            return new JsonResponse(['success' => true]);
            
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'La suppression a échoué : ' . $e->getMessage()], 500);
        }
    }

    private function handleAjaxFormSubmission($form, $activite, $entityManager, $slugger): JsonResponse
    {
        $response = ['success' => false, 'errors' => []];

        if ($form->isValid()) {
            $this->handleFileUpload($form, $activite, $slugger);

            if (count($form->getErrors(true)) > 0) {
                foreach ($form->getErrors(true) as $error) {
                    $field = $error->getOrigin()->getName();
                    $response['errors'][$field] = $error->getMessage();
                }
                return new JsonResponse($response, 400);
            }

            try {
                $entityManager->persist($activite);
                $entityManager->flush();

                $response['success'] = true;
                $response['message'] = 'Activité créée avec succès !';
                $response['redirect'] = $this->generateUrl('admin_activite_list');
            } catch (\Exception $e) {
                $response['message'] = 'Une erreur est survenue lors de la création de l\'activité.';
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $field = $error->getOrigin()->getName();
                $response['errors'][$field] = $error->getMessage();
            }
        }

        return new JsonResponse($response, $response['success'] ? 200 : 400);
    }

    private function handleAjaxEditFormSubmission($form, $activite, $entityManager, $slugger): JsonResponse
    {
        $response = ['success' => false, 'errors' => []];

        if ($form->isValid()) {
            try {
                $this->handleFileUpload($form, $activite, $slugger);

                $entityManager->flush();

                $response = [
                    'success' => true,
                    'message' => 'Activité mise à jour avec succès !',
                    'redirect' => $this->generateUrl('admin_activite_list')
                ];
            } catch (\Exception $e) {
                $response['message'] = 'Erreur technique: ' . $e->getMessage();
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $field = $error->getOrigin()->getName();
                $response['errors'][$field] = $error->getMessage();
            }
        }

        return new JsonResponse($response, $response['success'] ? 200 : 400);
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
        error_log('Erreur d\'upload fichier: ' . $e->getMessage());
        throw new \RuntimeException('Erreur lors du téléchargement de l\'image.');
    }

    return $newFilename;
}

private function handleFileUpload($form, $activite, $slugger): void
{
    /** @var UploadedFile|null $imageFile */
    $imageFile = $form->get('activiteimage')->getData();

    if ($imageFile) {
        $newFilename = $this->uploadImage($imageFile, $slugger);
        $fullPath = $this->getParameter('uploads_directory') . '/' . $newFilename;

        try {
            // Analyse de contenu Sightengine
            if (!$this->vision->isImageSafe($fullPath)) {
                unlink($fullPath);
                $form->get('activiteimage')->addError(new FormError('L\'image contient un contenu inapproprié.'));
            } else {
                $activite->setActiviteimage($newFilename);
            }
        } catch (\Exception $e) {
            error_log('Erreur d\'analyse d\'image: ' . $e->getMessage());
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $form->get('activiteimage')->addError(new FormError('Erreur technique lors de l\'analyse de l\'image.'));
        }
    }
}

}
