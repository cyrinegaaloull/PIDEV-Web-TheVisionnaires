<?php

namespace App\Controller\back_office\assistance;

use App\Entity\Etablissement;
use App\Entity\Service;
use App\Entity\Avis;
use App\Form\AdminEtablissementType;
use App\Form\AdminServiceType;
use App\Repository\EtablissementRepository;
use App\Repository\ServiceRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin/assistance', name: 'admin_assistance_')]
class AdminAssistanceController extends AbstractController
{
    // Dashboard de l'assistance
    #[Route('/', name: 'dashboard')]
    public function dashboardAssistance(
        EntityManagerInterface $entityManager,
        EtablissementRepository $etablissementRepository,
        ServiceRepository $serviceRepository,
        AvisRepository $avisRepository
    ): Response
    {
        // Statistiques pour la section Assistance
        $stats = [
            'totalEtablissements' => count($etablissementRepository->findAll()),
            'totalServices' => count($serviceRepository->findAll()),
            'totalAvis' => count($avisRepository->findAll()),
        ];
        
        // Obtenir des établissements les mieux notés
        $topEtablissements = $entityManager->createQuery(
            'SELECT e.etabname, AVG(a.rating) as avgRating, COUNT(a.avisid) as totalAvis
             FROM App\Entity\Avis a
             JOIN a.etablissement e
             GROUP BY e.etabid, e.etabname
             HAVING COUNT(a.avisid) > 0
             ORDER BY avgRating DESC'
        )
        ->setMaxResults(5)
        ->getResult();
        
        // Obtenir la distribution des services par établissement
        $servicesByEtab = $entityManager->createQuery(
            'SELECT e.etabname, COUNT(s.serviceid) as serviceCount
             FROM App\Entity\Service s
             JOIN s.etablissement e
             GROUP BY e.etabid, e.etabname
             ORDER BY serviceCount DESC'
        )
        ->setMaxResults(5)
        ->getResult();
        
        return $this->render('back_office/assistance/dashboardAssistance.html.twig', [
            'stats' => $stats,
            'topEtablissements' => $topEtablissements,
            'servicesByEtab' => $servicesByEtab
        ]);
    }
    

    // Gestion des établissements
    #[Route('/locaux', name: 'etablissements_list')]
    public function listEtablissements(EtablissementRepository $etablissementRepository): Response
    {
        return $this->render('back_office/assistance/etablissements-list.html.twig', [
            'etablissements' => $etablissementRepository->findAll(),
        ]);
    }

    #[Route('/locaux/creer', name: 'etablissement_create')]
    public function createEtablissement(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $etablissement = new Etablissement();
        $form = $this->createForm(AdminEtablissementType::class, $etablissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image si nécessaire
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/assets/uploads-naima',
                        $newFilename
                    );
                    // Ici, vous pourriez stocker le nom du fichier dans l'entité si vous avez un champ pour ça
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                }
            }

            $entityManager->persist($etablissement);
            $entityManager->flush();

            $this->addFlash('success', 'Local ajouté avec succès!');
            return $this->redirectToRoute('admin_assistance_etablissements_list');
        }

        return $this->render('back_office/assistance/etablissements-form.html.twig', [
            'form' => $form->createView(),
            'etablissement' => $etablissement,
            'mode' => 'create'
        ]);
    }

    #[Route('/locaux/{etabid}/editer', name: 'etablissement_edit')]
    public function editEtablissement(Request $request, Etablissement $etablissement, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(AdminEtablissementType::class, $etablissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image si nécessaire
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/assets/uploads-naima',
                        $newFilename
                    );
                    // Ici, vous pourriez stocker le nom du fichier dans l'entité si vous avez un champ pour ça
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Local modifié avec succès!');
            return $this->redirectToRoute('admin_assistance_etablissements_list');
        }

        return $this->render('back_office/assistance/etablissements-form.html.twig', [
            'form' => $form->createView(),
            'etablissement' => $etablissement,
            'mode' => 'edit'
        ]);
    }

    #[Route('/locaux/{etabid}/supprimer', name: 'etablissement_delete', methods: ['POST'])]
    public function deleteEtablissement(Request $request, Etablissement $etablissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etablissement->getEtabid(), $request->request->get('_token'))) {
            $entityManager->remove($etablissement);
            $entityManager->flush();
            $this->addFlash('success', 'Local supprimé avec succès!');
        }

        return $this->redirectToRoute('admin_assistance_etablissements_list');
    }

    // Gestion des services
    #[Route('/prestations', name: 'services_list')]
