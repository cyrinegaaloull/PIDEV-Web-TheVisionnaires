<?php

namespace App\Repository;

use App\Entity\ReservationEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationEvent::class);
    }

    /**
     * Compte le nombre de réservations par mois pour l'année en cours
     *
     * @return array
     */
    public function countReservationsByMonth(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $currentYear = date('Y');
        
        $sql = "
            SELECT 
                MONTH(reservation_date) as month,
                COUNT(reservation_id) as count
            FROM 
                reservation_event
            WHERE 
                YEAR(reservation_date) = :year
            GROUP BY 
                MONTH(reservation_date)
            ORDER BY 
                month ASC
        ";
        
        $result = $conn->executeQuery($sql, ['year' => $currentYear])->fetchAllAssociative();
        
        // Formater les données pour un graphique
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        $data = array_fill(1, 12, 0); // Initialiser avec 0 pour tous les mois
        
        foreach ($result as $row) {
            $data[$row['month']] = (int)$row['count'];
        }
        
        return [
            'labels' => array_values($months),
            'data' => array_values($data)
        ];
    }
    
    /**
     * Compte le nombre de réservations par jour pour le mois en cours
     *
     * @return array
     */
    public function countReservationsByDay(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        $sql = "
            SELECT 
                DAY(reservation_date) as day,
                COUNT(reservation_id) as count
            FROM 
                reservation_event
            WHERE 
                YEAR(reservation_date) = :year
                AND MONTH(reservation_date) = :month
            GROUP BY 
                DAY(reservation_date)
            ORDER BY 
                day ASC
        ";
        
        $result = $conn->executeQuery($sql, [
            'year' => $currentYear,
            'month' => $currentMonth
        ])->fetchAllAssociative();
        
        // Déterminer le nombre de jours dans le mois courant
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        
        // Initialiser les données
        $formattedResult = [
            'labels' => range(1, $daysInMonth),
            'data' => array_fill(0, $daysInMonth, 0)
        ];
        
        // Remplir avec les données réelles
        foreach ($result as $row) {
            $day = (int)$row['day'];
            $formattedResult['data'][$day - 1] = (int)$row['count'];
        }
        
        return $formattedResult;
    }
    
    /**
     * Récupère les informations sur les utilisateurs qui réservent le plus
     *
     * @param int $limit Nombre d'utilisateurs à récupérer
     * @return array
     */
    public function findTopUsers(int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->select('IDENTITY(r.user) as userId, COUNT(r.reservationId) as reservationCount')
            ->groupBy('r.user')
            ->orderBy('reservationCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}