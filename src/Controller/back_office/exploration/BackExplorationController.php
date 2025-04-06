<?php
namespace App\Controller\back_office\exploration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Lieu;

use App\Form\LieuType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
class BackExplorationController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('/back_office/dashboard.html.twig');
    }
    
#[Route('/admin/lieu/init', name: 'admin_lieu_init')]
public function initLieu(): Response
{
    return $this->render('back_office/exploration/ajout_lieu_map.html.twig');
}
#[Route('/admin/lieu/complete/{lat}/{lon}/{name}', name: 'admin_lieu_finalize')]
public function finalizeLieu(Request $request, EntityManagerInterface $em, float $lat, float $lon, string $name): Response
{
    $lieu = new Lieu();
$lieu->setLatitude($lat);
$lieu->setLongitude($lon);
$lieu->setLieuname($name); // 👈 this will prefill the form field correctly


$form = $this->createForm(LieuType::class, $lieu, ['disabled_name' => true]);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('lieuimage')->getData();
        if ($file) {
            $filename = uniqid().'.'.$file->guessExtension();
            $file->move($this->getParameter('your_upload_directory'), $filename);
            $lieu->setLieuimage($filename);
        }

        $em->persist($lieu);
        $em->flush();

        $this->addFlash('success', 'Lieu enregistré avec succès !');
        return $this->redirectToRoute('admin_lieux');
    }

    // If submitted and invalid, show errors
    if ($form->isSubmitted()) {
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $message = $error->getMessage();
            dump("Erreur sur champ: $field => $message");
        }
        
        $this->addFlash('error', 'Formulaire invalide. Veuillez corriger les erreurs.');
    }

    return $this->render('back_office/exploration/ajout_lieu_finalize.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/admin/lieux', name: 'admin_lieux')]
public function listLieux(EntityManagerInterface $em): Response
{
    $lieux = $em->getRepository(Lieu::class)->findAll();

    return $this->render('back_office/exploration/lieux_list.html.twig', [
        'lieux' => $lieux
    ]);
}

#[Route('/admin/lieu/edit/{id}', name: 'admin_lieu_edit')]
public function editLieu(Request $request, Lieu $lieu, EntityManagerInterface $em): Response
{
    $form = $this->createForm(LieuType::class, $lieu);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('lieuimage')->getData();
        if ($file) {
            $filename = uniqid().'.'.$file->guessExtension();
            $file->move($this->getParameter('your_upload_directory'), $filename);
            $lieu->setLieuimage($filename);
        }

        $em->flush();
        $this->addFlash('success', 'Lieu modifié avec succès !');
        return $this->redirectToRoute('admin_lieux');
    }

    return $this->render('back_office/exploration/lieu_edit.html.twig', [
        'form' => $form->createView(),
        'lieu' => $lieu
    ]);
}

#[Route('/admin/lieu/delete/{id}', name: 'admin_lieu_delete')]
public function deleteLieu(Lieu $lieu, EntityManagerInterface $em): Response
{
    $em->remove($lieu);
    $em->flush();

    $this->addFlash('success', 'Lieu supprimé avec succès.');
    return $this->redirectToRoute('admin_lieux');
}



}
