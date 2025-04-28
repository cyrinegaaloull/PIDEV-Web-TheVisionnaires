<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByLieuId(int $lieuId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.lieuid = :lieuId')
            ->setParameter('lieuId', $lieuId)
            ->orderBy('r.reviewdate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function calculateAverageRating(int $lieuId): float
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT AVG(rating) as avg FROM review WHERE lieuID = :lieuId';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['lieuId' => $lieuId])->fetchAssociative();

        return round((float)$result['avg'], 1);
    }

    /**
     * Récupère les derniers commentaires avec des informations sur le lieu et l'utilisateur
     *
     * @param int $limit Nombre de commentaires à récupérer
     * @return array
     */
    public function findLatestReviewsWithDetails(int $limit = 10): array
    {
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery(
            'SELECT r, l.lieuname, u.username
            FROM App\Entity\Review r
            JOIN App\Entity\Lieu l WITH r.lieuid = l.lieuid
            JOIN App\Entity\Users u WITH r.userid = u.userid
            ORDER BY r.reviewdate DESC'
        )->setMaxResults($limit);
        
        return $query->getResult();
    }
    
    /**
     * Compte le nombre de commentaires par mois pour l'année en cours
     *
     * @return array
     */
    public function countReviewsByMonth(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $currentYear = date('Y');
        
        $sql = "
            SELECT 
                MONTH(reviewDate) as month,
                COUNT(reviewID) as count
            FROM 
                review
            WHERE 
                YEAR(reviewDate) = :year
            GROUP BY 
                MONTH(reviewDate)
            ORDER BY 
                month ASC
        ";
        
        $result = $conn->executeQuery($sql, ['year' => $currentYear])->fetchAllAssociative();
        
        // Formater les données pour un graphique
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        $data = array_fill(1, 12, 0); // Initialiser avec 0 pour tous les mois
        
        foreach ($result as $row) {
            $data[$row['month']] = (int)$row['count'];
        }
        
        return [
            'labels' => array_values($months),
            'data' => array_values($data)
        ];
    }
    
    /**
     * Récupère la distribution des notes (nombre de 1 étoile, 2 étoiles, etc.)
     *
     * @return array
     */
    public function getRatingDistribution(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.rating, COUNT(r.reviewid) as count')
            ->groupBy('r.rating')
            ->orderBy('r.rating', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Initialiser avec des zéros pour toutes les notes possibles
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];
        
        foreach ($result as $row) {
            $rating = (int)$row['rating'];
            if (isset($distribution[$rating])) {
                $distribution[$rating] = $row['count'];
            }
        }
        
        return [
            'labels' => array_keys($distribution),
            'data' => array_values($distribution)
        ];
    }
}