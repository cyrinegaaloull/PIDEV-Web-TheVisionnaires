<?php
// src/Service/RouteHistoryService.php
namespace App\Service;

use App\Entity\RouteHistory;
use App\Repository\RouteHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class RouteHistoryService
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager, RouteHistoryRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getAllRoutes()
    {
        return $this->repository->findAllSortedByTimestamp();
    }
    /**
     * Get a route by its ID
     *
     * @param int $routeId The ID of the route to fetch
     * @return RouteHistory|null The route if found, null otherwise
     */
    public function getRoute(int $routeId): ?RouteHistory
    {
        return $this->repository->find($routeId);
    }

    public function saveRoute(RouteHistory $route)
    {
        $this->entityManager->persist($route);
        $this->entityManager->flush();
        
        return $route;
    }

    public function deleteRoute(int $routeId): bool
    {
        $route = $this->repository->find($routeId);
        
        if (!$route) {
            return false;
        }
        
        $this->entityManager->remove($route);
        $this->entityManager->flush();
        
        return true;
    }

    public function findRouteByCoordinates(
        float $departureLat, 
        float $departureLon,
        float $arrivalLat, 
        float $arrivalLon,
        string $transportMode
    ) {
        return $this->repository->findRouteByCoordinatesAndMode(
            $departureLat, 
            $departureLon, 
            $arrivalLat, 
            $arrivalLon, 
            $transportMode
        );
    }

    public function generateRouteName(string $departurePlace, string $arrivalPlace): string
    {
        $depName = $this->extractShortPlaceName($departurePlace);
        $arrName = $this->extractShortPlaceName($arrivalPlace);
        
        $timeStr = (new DateTime())->format('H:i');
        
        return $depName . ' → ' . $arrName . ' (' . $timeStr . ')';
    }

    private function extractShortPlaceName(string $placeName): string
    {
        if (empty($placeName)) {
            return 'Unknown';
        }
        
        // Split by commas and take the first part (usually the city/location name)
        $parts = explode(',', $placeName);
        
        if (!empty($parts[0])) {
            $shortName = trim($parts[0]);
            
            // If the name is still too long, truncate it
            if (strlen($shortName) > 12) {
                return substr($shortName, 0, 10) . '...';
            }
            
            return $shortName;
        }
        
        // If the full name is too long, truncate it
        if (strlen($placeName) > 12) {
            return substr($placeName, 0, 10) . '...';
        }
        
        return $placeName;
    }
}