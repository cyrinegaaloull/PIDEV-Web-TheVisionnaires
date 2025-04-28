<?php
namespace App\Controller\back_office\exploration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Lieu;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\LieuRepository;
use App\Form\LieuType;
use App\Form\EventType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\AIDescriptionService;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\ReservationEventRepository;
use App\Repository\ReviewRepository;

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
    public function finalizeLieu(Request $request, EntityManagerInterface $em, float $lat, float $lon, string $name, AIDescriptionService $aiService): Response
    {
        $lieu = new Lieu();
        $lieu->setLatitude($lat);
        $lieu->setLongitude($lon);
        $lieu->setLieuname($name);

        $generatedDescription = $aiService->generateDescription($name);
        if ($generatedDescription) {
            $lieu->setLieudescription($generatedDescription);
        }

        $form = $this->createForm(LieuType::class, $lieu, [
            'disabled_name' => true,
        ]);

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

            $this->addFlash('success', 'Lieu ajouté avec succès.');
            return $this->redirectToRoute('admin_lieux');
        }

        return $this->render('back_office/exploration/ajout_lieu_finalize.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/lieux', name: 'admin_lieux')]
    public function listLieux(Request $request, LieuRepository $lieuRepository): Response
    {
        // Récupérer les filtres de la requête
        $filters = [
            'nom' => $request->query->get('nom'),
            'categorie' => $request->query->get('categorie'),
            'adresse' => $request->query->get('adresse'),
        ];

        // Si on a des filtres, utiliser la méthode findByFilters
        if (array_filter($filters)) {
            $lieux = $lieuRepository->findByFilters($filters);
        } else {
            $lieux = $lieuRepository->findAll();
        }

        // Récupérer toutes les catégories pour le filtre
        $categories = $lieuRepository->findAllCategories();

        return $this->render('back_office/exploration/lieux_list.html.twig', [
            'lieux' => $lieux,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    #[Route('/admin/lieu/search', name: 'admin_lieu_search')]
    public function searchLieux(Request $request, LieuRepository $lieuRepository): JsonResponse
    {
        $term = $request->query->get('term');
        
        if (empty($term)) {
            return new JsonResponse([]);
        }
        
        $lieux = $lieuRepository->findBySearch($term);
        
        $result = [];
        foreach ($lieux as $lieu) {
            $result[] = [
                'id' => $lieu->getLieuid(),
                'name' => $lieu->getLieuname(),
                'address' => $lieu->getLieuaddress(),
                'category' => $lieu->getLieucategory(),
                'url' => $this->generateUrl('admin_lieu_show', ['id' => $lieu->getLieuid()])
            ];
        }
        
        return new JsonResponse($result);
    }

    #[Route('/admin/lieu/edit/{id}', name: 'admin_lieu_edit')]
    public function editLieu(Request $request, ?Lieu $lieu, EntityManagerInterface $em): Response
    {
        if (!$lieu) {
            throw $this->createNotFoundException('Lieu non trouvé.');
        }
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
    public function deleteLieu(?Lieu $lieu, EntityManagerInterface $em): Response
    {
        if (!$lieu) {
            throw $this->createNotFoundException('Lieu non trouvé.');
        }
        $em->remove($lieu);
        $em->flush();

        $this->addFlash('success', 'Lieu supprimé avec succès.');
        return $this->redirectToRoute('admin_lieux');
    }

    #[Route('/admin/lieu/{id}', name: 'admin_lieu_show')]
    public function showLieu(?Lieu $lieu, EventRepository $eventRepo): Response
    {
        if (!$lieu) {
            throw $this->createNotFoundException('Lieu non trouvé.');
        }
        $events = $eventRepo->findBy(['lieu' => $lieu]);
        return $this->render('back_office/exploration/lieu_show.html.twig', [
            'lieu' => $lieu,
            'events' => $events,
        ]);
    }
    #[Route('/admin/event/add/{id}', name: 'admin_event_add')]
public function addEvent(Request $request, ?Lieu $lieu, EntityManagerInterface $em): Response
{
    if (!$lieu) {
        throw $this->createNotFoundException('Lieu non trouvé.');
    }

    $event = new Event();
    $event->setLieu($lieu);

    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $form->get('eventimage')->getData();
        if ($file) {
            $filename = uniqid().'.'.$file->guessExtension();
            $file->move($this->getParameter('your_upload_directory'), $filename);
            $event->setEventimage($filename);
        }

        $em->persist($event);
        $em->flush();
        $this->addFlash('success', 'Événement ajouté avec succès !');
        return $this->redirectToRoute('admin_lieu_show', ['id' => $lieu->getLieuid()]);
    }

    return $this->render('back_office/exploration/event_add.html.twig', [
        'form' => $form->createView(),
        'lieu' => $lieu,
    ]);
}

    #[Route('/admin/event/edit/{id}', name: 'admin_event_edit')]
    public function editEvent(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('admin_lieu_show', ['id' => $event->getLieu()->getLieuid()]);
        }

        return $this->render('back_office/exploration/event_edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'lieu' => $event->getLieu(),
        ]);
    }

    #[Route('/admin/event/delete/{id}', name: 'admin_event_delete', methods: ['POST'])]
    public function deleteEvent(Event $event, EntityManagerInterface $em): Response
    {
        $lieuId = $event->getLieu()->getLieuid();
        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Événement supprimé avec succès.');
        return $this->redirectToRoute('admin_lieu_show', ['id' => $lieuId]);
    }

    #[Route('/admin/statistiques/lieux', name: 'lieu_statistiques')]
    public function index(
        EventRepository $eventRepository,
        LieuRepository $lieuRepository,
        ReservationEventRepository $reservationRepository,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Statistiques générales
        $totalLieux = count($lieuRepository->findAll());
        $totalEvents = count($eventRepository->findAll());
        $totalReservations = count($reservationRepository->findAll());
        $totalReviews = count($reviewRepository->findAll());

        // TOP 5 événements les plus réservés
        $topEvents = $eventRepository->findMostReservedEvents(5);
        
        // TOP 5 lieux les mieux notés
        $topLieux = $lieuRepository->findTopRated(5);
        
        // Derniers commentaires (reviews)
        $latestReviews = $reviewRepository->findBy([], ['reviewdate' => 'DESC'], 10);
        
        // Récupérer les statistiques de réservation par mois
        $reservationsByMonth = $reservationRepository->countReservationsByMonth();
        
        // Statistiques par catégorie d'événements
        $eventsByCategory = $eventRepository->countEventsByCategory();
        
        // Statistiques par catégorie de lieux
        $lieuxByCategory = $lieuRepository->countLieuxByCategory();
        
        // Taux d'occupation des événements (réservations/capacité max)
        $eventsOccupancyRate = $eventRepository->calculateOccupancyRate();

        return $this->render('back_office/exploration/statistiques.html.twig', [
            'totalLieux' => $totalLieux,
            'totalEvents' => $totalEvents,
            'totalReservations' => $totalReservations,
            'totalReviews' => $totalReviews,
            'topEvents' => $topEvents,
            'topLieux' => $topLieux,
            'latestReviews' => $latestReviews,
            'reservationsByMonth' => $reservationsByMonth,
            'eventsByCategory' => $eventsByCategory,
            'lieuxByCategory' => $lieuxByCategory,
            'eventsOccupancyRate' => $eventsOccupancyRate
        ]);
    }
}