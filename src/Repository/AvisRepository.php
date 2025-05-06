<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Etablissement;
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
            ->andWhere('a.etablissement = :val')
            ->setParameter('val', $this->getEntityManager()->getReference(Etablissement::class, $etabId))
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
        if ($minRating < 1 || $minRating > 5) {
            throw new \InvalidArgumentException('Minimum rating must be between 1 and 5.');
        }

        return $this->createQueryBuilder('a')
            ->andWhere('a.rating >= :rating')
            ->setParameter('rating', $minRating)
            ->orderBy('a.rating', 'DESC')
            ->getQuery()
            ->getResult();
    }




    public function findAllWithRelations()
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->addSelect('u')
            ->leftJoin('a.etablissement', 'e')
            ->addSelect('e')
            ->orderBy('a.dateavis', 'DESC')
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
        $now = new \DateTime('now', new \DateTimeZone('UTC')); // Adjust timezone as needed

        switch ($period) {
            case 'today':
                $date = (clone $now)->setTime(0, 0, 0);
                $qb->andWhere('a.dateavis >= :date')
                    ->setParameter('date', $date);
                break;
            case 'week':
                $date = (clone $now)->modify('-7 days');
                $qb->andWhere('a.dateavis >= :date')
                    ->setParameter('date', $date);
                break;
            case 'month':
                $date = (clone $now)->modify('-1 month');
                $qb->andWhere('a.dateavis >= :date')
                    ->setParameter('date', $date);
                break;
            case 'year':
                $date = (clone $now)->modify('-1 year');
                $qb->andWhere('a.dateavis >= :date')
                    ->setParameter('date', $date);
                break;
            default:
                throw new \InvalidArgumentException("Invalid period value: '$period'. Must be 'today', 'week', 'month', or 'year'.");
        }

        return $qb->orderBy('a.dateavis', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get average rating for an establishment
     * 
     * @param int $etabId
     * @return float
     */
    public function getAverageRatingForEtablissement(int $etabId): float
    {
        try {
            $qb = $this->createQueryBuilder('a');
            $qb->select('AVG(a.rating) as average')
                ->where('a.etablissement = :etabId')
                ->setParameter('etabId', $this->getEntityManager()->getReference(Etablissement::class, $etabId));

            $result = $qb->getQuery()->getSingleScalarResult();

            return (float) ($result ?? 0);
        } catch (\Exception $e) {
            // Log the error: $this->logger->error('Error calculating average rating: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count number of ratings for an establishment
     * 
     * @param int $etabId
     * @return int
     */
    public function countAvisForEtablissement(int $etabId): int
    {
        try {
            $qb = $this->createQueryBuilder('a');
            $qb->select('COUNT(a.avisid) as count')
                ->where('a.etablissement = :etabId')
                ->setParameter('etabId', $this->getEntityManager()->getReference(Etablissement::class, $etabId));

            $result = $qb->getQuery()->getSingleScalarResult();

            return (int) ($result ?? 0);
        } catch (\Exception $e) {
            // Log the error: $this->logger->error('Error counting avis: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get distribution of ratings for an establishment
     * 
     * @param int $etabId
     * @return array with keys 1-5 and values representing count of each rating
     */
    public function getDistributionForEtablissement(int $etabId): array
    {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        try {
            $qb = $this->createQueryBuilder('a');
            $qb->select('a.rating, COUNT(a.avisid) as count')
                ->where('a.etablissement = :etabId')
                ->groupBy('a.rating')
                ->setParameter('etabId', $this->getEntityManager()->getReference(Etablissement::class, $etabId));

            $results = $qb->getQuery()->getResult();

            foreach ($results as $result) {
                $rating = (int) $result['rating'];
                $count = (int) $result['count'];

                if (isset($distribution[$rating])) {
                    $distribution[$rating] = $count;
                }
            }
        } catch (\Exception $e) {
            // Log the error: $this->logger->error('Error getting rating distribution: ' . $e->getMessage());
        }

        return $distribution;
    }

    /**
     * Get the top rated establishments
     * 
     * @param int $limit
     * @return array
     */
    public function getTopRatedEstablishments(int $limit = 5): array
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException('Limit must be a positive integer.');
        }

        try {
            $qb = $this->createQueryBuilder('a');
            $qb->select('e.etabid, e.etabname, AVG(a.rating) as average_rating, COUNT(a.avisid) as count')
                ->join('a.etablissement', 'e')
                ->groupBy('e.etabid')
                ->having('COUNT(a.avisid) >= 3')
                ->orderBy('average_rating', 'DESC')
                ->addOrderBy('count', 'DESC')
                ->setMaxResults($limit);

            return $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            // Log the error: $this->logger->error('Error getting top rated establishments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent ratings
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentRatings(int $limit = 10): array
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException('Limit must be a positive integer.');
        }

        try {
            return $this->createQueryBuilder('a')
                ->select('a', 'e.etabName')
                ->join('a.etablissement', 'e')
                ->orderBy('a.dateavis', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            // Log the error: $this->logger->error('Error getting recent ratings: ' . $e->getMessage());
            return [];
        }
    }
}
