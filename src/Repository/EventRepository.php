<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByLieuId(int $lieuId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.lieuid = :lieuId')
            ->setParameter('lieuId', $lieuId)
            ->orderBy('e.eventdate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
