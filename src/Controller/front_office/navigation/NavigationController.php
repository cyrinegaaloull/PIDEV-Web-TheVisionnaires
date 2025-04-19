<?php
namespace App\Controller\front_office\navigation;

use App\Entity\RouteHistory;
use App\Service\LocationService;
use App\Service\RoutingService;
use App\Service\RouteHistoryService;
use App\Service\TransportModeService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/navigation')]
class NavigationController extends AbstractController
{
    #[Route('', name: 'app_navigation')]
    public function index(
        TransportModeService $transportModeService,
        RouteHistoryService $routeHistoryService
    ): Response {
        $transportModes = $transportModeService->getAllTransportModes();
        $savedRoutes = $routeHistoryService->getAllRoutes();
        
        // false pour user déconnecté true pour connecté
        $simulateUser = true;

        $user = null;
        if ($simulateUser) {
            $user = [
                'username' => 'John Doe',
                'profile_picture' => 'default_profile_pic.jpg',
            ];
        }
        
        return $this->render('front_office/navigation/index.html.twig', [
            'transportModes' => $transportModes,
            'savedRoutes' => $savedRoutes,
            'user' => $user,
        ]);
    }

    #[Route('/search-location', name: 'app_navigation_search_location', methods: ['GET'])]
    public function searchLocation(
        Request $request, 
        LocationService $locationService
    ): JsonResponse {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json([]);
        }
        
        $results = $locationService->searchLocation($query);
        
        return $this->json($results);
    }

    #[Route('/get-current-location', name: 'app_navigation_get_current_location', methods: ['GET'])]
    public function getCurrentLocation(LocationService $locationService): JsonResponse
    {
        $result = $locationService->getCurrentLocation();
        
        return $this->json($result);
    }

    #[Route('/calculate-route', name: 'app_navigation_calculate_route', methods: ['POST'])]
    public function calculateRoute(
        Request $request, 
        RoutingService $routingService,
        RouteHistoryService $routeHistoryService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        $requiredFields = ['departureLat', 'departureLon', 'arrivalLat', 'arrivalLon', 'transportMode'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing required field: $field"], 400);
            }
        }
        
        // Get route estimate
        $routeEstimate = $routingService->getRouteEstimate(
            $data['departureLat'],
            $data['departureLon'],
            $data['arrivalLat'],
            $data['arrivalLon'],
            $data['transportMode']
        );
        
        // Save route to history if requested
        if (isset($data['saveRoute']) && $data['saveRoute']) {
            $route = new RouteHistory();
            
            // Use custom name if provided, otherwise generate
            if (isset($data['routeName']) && !empty($data['routeName'])) {
                $routeName = $data['routeName'];
            } else {
                $routeName = $routeHistoryService->generateRouteName(
                    $data['departurePlaceName'] ?? 'Origin',
                    $data['arrivalPlaceName'] ?? 'Destination'
                );
            }
            
            $route->setName($routeName);
            $route->setDeparturePlaceName($data['departurePlaceName'] ?? 'Origin');
            $route->setDepartureLat($data['departureLat']);
            $route->setDepartureLon($data['departureLon']);
            $route->setArrivalPlaceName($data['arrivalPlaceName'] ?? 'Destination');
            $route->setArrivalLat($data['arrivalLat']);
            $route->setArrivalLon($data['arrivalLon']);
            $route->setTransportMode($data['transportMode']);
            $route->setTimestamp(new DateTime());
            $route->setDescription(
                "Route from " . ($data['departurePlaceName'] ?? 'Origin') . 
                " to " . ($data['arrivalPlaceName'] ?? 'Destination') . 
                " via " . $data['transportMode']
            );
            
            $routeHistoryService->saveRoute($route);
            $routeEstimate['savedRouteId'] = $route->getId();
        }
        
        return $this->json($routeEstimate);
    }

    #[Route('/public-transport', name: 'app_navigation_public_transport', methods: ['POST'])]
    public function getPublicTransport(
        Request $request, 
        RoutingService $routingService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        $requiredFields = ['fromLat', 'fromLon', 'toLat', 'toLon'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing required field: $field"], 400);
            }
        }
        
        $departureTime = $data['departureTime'] ?? 'now';
        
        $transportOptions = $routingService->getPublicTransportOptions(
            $data['fromLat'],
            $data['fromLon'],
            $data['toLat'],
            $data['toLon'],
            $departureTime
        );
        
        return $this->json($transportOptions);
    }
    
    #[Route('/history', name: 'app_navigation_history')]
    public function history(RouteHistoryService $routeHistoryService): Response
    {
        $routes = $routeHistoryService->getAllRoutes();
        
        // false pour user déconnecté true pour connecté
        $simulateUser = true;

        $user = null;
        if ($simulateUser) {
            $user = [
                'username' => 'John Doe',
                'profile_picture' => 'default_profile_pic.jpg',
            ];
        }
        
        return $this->render('front_office/navigation/history.html.twig', [
            'routes' => $routes,
            'user' => $user,
        ]);
    }
    
    #[Route('/history/{id}', name: 'app_navigation_history_get', methods: ['GET'])]
    public function getRouteHistory(int $id, RouteHistoryService $routeHistoryService): JsonResponse
    {
        $route = $routeHistoryService->getRoute($id);
        
        if (!$route) {
            return $this->json(['error' => 'Route not found'], 404);
        }
        
        return $this->json($route);
    }
    
    #[Route('/history/delete/{id}', name: 'app_navigation_history_delete', methods: ['DELETE'])]
    public function deleteRouteHistory(int $id, RouteHistoryService $routeHistoryService): JsonResponse
    {
        $success = $routeHistoryService->deleteRoute($id);
        
        if (!$success) {
            return $this->json(['error' => 'Route not found'], 404);
        }
        
        return $this->json(['success' => true]);
    }
}