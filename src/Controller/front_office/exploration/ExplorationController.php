<?php

namespace App\Controller\front_office\exploration;
use App\Entity\Lieu;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;

use App\Entity\Review;
use App\Service\NeutrinoService;
use App\Service\NotificationService;
use App\Repository\ReservationEventRepository;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Dompdf\Dompdf;




class ExplorationController extends AbstractController
{
    #[Route('/exploration', name: 'app_exploration')]
public function index(
    Request $request,
    LieuRepository $lieuRepository,
    ReviewRepository $reviewRepo,
): Response{
        $filter = $request->query->get('filter');
        $lat = $request->query->get('lat');
        $lon = $request->query->get('lon');
        $search = trim($request->query->get('search', ''));
/** @var \App\Entity\Users $user */
        $user = $this->getUser();

        if ($filter === 'nearest' && $lat && $lon) {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findByNearest((float)$lat, (float)$lon);
        } elseif ($filter === 'top-rated') {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findTopRated(); 
        } elseif ($search !== '') {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findBySearch($search);
        } else {
            $favorites = $lieuRepository->findFavorites();
            $lieux = $lieuRepository->findNonFavorites();
        }
        
        
        $ratings = [];
        foreach ($lieux as $lieu) {
            $ratings[$lieu->getLieuid()] = $reviewRepo->calculateAverageRating($lieu->getLieuid());
        }

        if ($request->isXmlHttpRequest() || $request->query->get('_ajax') === '1') {
            return $this->render('front_office/exploration/_lieux_results.html.twig', [
                'lieux' => $lieux,
                'favorites' => $favorites,
                'ratings' => $ratings,
                'search' => $search,
                'user' => $user,
            ]);
            
        }
        return $this->render('front_office/exploration/exploration.html.twig', [
            'lieux' => $lieux,
            'favorites' => $favorites,
            'ratings' => $ratings,
            'user' => $user,
            'search' => $search,
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
    /** @var \App\Entity\Users $user */
    $user = $this->getUser();

    $lieu = $lieuRepository->find($id);
    if (!$lieu) {
        throw $this->createNotFoundException('Lieu non trouvé.');
    }

    $events = $eventRepo->findBy(['lieu' => $lieu]);
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
    NeutrinoService $neutrino,
    ValidatorInterface $validator
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
/** @var \App\Entity\Users $user */
    $user = $this->getUser();
    $review = new Review();
    $review->setComment($comment);
    $review->setRating($rating);
    $review->setReviewdate(new \DateTime());
    $review->setUser($user);
    $review->setLieuid($lieu);

    $errors = $validator->validate($review);

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $this->addFlash('danger', $error->getMessage());
        }
        return $this->redirectToRoute('lieu_details', ['id' => $id]);
    }

    $em->persist($review);
    $em->flush();

    $this->addFlash('success', 'Merci pour votre avis !');
    return $this->redirectToRoute('lieu_details', ['id' => $id]);
}

#[Route('/event/{id}', name: 'event_details')]
public function eventDetails(
    int $id, 
    EventRepository $eventRepo, 
    ReservationEventRepository $reservationRepo
): Response
{
    /** @var \App\Entity\Users $user */
    $user = $this->getUser();
    $event = $eventRepo->find($id);

    if (!$event) {
        throw $this->createNotFoundException('Événement introuvable.');
    }

    $userReservations = $reservationRepo->findBy(['user' => $user]);

    return $this->render('front_office/exploration/event_details.html.twig', [
        'event' => $event,
        'user' => $user,
        'userReservations' => $userReservations,
    ]);
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
public function reserveTicket(
    int $id,
    EventRepository $eventRepo,
    NotificationService $notifier,
    EntityManagerInterface $em
): Response {
    $event = $eventRepo->find($id);
    if (!$event) {
        throw $this->createNotFoundException('Événement introuvable.');
    }

    /** @var \App\Entity\Users $user */
    $user = $this->getUser();
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur introuvable.');
    }

    $existingReservation = $em->getRepository(\App\Entity\ReservationEvent::class)
        ->findOneBy(['user' => $user, 'event' => $event]);
    if ($existingReservation) {
        $this->addFlash('info', 'ℹ️ Vous avez déjà réservé cet événement.');
        return $this->redirectToRoute('event_details', ['id' => $id]);
    }

    $reservation = new \App\Entity\ReservationEvent();
    $reservation->setUser($user);
    $reservation->setEvent($event);
    $reservation->setReservationDate(new \DateTime());

    $em->persist($reservation);
    $event->setReservedtickets($event->getReservedtickets() + 1);
    $em->flush();

    // --- DEBUG: Create var/log directory if needed
    $logDir = $this->getParameter('kernel.project_dir') . '/var/log';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }
    $debugFile = $logDir . '/qr_debug.log';

    file_put_contents($debugFile, "Event ID: {$event->getEventid()}\n");

