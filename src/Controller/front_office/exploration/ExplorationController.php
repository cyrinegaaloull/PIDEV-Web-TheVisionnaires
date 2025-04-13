<?php

namespace App\Controller\front_office\exploration;
use App\Entity\Lieu;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;

use App\Form\LieuType;
use App\Entity\Review;
use App\Repository\UsersRepository;
use App\Service\NeutrinoService;
use App\Service\NotificationService;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;


class ExplorationController extends AbstractController
{
    #[Route('/exploration', name: 'app_exploration')]
    public function index(Request $request, LieuRepository $lieuRepository, ReviewRepository $reviewRepo): Response
    {
        $filter = $request->query->get('filter');
        $lat = $request->query->get('lat');
        $lon = $request->query->get('lon');
        $search = trim($request->query->get('search', ''));

        $simulateUser = false;
        $user = $simulateUser ? ['username' => 'John Doe', 'profile_picture' => 'default_profile_pic.jpg'] : null;

        if ($filter === 'nearest' && $lat && $lon) {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findByNearest((float)$lat, (float)$lon);
        } elseif ($search !== '') {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findBySearch($search);
        } else {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findNonFavorites();
        }
        
        // add this inside index() before rendering
        $ratings = [];
        foreach ($lieux as $lieu) {
            $ratings[$lieu->getLieuid()] = $reviewRepo->calculateAverageRating($lieu->getLieuid());
        }

        if ($request->isXmlHttpRequest() || $request->query->get('_ajax') === '1') {
            return $this->render('front_office/exploration/_lieux_results.html.twig', [
                'lieux' => $lieux,
                'favorites' => $favorites,
                'ratings' => $ratings,
            ]);
            
        }
        
        return $this->render('front_office/exploration/exploration.html.twig', [
            'lieux' => $lieux,
            'favorites' => $favorites,
            'ratings' => $ratings,
            'user' => $user,
        ]);        
        
    }
    #[Route('/toggle-favorite/{id}', name: 'toggle_favorite', methods: ['POST'])]
public function toggleFavorite(Lieu $lieu, EntityManagerInterface $em): JsonResponse
{
    $lieu->setIsfavorite(!$lieu->getIsfavorite());
    $em->flush();

    return new JsonResponse(['status' => 'success', 'favorite' => $lieu->getIsFavorite()]);
}

#[Route('/lieu/{id}', name: 'lieu_details')]
public function lieuDetails(int $id, LieuRepository $lieuRepository, EventRepository $eventRepo, ReviewRepository $reviewRepo): Response
{
    $simulateUser = false;
    $user = $simulateUser ? ['username' => 'John Doe', 'profile_picture' => 'default_profile_pic.jpg'] : null;
    $lieu = $lieuRepository->find($id);
    if (!$lieu) {
        throw $this->createNotFoundException('Lieu non trouvé.');
    }

    $events = $eventRepo->findBy(['lieuid' => $lieu->getLieuid()]);
    $reviews = $reviewRepo->findBy(['lieuid' => $lieu->getLieuid()]);

    return $this->render('front_office/exploration/lieu_details.html.twig', [
        'lieu' => $lieu,
        'events' => $events,
        'reviews' => $reviews, 'user' => $user,
    ]);
}

#[Route('/lieu/{id}/review', name: 'add_review', methods: ['POST'])]
public function addReview(
    int $id,
    Request $request,
    EntityManagerInterface $em,
    LieuRepository $lieuRepository,
    UsersRepository $userRepo,
    NeutrinoService $neutrino
): Response {
    $lieu = $lieuRepository->find($id);
    if (!$lieu) {
        throw $this->createNotFoundException('Lieu introuvable');
    }

    $comment = trim($request->request->get('comment'));
    $rating = (float) $request->request->get('rating');

    if (!$neutrino->isClean($comment)) {
        $this->addFlash('danger', 'Votre commentaire contient des mots inappropriés.');
        return $this->redirectToRoute('lieu_details', ['id' => $id]);
    }

    // ⚠️ Simulate or manually fetch a user (temporary)
    $user = $userRepo->findOneBy(['username' => 'cyrine']);

    $review = new Review();
    $review->setComment($comment);
    $review->setRating($rating);
    $review->setReviewdate(new \DateTime());
    $review->setUserid($user->getUserId());
    $review->setLieuid($lieu->getLieuid());

    $em->persist($review);
    $em->flush();

    $this->addFlash('success', 'Merci pour votre avis !');

    return $this->redirectToRoute('lieu_details', ['id' => $id]);
}

#[Route('/event/{id}', name: 'event_details')]
public function eventDetails(int $id, EventRepository $eventRepo, UsersRepository $userRepo): Response
{
    // ⚠️ Simulate or manually fetch a user (temporary)
    $user = $userRepo->findOneBy(['username' => 'cyrine']);
    $event = $eventRepo->find($id);
    if (!$event) {
        throw $this->createNotFoundException('Événement introuvable.');
    }

    return $this->render('front_office/exploration/event_details.html.twig', [
        'event' => $event, 'user' => $user,
    ]);
}
#[Route('/event/{id}/notify', name: 'event_notify', methods: ['POST'])]
public function notifyUser(
    int $id,
    EventRepository $eventRepo,
    NotificationService $notifier,
    UsersRepository $userRepo 
): Response {
    $event = $eventRepo->find($id);
    if (!$event) {
        throw $this->createNotFoundException('Event not found');
    }

    $user = $userRepo->findOneBy(['username' => 'cyrine']);
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur introuvable');
    }

