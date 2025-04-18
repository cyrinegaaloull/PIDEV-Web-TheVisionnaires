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
}
