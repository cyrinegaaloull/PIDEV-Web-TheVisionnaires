<?php

namespace App\Controller\front_office\assistance;

use App\Entity\Etablissement;
use App\Entity\Categorie;
use App\Form\EtablissementType;
use App\Repository\EtablissementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class EtablissementController extends AbstractController
{
    #[Route('/etablissements', name: 'liste_etablissements')]
    public function index(EtablissementRepository $repo): Response
    {
        $etablissements = $repo->findAll();
        
        return $this->render('front_office/assistance/etabs.html.twig', [
            'etablissements' => $etablissements,
            'user' => $this->getUser(), // Ajoutez cette ligne pour passer l'utilisateur à la vue
            'titre' => 'Liste des établissements'
        ]);
    }
    
    #[Route('/etablissements/ajouter', name: 'ajouter_etablissement')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etablissement = new Etablissement();
        
        // Utilisation de la classe de formulaire externalisée
        $form = $this->createForm(EtablissementType::class, $etablissement);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($etablissement);
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'établissement a été ajouté avec succès!');
            return $this->redirectToRoute('liste_etablissements');
        }
        
        return $this->render('front_office/assistance/ajouterEtabs.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(), // Ajoutez cette ligne pour passer l'utilisateur à la vue
            'titre' => 'Ajouter un établissement'
        ]);
    }
    
    #[Route('/etablissements/modifier/{id}', name: 'modifier_etablissement')]
    public function edit(Request $request, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire pré-rempli avec les données de l'établissement
        $form = $this->createForm(EtablissementType::class, $etablissement);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification explicite que les champs requis ne sont pas null
            if ($etablissement->getEtabname() === null) {
                $etablissement->setEtabname($etablissement->getEtabname() ?: '');
                $this->addFlash('error', 'Le nom de l\'établissement ne peut pas être vide');
                return $this->render('front_office/assistance/ajouterEtabs.html.twig', [
                    'form' => $form->createView(),
                    'user' => $this->getUser(), // Ajoutez cette ligne pour passer l'utilisateur à la vue
                    'titre' => 'Modifier l\'établissement'
                ]);
            }
            
            if ($etablissement->getEtabaddress() === null) {
                $etablissement->setEtabaddress($etablissement->getEtabaddress() ?: '');
                $this->addFlash('error', 'L\'adresse ne peut pas être vide');
                return $this->render('front_office/assistance/ajouterEtabs.html.twig', [
                    'form' => $form->createView(),
                    'user' => $this->getUser(), // Ajoutez cette ligne pour passer l'utilisateur à la vue
                    'titre' => 'Modifier l\'établissement'
                ]);
            }
            
            // Traitement normal si tout va bien
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'établissement a été modifié avec succès!');
            return $this->redirectToRoute('liste_etablissements');
        }
        
        return $this->render('front_office/assistance/ajouterEtabs.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(), // Ajoutez cette ligne pour passer l'utilisateur à la vue
            'titre' => 'Modifier l\'établissement'
        ]);
    }
    
    #[Route('/etablissements/supprimer/{id}', name: 'supprimer_etablissement')]
    public function delete(Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($etablissement);
        $entityManager->flush();
        
        $this->addFlash('success', 'L\'établissement a été supprimé avec succès!');
        return $this->redirectToRoute('liste_etablissements');
    }
}