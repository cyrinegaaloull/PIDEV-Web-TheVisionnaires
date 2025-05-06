<?php

namespace App\Controller\back_office\assistance;

use App\Entity\Avis;
use App\Entity\Etablissement;
use App\Repository\AvisRepository;
use App\Repository\EtablissementRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/avis', name: 'admin_avis_')]
class AdminAvisController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AvisRepository $avisRepository): Response
    {
        return $this->render('back_office/assistance/avis_list.html.twig', [
            'avis' => $avisRepository->findBy([], ['dateavis' => 'DESC']),
        ]);
    }

    #[Route('/{avisid}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avis, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $avis->getAvisid(), $request->request->get('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();

            $this->addFlash('success', 'Avis supprimé avec succès!');
        }

        return $this->redirectToRoute('admin_avis_index');
    }

    #[Route('/stats/ratings', name: 'stats_ratings', methods: ['GET'])]
    public function ratingsStats(EntityManagerInterface $entityManager): Response
    {
        // Statistiques globales sur les avis
        $ratingDistribution = $entityManager->createQuery(
            'SELECT a.rating, COUNT(a.avisid) as count
             FROM App\Entity\Avis a
             GROUP BY a.rating
             ORDER BY a.rating ASC'
        )->getResult();

        // Préparation des données pour le graphique
        $labels = [];
        $data = [];

        foreach ($ratingDistribution as $rating) {
            $labels[] = $rating['rating'] . ' étoile(s)';
            $data[] = $rating['count'];
        }

        // Obtenir les établissements les mieux notés
        $topEtablissements = $entityManager->createQuery(
            'SELECT e.etabname, AVG(a.rating) as avgRating, COUNT(a.avisid) as totalAvis
             FROM App\Entity\Avis a
             JOIN a.etablissement e
             GROUP BY e.etabid, e.etabname
             HAVING COUNT(a.avisid) > 2
             ORDER BY avgRating DESC'
        )
            ->setMaxResults(5)
            ->getResult();

        return $this->render('back_office/assistance/avis_stats.html.twig', [
            'ratingDistribution' => $ratingDistribution,
            'chartLabels' => json_encode($labels),
            'chartData' => json_encode($data),
            'topEtablissements' => $topEtablissements
        ]);
    }
}
