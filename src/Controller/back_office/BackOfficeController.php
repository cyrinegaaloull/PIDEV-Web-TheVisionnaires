<?php

namespace App\Controller\back_office;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ClubRepository;
use App\Repository\ActiviteRepository;

class BackOfficeController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        ClubRepository $clubRepo,
        ActiviteRepository $activiteRepo,
        EntityManagerInterface $em,
    ): Response {
        $totalClubs = $clubRepo->count([]);
        $upcomingEvents = $activiteRepo->createQueryBuilder('a')
            ->select('COUNT(a.activiteid)')
            ->where('a.activitestatus = :status')
            ->setParameter('status', 'A_venir')
            ->getQuery()->getSingleScalarResult();

        
            $conn = $em->getConnection();

            $sql = '
                SELECT EXTRACT(MONTH FROM activitedate) AS month, COUNT(activiteid) AS count
                FROM activite
                GROUP BY month
                ORDER BY month
            ';

            $monthlyDataRaw = $conn->executeQuery($sql)->fetchAllAssociative();

            // Normalize to 12 months
            $monthlyData = array_fill(0, 12, 0); // Always 12 slots
            foreach ($monthlyDataRaw as $row) {
                $monthIndex = (int)$row['month'] - 1; // Month 1–12 to index 0–11
                $monthlyData[$monthIndex] = (int)$row['count'];
            }


        return $this->render('back_office/dashboard.html.twig', [
            'totalClubs' => $totalClubs,
            'upcomingEvents' => $upcomingEvents,
            'monthlyData' => array_values($monthlyData),
        ]);
    }

    #[Route('/admin/flux-social', name: 'admin_flux_social')]
    public function fluxSocial(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->render('back_office/flux_social.html.twig', [
            'posts' => $posts,
        ]);
    }
}
