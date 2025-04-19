<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * @return Avis[] Returns an array of Avis objects
     */
    public function findByEtablissement(int $etabId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.etabid = :val')
            ->setParameter('val', $etabId)
            ->orderBy('a.dateavis', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find avis with minimum rating
     * 
     * @return Avis[] Returns an array of Avis objects
     */
    public function findByMinRating(int $minRating): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.rating >= :rating')
            ->setParameter('rating', $minRating)
            ->orderBy('a.rating', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find avis created in a specific period
     * 
     * @param string $period 'today', 'week', 'month', 'year'
     * @return Avis[] Returns an array of Avis objects
     */
    public function findByPeriod(string $period): array
    {
        $qb = $this->createQueryBuilder('a');
        
        $now = new \DateTime();
        
        switch ($period) {
            case 'today':
                $date = $now->format('Y-m-d');
                $qb->andWhere('a.dateavis >= :date')
                   ->setParameter('date', $date.' 00:00:00');
                break;
            case 'week':
                $date = (clone $now)->modify('-7 days');
                $qb->andWhere('a.dateavis >= :date')
                   ->setParameter('date', $date->format('Y-m-d H:i:s'));
                break;
            case 'month':
                $date = (clone $now)->modify('-1 month');
                $qb->andWhere('a.dateavis >= :date')
                   ->setParameter('date', $date->format('Y-m-d H:i:s'));
                break;
            case 'year':
                $date = (clone $now)->modify('-1 year');
                $qb->andWhere('a.dateavis >= :date')
                   ->setParameter('date', $date->format('Y-m-d H:i:s'));
                break;
        }
        
        return $qb->orderBy('a.dateavis', 'DESC')
                 ->getQuery()
                 ->getResult();
    }
}