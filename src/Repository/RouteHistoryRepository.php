<?php
// src/Repository/RouteHistoryRepository.php
namespace App\Repository;

use App\Entity\RouteHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RouteHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RouteHistory::class);
    }

    public function findAllSortedByTimestamp()
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRouteByCoordinatesAndMode(
        float $departureLat, 
        float $departureLon, 
        float $arrivalLat, 
        float $arrivalLon, 
        string $transportMode
    ) {
        return $this->createQueryBuilder('r')
            ->where('r.departureLat = :departureLat')
            ->andWhere('r.departureLon = :departureLon')
            ->andWhere('r.arrivalLat = :arrivalLat')
            ->andWhere('r.arrivalLon = :arrivalLon')
            ->andWhere('r.transportMode = :transportMode')
            ->setParameters([
                'departureLat' => $departureLat,
                'departureLon' => $departureLon,
                'arrivalLat' => $arrivalLat,
                'arrivalLon' => $arrivalLon,
                'transportMode' => $transportMode
            ])
            ->orderBy('r.timestamp', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}