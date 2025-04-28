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
    /**
     * Récupère les événements les plus réservés
     *
     * @param int $limit Nombre d'événements à récupérer
     * @return array
     */
    public function findMostReservedEvents(int $limit = 5): array
{
    return $this->createQueryBuilder('e')
        ->select('e', 'l', 'COUNT(r.reservationId) as reservationCount')
        ->leftJoin('e.lieu', 'l')
        ->leftJoin('App\Entity\ReservationEvent', 'r', 'WITH', 'r.event = e')
        ->where('l.lieuid IS NOT NULL') // 🔥 🔥 🔥 ajoute cette ligne
        ->groupBy('e.eventid')
        ->orderBy('reservationCount', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

    /**
     * Compte le nombre d'événements par catégorie
     *
     * @return array
     */
    public function countEventsByCategory(): array
    {
        $result = $this->createQueryBuilder('e')
            ->select('e.eventcategory as category, COUNT(e.eventid) as count')
            ->groupBy('e.eventcategory')
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
     * Calcule le taux d'occupation pour chaque événement
     *
     * @return array
     */
    public function calculateOccupancyRate(): array
    {
        $events = $this->createQueryBuilder('e')
            ->select('e.eventid, e.eventname, e.maxtickets, e.reservedtickets')
            ->where('e.maxtickets > 0')
            ->getQuery()
            ->getResult();
        
        $result = [];
        foreach ($events as $event) {
            $occupancyRate = ($event['reservedtickets'] / $event['maxtickets']) * 100;
            $result[] = [
                'id' => $event['eventid'],
                'name' => $event['eventname'],
                'occupancyRate' => round($occupancyRate, 2),
                'reserved' => $event['reservedtickets'],
                'max' => $event['maxtickets']
            ];
        }
        
        // Trier par taux d'occupation décroissant
        usort($result, function($a, $b) {
            return $b['occupancyRate'] <=> $a['occupancyRate'];
        });
        
        return $result;
    }
    
    /**
     * Récupère les événements à venir
     *
     * @param int $limit Nombre d'événements à récupérer
     * @return array
     */
    public function findUpcomingEvents(int $limit = 5): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('e')
            ->where('e.eventdate >= :now')
            ->setParameter('now', $now)
            ->orderBy('e.eventdate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