    // --- Generate QR Code using Endroid QR Code
    $eventUrl = 'http://127.0.0.1:8000/event/' . $event->getEventid();
    $qrCode = QrCode::create($eventUrl)
        ->setSize(300);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    $qrContent = $result->getString();

    // Verify that the content is a valid PNG
    $pngHeader = "\x89PNG\r\n\x1A\n";
    if (substr($qrContent, 0, 8) !== $pngHeader) {
        file_put_contents($debugFile, "QR code content is not a valid PNG\n", FILE_APPEND);
        throw new \Exception('QR code content is not a valid PNG image.');
    }

    // Base64 encode the QR code for embedding in the PDF
    $base64Content = base64_encode($qrContent);
    $embeddedImage = "data:image/png;base64,{$base64Content}";

    // --- Generate PDF with reservation details and QR code
    $userEmail = $user->getEmail();
    $eventName = $event->getEventname();
    $eventDate = $event->getEventdate()?->format('d/m/Y') ?? 'bientôt';
    $reservationDate = $reservation->getReservationDate()->format('d/m/Y H:i:s');

    // HTML content for the PDF
    $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; }
            .ticket { max-width: 600px; margin: auto; padding: 20px; border: 2px dashed #4CAF50; border-radius: 15px; background: #f9f9f9; }
            h1 { color: #4CAF50; }
            hr { margin: 20px 0; }
            img { width: 300px; max-width: 100%; height: auto; }
            .footer { font-size: 0.8em; color: #999; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class=\"ticket\">
            <h1>Billet LocalLens</h1>
            <p>Merci <strong>{$user->getUsername()}</strong> pour votre réservation !</p>
            <hr>
            <p><strong>Événement :</strong> {$eventName}</p>
            <p><strong>Date de l'événement :</strong> {$eventDate}</p>
            <p><strong>Date de réservation :</strong> {$reservationDate}</p>
            <p><strong>Email :</strong> {$userEmail}</p>
            <p style=\"margin-top:20px;\">Votre QR Code :</p>
            <img src=\"{$embeddedImage}\" alt=\"QR Code\" />
            <hr>
            <p class=\"footer\">Présentez ce billet à l'entrée de l'événement.</p>
        </div>
    </body>
    </html>";

    // Initialize Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfContent = $dompdf->output();

    // Save the PDF to disk with a unique filename
    $ticketDir = $this->getParameter('kernel.project_dir') . '/public/tickets';
    if (!is_dir($ticketDir)) {
        mkdir($ticketDir, 0775, true);
    }
    $ticketFilename = 'ticket_reservation_' . $reservation->getReservationId() . '.pdf';
    $ticketFilePath = $ticketDir . '/' . $ticketFilename;
    file_put_contents($ticketFilePath, $pdfContent);
    file_put_contents($debugFile, "PDF ticket saved to $ticketFilePath. Size: " . strlen($pdfContent) . " bytes\n", FILE_APPEND);

    // Construct the URL for the PDF
    $ticketPublicUrl = 'http://127.0.0.1:8000/tickets/' . $ticketFilename;

    // --- Email
    $subject = 'Votre billet pour ' . $eventName;
    $message = "
    <div style=\"max-width:600px;margin:auto;background:#f9f9f9;border:2px dashed #4CAF50;border-radius:15px;padding:20px;font-family:Arial,sans-serif;color:#333;\">
        <h1 style=\"text-align:center;color:#4CAF50;margin-bottom:10px;\">Billet LocalLens</h1>
        <p style=\"text-align:center;\">Merci <strong>{$user->getUsername()}</strong> pour votre réservation !</p>
        <hr style=\"margin:20px 0;\">
        <p><strong>Événement :</strong> {$eventName}</p>
        <p><strong>Date :</strong> {$eventDate}</p>
        <p style=\"text-align:center;margin-top:20px;\">Cliquez ci-dessous pour télécharger votre billet (PDF) :</p>
        <p style=\"text-align:center;\"><a href=\"{$ticketPublicUrl}\" target=\"_blank\">Télécharger le billet</a></p>
        <hr style=\"margin:20px 0;\">
        <p style=\"font-size:0.8em;text-align:center;color:#999;\">Cet email contient votre billet officiel LocalLens.</p>
    </div>";

    try {
        $notifier->sendEmail($userEmail, $subject, $message);
        file_put_contents($debugFile, "Email sent successfully to $userEmail\n", FILE_APPEND);
    } catch (\Exception $e) {
        file_put_contents($debugFile, "Email sending error: " . $e->getMessage() . "\n", FILE_APPEND);
        throw new \Exception('Erreur lors de l\'envoi du billet: ' . $e->getMessage());
    }

    $this->addFlash('success', '🎫 Billet envoyé avec succès.');
    return $this->redirectToRoute('event_details', ['id' => $id]);
}
}