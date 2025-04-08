<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Activite;
use App\Form\clubs\ActiviteType;
use App\Form\clubs\ActiviteEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ActiviteFormController extends AbstractController
{
    #[Route('/admin/activites/new', name: 'admin_activite_new')]
public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $activite = new Activite();
    $form = $this->createForm(ActiviteType::class, $activite);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($request->isXmlHttpRequest()) {
            $response = ['success' => false, 'errors' => []];

            if ($form->isValid()) {
                // Handle image upload
                $imageFile = $form->get('activiteimage')->getData();
                if ($imageFile) {
                    $filename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move($this->getParameter('uploads_directory'), $filename);
                    $activite->setActiviteimage($filename);
                }

                $em->persist($activite);
                $em->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Activité ajoutée avec succès.',
                    'redirect' => $this->generateUrl('admin_activite_list')
                ]);
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $field = $error->getOrigin()->getName();
                    $response['errors'][$field] = $error->getMessage();
                }
            }

            return new JsonResponse($response, 400);
        }

        // Fallback for regular form submission
        if ($form->isValid()) {
            $imageFile = $form->get('activiteimage')->getData();
            if ($imageFile) {
                $filename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('uploads_directory'), $filename);
                $activite->setActiviteimage($filename);
            }

            $em->persist($activite);
            $em->flush();

            $this->addFlash('success', 'Activité ajoutée avec succès.');
            return $this->redirectToRoute('admin_activite_list');
        }
    }

    return $this->render('back_office/clubs/create-activite.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/admin/activite/modifier/{id}', name: 'admin_activite_edit')]
    public function edit(Activite $activite, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ActiviteEditType::class, $activite);

        $imageFile = $form->get('activiteimage')->getData();
if ($imageFile) {
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

    try {
        $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
        $activite->setActiviteimage($newFilename);
    } catch (FileException $e) {
        $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
    }
}


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Activité mise à jour avec succès !');
            return $this->redirectToRoute('admin_activite_list');
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
        if (!$this->isCsrfTokenValid('delete'.$activite->getActiviteid(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        try {
            $em->remove($activite);
            $em->flush();
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Delete failed: '.$e->getMessage()], 500);
        }
    }
}