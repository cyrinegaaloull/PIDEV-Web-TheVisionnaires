<?php

namespace App\Controller\front_office\clubs;

use App\Repository\ClubRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activite;
use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;

class ClubController extends AbstractController
{
    #[Route('/clubs', name: 'app_clubs')]
public function index(Request $request, ClubRepository $clubRepository, PaginatorInterface $paginator): Response
{
    $user = [
        'username' => 'John Doe',
        'profile_picture' => 'default_profile_pic.jpg',
    ];

    $search = $request->query->get('search');
    $category = $request->query->get('category');

    $queryBuilder = $clubRepository->createQueryBuilder('c');

       if ($search) {
        $queryBuilder->andWhere('c.clubname LIKE :search OR c.clublocation LIKE :search')
                     ->setParameter('search', '%' . $search . '%');
    }

    if ($category) {
        $queryBuilder->andWhere('c.clubcategory = :category')
                     ->setParameter('category', $category);
    }

    $pagination = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        9
    );

    $categoriesRaw = $clubRepository->createQueryBuilder('c')
    ->select('DISTINCT c.clubcategory')
    ->where('c.clubcategory IS NOT NULL')
    ->getQuery()
    ->getScalarResult();

    $categories = array_map(fn($row) => $row['clubcategory'], $categoriesRaw);
    sort($categories); 



    return $this->render('front_office/clubs/clubs.html.twig', [
        'clubs' => $pagination,
        'user' => $user,
        'categories' => $categories,
    ]);
}

#[Route('/clubs/{id}', name: 'app_club_details')]
public function show(
    int $id,
    ClubRepository $clubRepository,
    EntityManagerInterface $em
): Response {
    $user = [
        'username' => 'John Doe',
        'profile_picture' => 'default_profile_pic.jpg',
    ];

    // Fetch the club
    $club = $clubRepository->find($id);

    if (!$club) {
        throw $this->createNotFoundException('Ce club est introuvable.');
    }

    // Count all activities (regardless of status)
    $activitiesCount = $em->getRepository(Activite::class)
        ->createQueryBuilder('a')
        ->select('COUNT(a.activiteid)')
        ->where('a.clubid = :club')
        ->setParameter('club', $club)
        ->getQuery()
        ->getSingleScalarResult();

    // Get only A_venir activities
    $upcomingActivities = $em->getRepository(Activite::class)
        ->createQueryBuilder('a')
        ->where('a.clubid = :club')
        ->andWhere('a.activitestatus = :status')
        ->setParameter('club', $club)
        ->setParameter('status', 'A_venir')
        ->orderBy('a.activitedate', 'ASC')
        ->getQuery()
        ->getResult();

    // Related clubs from same category
    $relatedClubs = $clubRepository->createQueryBuilder('c')
        ->where('c.clubcategory = :cat')
        ->andWhere('c.clubid != :id')
        ->setParameter('cat', $club->getClubcategory())
        ->setParameter('id', $club->getClubid())
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();

    return $this->render('front_office/clubs/club-details.html.twig', [
        'club' => $club,
        'user' => $user,
        'upcomingActivities' => $upcomingActivities,
        'activitiesCount' => $activitiesCount,
        'relatedClubs' => $relatedClubs,
    ]);
}




}
