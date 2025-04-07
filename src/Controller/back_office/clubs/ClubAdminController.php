<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClubAdminController extends AbstractController
{
    #[Route('/admin/clubs', name: 'admin_club_list')]
    public function list(
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $filter = $request->query->get('filter');

        // Get the base QueryBuilder
        $qb = $em->getRepository(Club::class)->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.clubname LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply filter
        switch ($filter) {
            case 'az':
                $qb->orderBy('c.clubname', 'ASC');
                break;
            case 'za':
                $qb->orderBy('c.clubname', 'DESC');
                break;
            case 'oldest':
                $qb->orderBy('c.creationdate', 'ASC');
                break;
            case 'newest':
                $qb->orderBy('c.creationdate', 'DESC');
                break;
            default:
                $qb->orderBy('c.creationdate', 'DESC');
        }

        // Apply pagination
        $clubs = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('back_office/clubs/_clubs_table.html.twig', [
                'clubs' => $clubs
            ]);
        }

        // Get total count for summary (optional)
        $totalClubs = $em->getRepository(Club::class)->count([]);

        return $this->render('back_office/clubs/clubs-list.html.twig', [
            'clubs' => $clubs,
            'totalClubs' => $totalClubs
        ]);
    }

    #[Route('/admin/clubs/delete/{id}', name: 'admin_club_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $club = $em->getRepository(Club::class)->find($id);

        if (!$club) {
            $this->addFlash('danger', 'Club introuvable.');
            return $this->redirectToRoute('admin_club_list');
        }

        $em->remove($club);
        $em->flush();

        $this->addFlash('success', 'Club supprimé avec succès.');
        return $this->redirectToRoute('admin_club_list');
    }
}
