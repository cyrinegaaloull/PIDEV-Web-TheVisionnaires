<?php

namespace App\Repository;

use App\Entity\Activite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activite>
 *
 * @method Activite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activite[]    findAll()
 * @method Activite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activite::class);
    }

    /**
     * Builds a query for filtering/searching/pagination.
     * (This is actually used directly in the controller via ->createQueryBuilder)
     */
    public function getFilteredQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('a')
                    ->leftJoin('a.clubid', 'c')
                    ->addSelect('c');
    }

    // Optional: method to fetch only upcoming activities
    public function findUpcomingActivities(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.activitestatus = :status')
            ->setParameter('status', 'A_venir')
            ->orderBy('a.activitedate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithClub()
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.clubid', 'c')
        ->addSelect('c')
        ->getQuery()
        ->getResult();
}
}
