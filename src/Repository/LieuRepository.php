<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    /**
     * Méthode pour rechercher des lieux avec des filtres multiples
     * 
     * @param array $filters Les filtres à appliquer (nom, catégorie, adresse)
     * @return array
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('l');
        
        // Filtre par nom
        if (!empty($filters['nom'])) {
            $qb->andWhere('l.lieuname LIKE :nom')
               ->setParameter('nom', '%' . $filters['nom'] . '%');
        }
        
        // Filtre par catégorie
        if (!empty($filters['categorie'])) {
            $qb->andWhere('l.lieucategory = :categorie')
               ->setParameter('categorie', $filters['categorie']);
        }
        
        // Filtre par adresse
        if (!empty($filters['adresse'])) {
            $qb->andWhere('l.lieuaddress LIKE :adresse')
               ->setParameter('adresse', '%' . $filters['adresse'] . '%');
        }
        
        // Tri par défaut par nom
        $qb->orderBy('l.lieuname', 'ASC');
        
        return $qb->getQuery()->getResult();
    }

    public function findBySearch(string $term): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.lieuname LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('l.lieuname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver toutes les catégories distinctes
     * 
     * @return array
     */
    public function findAllCategories(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.lieucategory')
            ->where('l.lieucategory IS NOT NULL')
            ->orderBy('l.lieucategory', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findByNearest(float $lat, float $lon): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT l.lieuID
            FROM lieu l
            WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL
            ORDER BY (6371 * ACOS(
                COS(RADIANS(:lat)) * COS(RADIANS(l.latitude)) *
                COS(RADIANS(l.longitude) - RADIANS(:lon)) +
                SIN(RADIANS(:lat)) * SIN(RADIANS(l.latitude))
            )) ASC
            LIMIT 20
        ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['lat' => $lat, 'lon' => $lon]);
        $ids = array_column($resultSet->fetchAllAssociative(), 'lieuID');

        if (empty($ids)) {
            return [];
        }

        // Hydrate en respectant l'ordre
        $lieux = $this->createQueryBuilder('l')
            ->where('l.lieuid IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Reclasser selon l'ordre des IDs
        $lieuxMap = [];
        foreach ($lieux as $lieu) {
            $lieuxMap[$lieu->getLieuid()] = $lieu;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($lieuxMap[$id])) {
                $ordered[] = $lieuxMap[$id];
            }
        }

        return $ordered;
    }

    public function findFavorites(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.isfavorite = true')
            ->getQuery()
            ->getResult();
    }

    public function findNonFavorites(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.isfavorite = false OR l.isfavorite IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function findRecommendedNearest(array $categories, float $lat, float $lon): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.lieucategory IN (:categories)')
            ->setParameter('categories', $categories)
            ->addSelect('((l.latitude - :lat)*(l.latitude - :lat) + (l.longitude - :lon)*(l.longitude - :lon)) AS HIDDEN distance')
            ->setParameter('lat', $lat)
            ->setParameter('lon', $lon)
            ->orderBy('distance', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findTopRated(int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'AVG(r.rating) as avgRating', 'COUNT(r.reviewid) as reviewCount')
            ->leftJoin('App\Entity\Review', 'r', 'WITH', 'r.lieuid = l.lieuid')
            ->groupBy('l.lieuid')
            ->having('COUNT(r.reviewid) > 0')
            ->orderBy('avgRating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

     /**
     * Compte le nombre de lieux par catégorie
     *
     * @return array
     */
    public function countLieuxByCategory(): array
    {
        $result = $this->createQueryBuilder('l')
            ->select('l.lieucategory as category, COUNT(l.lieuid) as count')
            ->groupBy('l.lieucategory')
            ->getQuery()
            ->getResult();
        
        // Formater le résultat pour être utilisé dans un graphique
        $formattedResult = [
            'labels' => [],
            'data' => []
        ];
        
        foreach ($result as $row) {
            $formattedResult['labels'][] = $row['category'];
            $formattedResult['data'][] = $row['count'];
        }
        
        return $formattedResult;
    }

     /**
     * Récupère les lieux avec le plus d'événements
     *
     * @param int $limit Nombre de lieux à récupérer
     * @return array
     */
    public function findLieuxWithMostEvents(int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'COUNT(e.eventid) as eventCount')
            ->leftJoin('App\Entity\Event', 'e', 'WITH', 'e.lieu = l')
            ->groupBy('l.lieuid')
            ->orderBy('eventCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

     /**
     * Récupère les lieux les plus populaires (ceux ayant le plus de réservations pour leurs événements)
     *
     * @param int $limit Nombre de lieux à récupérer
     * @return array
     */
    public function findMostPopularLieux(int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'COUNT(r.reservationId) as reservationCount')
            ->leftJoin('App\Entity\Event', 'e', 'WITH', 'e.lieu = l')
            ->leftJoin('App\Entity\ReservationEvent', 'r', 'WITH', 'r.event = e')
            ->groupBy('l.lieuid')
            ->orderBy('reservationCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


}