public function listServices(ServiceRepository $serviceRepository): Response
{
    $services = $serviceRepository->findAllWithEtablissement();

    return $this->render('back_office/assistance/services-list.html.twig', [
        'services' => $services,
    ]);
}

    #[Route('/prestations/creer', name: 'service_create')]
public function createService(Request $request, EntityManagerInterface $entityManager): Response
{
    $service = new Service();
    $form = $this->createForm(AdminServiceType::class, $service);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // La relation avec l'établissement est déjà gérée par le formulaire
        $entityManager->persist($service);
        $entityManager->flush();

        $this->addFlash('success', 'Prestation ajoutée avec succès!');
        return $this->redirectToRoute('admin_assistance_services_list');
    }

    return $this->render('back_office/assistance/services-form.html.twig', [
        'form' => $form->createView(),
        'service' => $service,
        'mode' => 'create'
    ]);
}
    


    #[Route('/prestations/{serviceid}/editer', name: 'service_edit')]
    public function editService(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminServiceType::class, $service);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'établissement sélectionné dans le formulaire (objet Etablissement)
            $etablissement = $service->getEtablissement(); // C'est un objet Etablissement
    
            // Assurez-vous que l'établissement est bien associé au service
            $service->setEtablissement($etablissement);
    
            // Mettre à jour le service avec l'établissement associé
            $entityManager->flush();
    
            $this->addFlash('success', 'Prestation modifiée avec succès!');
            return $this->redirectToRoute('admin_assistance_services_list');
        }
    
        return $this->render('back_office/assistance/services-form.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
            'mode' => 'edit'
        ]);
    }
    

    #[Route('/prestations/{serviceid}/supprimer', name: 'service_delete', methods: ['POST'])]
    public function deleteService(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getServiceid(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
            $this->addFlash('success', 'Prestation supprimée avec succès!');
        }

        return $this->redirectToRoute('admin_assistance_services_list');
    }

    #[Route('/etablissements/liste-json', name: 'etablissements_list_json')]
public function listEtablissementsJson(EtablissementRepository $etablissementRepository): JsonResponse
{
    try {
        $etablissements = $etablissementRepository->findAll();
        $data = [];
        foreach ($etablissements as $etablissement) {
            $data[] = [
                'etabid' => $etablissement->getEtabid(),
                'etabname' => $etablissement->getEtabname()
            ];
        }
        return $this->json($data);
    } catch (\Exception $e) {
        // Log the error
        return $this->json(['error' => $e->getMessage()], 500);
    }
}

    // Gestion des avis
    #[Route('/evaluations', name: 'avis_list')]
    public function listAvis(AvisRepository $avisRepository): Response
    {
        return $this->render('back_office/assistance/avis_list.html.twig', [
            'avis' => $avisRepository->findBy([], ['dateavis' => 'DESC']),
        ]);
    }

    #[Route('/evaluations/{avisid}/supprimer', name: 'avis_delete', methods: ['POST'])]
    public function deleteAvis(Request $request, Avis $avis, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avis->getAvisid(), $request->request->get('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();
            $this->addFlash('success', 'Évaluation supprimée avec succès!');
        }

        return $this->redirectToRoute('admin_assistance_avis_list');
    }
    
    // Statistiques
    #[Route('/statistiques', name: 'stats_dashboard')]
    public function statisticsAssistance(
        EntityManagerInterface $entityManager,
        EtablissementRepository $etablissementRepository,
        ServiceRepository $serviceRepository,
        AvisRepository $avisRepository
    ): Response
    {
        // Statistiques sur les établissements
        $etabByCategories = $entityManager->createQuery(
            'SELECT c.categoryname, COUNT(e.etabid) as count
             FROM App\Entity\Etablissement e
             JOIN e.categoryid c
             GROUP BY c.categoryid, c.categoryname'
        )->getResult();
        
        // Statistiques sur les avis
        $ratingDistribution = $entityManager->createQuery(
            'SELECT a.rating, COUNT(a.avisid) as count
             FROM App\Entity\Avis a
             GROUP BY a.rating
             ORDER BY a.rating ASC'
        )->getResult();
        
        // Statistiques sur les services
        $avgPriceByEtab = $entityManager->createQuery(
            'SELECT e.etabname, AVG(s.serviceprix) as avgPrice
             FROM App\Entity\Service s
             JOIN s.etablissement e
             GROUP BY e.etabid, e.etabname
             ORDER BY avgPrice DESC'
        )->getResult();
        
        return $this->render('back_office/assistance/statsAssistance.html.twig', [
            'etabByCategories' => $etabByCategories,
            'ratingDistribution' => $ratingDistribution,
            'avgPriceByEtab' => $avgPriceByEtab
        ]);
    }
}