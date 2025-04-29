<?php

namespace App\Controller\front_office\clubs;

use App\Entity\Activite;
use App\Repository\ActiviteRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActiviteController extends AbstractController
{
    #[Route('/activites', name: 'app_activites')]
    public function index(Request $request, ActiviteRepository $activiteRepository, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();


        $search = $request->query->get('search');

        $qb = $activiteRepository->createQueryBuilder('a')
            ->leftJoin('a.clubid', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->andWhere('a.activitename LIKE :search OR c.clubname LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('front_office/clubs/activities.html.twig', [
            'activites' => $pagination,
            'user' => $user,
        ]);
    }

    #[Route('/activites/{id}', name: 'app_activite_details')]
public function show(int $id, ActiviteRepository $activiteRepository): Response
{
    $activite = $activiteRepository->find($id);

    $user = $this->getUser();


    if (!$activite) {
        throw $this->createNotFoundException('Activité non trouvée.');
    }

    return $this->render('front_office/clubs/activite-details.html.twig', [
        'activite' => $activite,
        'user' => $user,
    ]);
}

}
