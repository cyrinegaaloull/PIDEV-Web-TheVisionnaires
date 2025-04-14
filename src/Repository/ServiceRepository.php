<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
     * @return Service[] Returns an array of Service objects
     */
    public function findAllWithEtablissement()
{
    return $this->createQueryBuilder('s')
        ->leftJoin('s.etablissement', 'e')
        ->addSelect('e')
        ->getQuery()
        ->getResult();
}

    /**
     * @return Service[] Returns an array of Service objects with price filter
     */
    public function findByPriceMax(float $maxPrice): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.serviceprix <= :price')
            ->setParameter('price', $maxPrice)
            ->orderBy('s.serviceprix', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Service[] Returns an array of Service objects with price filter and establishment
     */
    public function findByPriceMaxAndEtablissement(float $maxPrice, int $etabId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.serviceprix <= :price')
            ->andWhere('s.etabid = :etabId')
            ->setParameter('price', $maxPrice)
            ->setParameter('etabId', $etabId)
            ->orderBy('s.serviceprix', 'ASC')
            ->getQuery()
            ->getResult();
    }
   
}