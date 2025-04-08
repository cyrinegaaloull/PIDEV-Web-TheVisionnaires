<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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



    
    #[Route('/admin/clubs/export', name: 'admin_club_export')]
    public function exportClubs(ClubRepository $clubRepository): Response
    {
        $clubs = $clubRepository->findAll();
        
        // CSV header
        $csvData = "ID,Nom,Description,Catégorie,Logo,Contact,Localisation,Date de création,Nombre de membres,Horaires,Image de bannière\n";
        
        foreach ($clubs as $club) {
            $csvData .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"'."\n",
                $club->getClubid(),
                $this->escapeCsvValue($club->getClubname()),
                $this->escapeCsvValue($club->getClubdescription()),
                $this->escapeCsvValue($club->getClubcategory() ?? ''),
                $this->escapeCsvValue($club->getClublogo()),
                $this->escapeCsvValue($club->getClubcontact()),
                $this->escapeCsvValue($club->getClublocation()),
                $club->getCreationdate()->format('d/m/Y'),
                $club->getMemberscount(),
                $this->escapeCsvValue($club->getScheduleinfo()),
                $this->escapeCsvValue($club->getBannerimage())
            );
        }

        $response = new Response($csvData);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="clubs_export_'.date('Y-m-d').'.csv"');
        
        return $response;
    }

    private function escapeCsvValue($value): string
    {
        return str_replace('"', '""', $value);
    }
}
