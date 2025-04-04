<?php

namespace App\Controller\front_office\exploration;
use App\Entity\Lieu;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;

use App\Entity\Review;
use App\Repository\UsersRepository;
use App\Service\NeutrinoService;

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
}
