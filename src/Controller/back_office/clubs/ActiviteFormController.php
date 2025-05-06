<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Activite;
use App\Form\clubs\ActiviteType;
use App\Service\SightengineService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
                return $this->handleAjaxFormSubmission($form, $activite, $em, $slugger, false);
            }
        }

        return $this->render('back_office/clubs/create-activite.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/activites/edit/{id}', name: 'admin_activite_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $activite = $em->getRepository(Activite::class)->find($id);
        if (!$activite) {
            return new JsonResponse(['success' => false, 'message' => 'Activité introuvable.'], 404);
        }

        $form = $this->createForm(ActiviteType::class, $activite, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($request->isXmlHttpRequest()) {
                return $this->handleAjaxFormSubmission($form, $activite, $em, $slugger, true);
            }
        }

        return $this->render('back_office/clubs/edit-activite.html.twig', [
            'form' => $form->createView(),
            'activite' => $activite
        ]);
    }

    #[Route('/admin/activites/delete/{id}', name: 'admin_activite_delete', methods: ['POST'])]
    public function delete(Request $request, Activite $activite, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete' . $activite->getActiviteid(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Jeton CSRF invalide.'], 400);
        }

        try {
            $em->remove($activite);
            $em->flush();
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'La suppression a échoué : ' . $e->getMessage()
            ], 500);
        }
    }


    private function handleAjaxFormSubmission($form, Activite $activite, EntityManagerInterface $em, SluggerInterface $slugger, bool $isEdit): JsonResponse
    {
        $response = ['success' => false, 'errors' => []];

        // Validate logical constraint: endTime > startTime
        $startTime = $activite->getStarttime();
        $endTime = $activite->getEndtime();
        if ($endTime <= $startTime) {
            $form->get('endtime')->addError(new FormError("L'heure de fin doit être après l'heure de début."));
        }

        if ($form->isValid()) {
            try {
                $this->handleFileUpload($form, $activite, $slugger, $isEdit);
                $em->persist($activite);
                $em->flush();

                $response['success'] = true;
                $response['message'] = $isEdit ? 'Activité mise à jour avec succès !' : 'Activité créée avec succès !';
                $response['redirect'] = $this->generateUrl('admin_activite_list'); // change if needed
            } catch (\Exception $e) {
                $response['message'] = 'Erreur : ' . $e->getMessage();
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                if ($origin && method_exists($origin, 'getName')) {
                    $field = $origin->getName();
                    $response['errors'][$field] = $error->getMessage();
                } else {
                    $response['form'][] = $error->getMessage(); // general form errors
                }
            }            
        }
        return new JsonResponse($response, 400);
    }

    private function handleFileUpload($form, Activite $activite, SluggerInterface $slugger, bool $isEdit): void
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('activiteimage')->getData();

        if ($imageFile instanceof UploadedFile) {
            $fileName = $this->uploadImage($imageFile, $slugger);
            $fullPath = $this->getParameter('uploads_directory') . '/' . $fileName;

            if (!$this->vision->isImageSafe($fullPath)) {
                unlink($fullPath);
                throw new \RuntimeException("L’image contient un contenu inapproprié.");
            }

            $activite->setActiviteimage($fileName);
        } elseif (!$isEdit) {
            throw new \RuntimeException("Aucune image fournie.");
        }
    }

    private function uploadImage(UploadedFile $file, SluggerInterface $slugger): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getParameter('uploads_directory'), $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Erreur lors du téléchargement du fichier.');
        }

        return $newFilename;
    }
}
