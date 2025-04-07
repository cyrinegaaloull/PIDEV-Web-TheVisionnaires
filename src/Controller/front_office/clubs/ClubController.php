<?php

namespace App\Controller\front_office\clubs;

use App\Repository\ClubRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    $categories = $clubRepository->createQueryBuilder('c')
                    ->select('DISTINCT c.clubcategory')
                    ->getQuery()
                    ->getSingleColumnResult();

    return $this->render('front_office/clubs/clubs.html.twig', [
        'clubs' => $pagination,
        'user' => $user,
        'categories' => $categories,
    ]);
}

}
