<?php

namespace App\Controller\back_office\clubs;

use App\Entity\Activite;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class ActiviteAdminController extends AbstractController
{
    #[Route('/admin/activites', name: 'admin_activite_list')]
    public function list(
        Request $request,
        ActiviteRepository $activiteRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $filter = $request->query->get('filter');
        $status = $request->query->get('status');

        $query = $activiteRepository->createQueryBuilder('a')
            ->leftJoin('a.clubid', 'c')
            ->addSelect('c');

        if ($search) {
            $query->andWhere('a.activitename LIKE :search OR c.clubname LIKE :search')
                 ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $query->andWhere('a.activitestatus = :status')
                 ->setParameter('status', $status);
        }

        switch ($filter) {
            case 'alpha_asc':
                $query->orderBy('a.activitename', 'ASC');
                break;
            case 'alpha_desc':
                $query->orderBy('a.activitename', 'DESC');
                break;
            case 'date_asc':
                $query->orderBy('a.activitedate', 'ASC');
                break;
            case 'date_desc':
                $query->orderBy('a.activitedate', 'DESC');
                break;
            default:
                $query->orderBy('a.activitedate', 'DESC');
        }

        $activites = $paginator->paginate(
            $query->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('back_office/clubs/_activities-table.html.twig', [
                'activites' => $activites,
                'total_activites' => $activiteRepository->count([])
            ]);
        }

        return $this->render('back_office/clubs/activities-list.html.twig', [
            'activites' => $activites,
            'total_activites' => $activiteRepository->count([])
        ]);
    }

    #[Route('/admin/activites/export', name: 'admin_activite_export')]
    public function exportActivities(ActiviteRepository $activiteRepository): Response
    {
        $activities = $activiteRepository->findAllWithClub();
        
        // CSV header
        $csvData = "ID,Nom,Description,Club,Date,Heure début,Heure fin,Localisation,Type,Image,Statut\n";
        
        foreach ($activities as $activity) {
            $csvData .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"'."\n",
                $activity->getActiviteid(),
                $this->escapeCsvValue($activity->getActivitename()),
                $this->escapeCsvValue($activity->getActivitedescription()),
                $this->escapeCsvValue($activity->getClubid()->getClubname()),
                $activity->getActivitedate()->format('d/m/Y'),
                $activity->getStarttime()->format('H:i'),
                $activity->getEndtime()->format('H:i'),
                $this->escapeCsvValue($activity->getActivitelocation()),
                $this->escapeCsvValue($activity->getActivitetype()),
                $this->escapeCsvValue($activity->getActiviteimage()),
                $this->escapeCsvValue($activity->getActivitestatus())
            );
        }

        $response = new Response($csvData);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="activities_export_'.date('Y-m-d').'.csv"');
        
        return $response;
    }

    private function escapeCsvValue($value): string
    {
        return str_replace('"', '""', $value);
    }

    #[Route('/admin/activites/calendar', name: 'admin_activite_calendar')]
    public function calendar(ActiviteRepository $activiteRepository): Response
    {
        $activities = $activiteRepository->findAll();
    $events = [];

    foreach ($activities as $activity) {
        $events[] = [
            'id' => $activity->getActiviteid(),
            'title' => $activity->getActivitename(),
            'start' => $activity->getActivitedate()->format('Y-m-d'),
            'color' => '#7367F0',
            'extendedProps' => [
                'description' => $activity->getActivitedescription(),
                'club' => $activity->getClubid()?->getClubname()
            ]
        ];
    }

        return $this->render('back_office/clubs/calendar.html.twig', [
            'events' => $events,
        ]);
    }
    

}