    $userPhone = '+21653968669'; //hardcoded
    $userEmail = $user->getEmail(); 
    $notifier->sendWhatsApp($userPhone, "🎉 Rappel: L’événement '{$event->getEventname()}' aura lieu le " . $event->getEventdate()->format('d/m/Y'));
    $notifier->sendEmail($userEmail, 'Rappel événement', "🎫 Ne manquez pas '{$event->getEventname()}' le " . $event->getEventdate()->format('d/m/Y'));

    try {
        $notifier->sendWhatsApp($userPhone, "🎉 Rappel: L’événement '{$event->getEventname()}' aura lieu le " . $event->getEventdate()->format('d/m/Y'));
        $notifier->sendEmail($userEmail, 'Rappel événement', "🎫 Ne manquez pas '{$event->getEventname()}' le " . $event->getEventdate()->format('d/m/Y'));
        $this->addFlash('success', 'Notification envoyée avec succès.');
    } catch (\Throwable $e) {
        $this->addFlash('danger', '❌ Échec de l’envoi de la notification: ' . $e->getMessage());
    }    
    
    return $this->redirectToRoute('lieu_details', ['id' => $event->getLieuid()]);
}
#[Route('/recommendations', name: 'weather_recommendations')]
public function recommendByWeather(
    Request $request,
    LieuRepository $repo,
    ReviewRepository $reviewRepo
): Response {
    
    $weather = $request->query->get('weather', 'Clear');
    $lat = $request->query->get('lat');
    $lon = $request->query->get('lon');

    $categories = match ($weather) {
        'Clear' => ['PLAGE', 'PARK', 'STADE', 'CENTRE_SHOPPING'],
        'Clouds', 'Mist', 'Fog' => ['HOTEL', 'RESTAURANT', 'CINEMA', 'CENTRE_SHOPPING'],
        'Rain', 'Drizzle', 'Thunderstorm' => ['CINEMA', 'MUSEE', 'LIBRAIRIE', 'CENTRE_SHOPPING'],
        'Snow' => ['HOTEL', 'LIBRAIRIE', 'CENTRE_SHOPPING'],
        default => ['RESTAURANT', 'HOTEL', 'CENTRE_SHOPPING'],
    };
    dump([$weather, $lat, $lon, $categories]);

    

    if ($lat && $lon) {
        $recommended = $repo->createQueryBuilder('l')
        ->where('l.lieucategory IN (:categories)')
        ->setParameter('categories', $categories)
        ->getQuery()
        ->getResult();
        } else {
        $recommended = $repo->findBy(['lieucategory' => $categories]);
        dump([
            'weather' => $weather,
            'lat' => $lat,
            'lon' => $lon,
            'categories' => $categories,
            'results_count' => count($recommended),
            'results' => $recommended,
        ]);
        exit;
        
    }

    $ratings = [];
    foreach ($recommended as $lieu) {
        $ratings[$lieu->getLieuid()] = $reviewRepo->calculateAverageRating($lieu->getLieuid());
    }

    return $this->render('front_office/exploration/_weather_recommendations.html.twig', [
        'recommended' => $recommended,
        'ratings' => $ratings,
        'weather' => $weather,           
        'categories' => $categories,     
    ]);
    
}
#[Route('/event/{id}/reserve', name: 'event_reserve', methods: ['POST'])]
public function reserveTicket(int $id, EventRepository $eventRepo, EntityManagerInterface $em): Response
{
    $event = $eventRepo->find($id);
    if (!$event) {
        throw $this->createNotFoundException('Événement introuvable');
    }

    if ($event->getReservedtickets() >= $event->getMaxtickets()) {
        $this->addFlash('danger', 'Plus de billets disponibles.');
        return $this->redirectToRoute('event_details', ['id' => $id]);
    }

    $event->incrementReservedTickets();
    $em->flush();

    $this->addFlash('success', '🎫 Réservation réussie !');
    return $this->redirectToRoute('event_details', ['id' => $id]);
}
#[Route('/mail', name: 'test_mail')]
public function testMail(NotificationService $notifier): Response
{
    try {
        $notifier->sendEmail('cyrinegaaloul29@gmail.com', 'Test Subject', 'Body message here');
        return new Response('✅ Email sent!');
    } catch (\Throwable $e) {
        return new Response('❌ Failed: ' . $e->getMessage());
    }
}

}

