<?php

namespace App\Controller\front_office\assistance;

use App\Entity\Service;
use App\Entity\Etablissement;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ServiceController extends AbstractController
{
    #[Route('/etablissements/{id}/services', name: 'liste_services')]
    public function index(Etablissement $etablissement, ServiceRepository $repo): Response
    {
        // Récupérer tous les services liés à cet établissement
        $services = $repo->findBy(['etablissement' => $etablissement]);
        
        return $this->render('front_office/assistance/services.html.twig', [
            'etablissement' => $etablissement,
            'services' => $services,
        ]);
    }
    
    #[Route('/etablissements/{id}/services/ajouter', name: 'ajouter_service')]
    public function add(Request $request, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $service->setEtablissement($etablissement);
        
        // Pré-remplir l'établissement et le désactiver pour qu'il ne puisse pas être modifié
        $form = $this->createForm(ServiceType::class, $service, [
            'etablissement_readonly' => true
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le service a été ajouté avec succès!');
            return $this->redirectToRoute('liste_services', ['id' => $etablissement->getEtabid()]);
        }
        
        return $this->render('front_office/assistance/ajouterService.html.twig', [
            'form' => $form->createView(),
            'etablissement' => $etablissement,
            'titre' => 'Ajouter un service'
        ]);
    }
    
    #[Route('/services/modifier/{id}', name: 'modifier_service')]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $etablissement = $service->getEtablissement();
        
        // Pré-remplir l'établissement et le désactiver pour qu'il ne puisse pas être modifié
        $form = $this->createForm(ServiceType::class, $service, [
            'etablissement_readonly' => true
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le service a été modifié avec succès!');
            return $this->redirectToRoute('liste_services', ['id' => $etablissement->getEtabid()]);
        }
        
        return $this->render('front_office/assistance/ajouterService.html.twig', [
            'form' => $form->createView(),
            'etablissement' => $etablissement,
            'titre' => 'Modifier le service'
        ]);
    }
    
    #[Route('/services/supprimer/{id}', name: 'supprimer_service')]
    public function delete(Service $service, EntityManagerInterface $entityManager): Response
    {
        $etablissement = $service->getEtablissement();
        $entityManager->remove($service);
        $entityManager->flush();
        
        $this->addFlash('success', 'Le service a été supprimé avec succès!');
        return $this->redirectToRoute('liste_services', ['id' => $etablissement->getEtabid()]);
    }
}