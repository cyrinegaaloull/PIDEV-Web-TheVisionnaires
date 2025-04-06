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

    public function findBySearch(string $term): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.lieuname LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('l.lieuname', 'ASC')
            ->getQuery()
            ->getResult();
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

    // Hydrate en respectant l’ordre
    $lieux = $this->createQueryBuilder('l')
        ->where('l.lieuid IN (:ids)')
        ->setParameter('ids', $ids)
        ->getQuery()
        ->getResult();

    // Reclasser selon l’ordre des IDs
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


}
