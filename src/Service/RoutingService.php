<?php
// src/Service/RoutingService.php
namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RoutingService
{
    private $httpClient;
    private $cache;
    private $googleApiKey;
    
    public function __construct(HttpClientInterface $httpClient, string $googleApiKey = 'googleKey')
    {
        $this->httpClient = $httpClient;
        $this->cache = new FilesystemAdapter();
        $this->googleApiKey = $googleApiKey;
    }

    public function getRouteEstimate(
        float $depLat, 
        float $depLon, 
        float $arrLat, 
        float $arrLon, 
        string $transportMode
    ) {
        // Create a cache key
        $cacheKey = sprintf('route_%f_%f_%f_%f_%s', 
            $depLat, $depLon, $arrLat, $arrLon, $transportMode);
            
        // Get from cache or calculate
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($depLat, $depLon, $arrLat, $arrLon, $transportMode) {
            // Set cache for 1 hour
            $item->expiresAfter(3600);
            
            // Try to get from Google API
            try {
                return $this->getGoogleDirectionsEstimate($depLat, $depLon, $arrLat, $arrLon, $transportMode);
            } catch (\Exception $e) {
                // Fallback to calculation
                return $this->calculateRouteEstimate($depLat, $depLon, $arrLat, $arrLon, $transportMode);
            }
        });
    }

    private function getGoogleDirectionsEstimate(
        float $depLat, 
        float $depLon, 
        float $arrLat, 
        float $arrLon, 
        string $transportMode
    ) {
        // Convert to Google's mode format
        $googleMode = match(strtolower($transportMode)) {
            'cycling' => 'bicycling',
            'driving', 'walking' => strtolower($transportMode),
            default => 'driving'
        };
        
        $url = 'https://maps.googleapis.com/maps/api/directions/json';
        
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'origin' => "$depLat,$depLon",
                'destination' => "$arrLat,$arrLon",
                'mode' => $googleMode,
                'key' => $this->googleApiKey
            ]
        ]);
        
        $data = $response->toArray();
        
        if ($data['status'] !== 'OK') {
            throw new \Exception("Google Directions API error: " . $data['status']);
        }
        
        $route = $data['routes'][0];
        $leg = $route['legs'][0];
        
        $distanceMeters = $leg['distance']['value'];
        $durationSeconds = $leg['duration']['value'];
        $formattedDistance = $leg['distance']['text'];
        $formattedDuration = $leg['duration']['text'];
        $polyline = $route['overview_polyline']['points'] ?? '';
        
        return [
            'distanceKm' => $distanceMeters / 1000,
            'durationMinutes' => $durationSeconds / 60,
            'formattedDistance' => $formattedDistance,
            'formattedDuration' => $formattedDuration,
            'polyline' => $polyline
        ];
    }

    private function calculateRouteEstimate(
        float $depLat, 
        float $depLon, 
        float $arrLat, 
        float $arrLon, 
        string $transportMode
    ) {
        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance($depLat, $depLon, $arrLat, $arrLon);
        
        // Calculate travel time based on transport mode
        $travelTimeMinutes = $this->calculateTravelTime($distance, $transportMode);
        
        return [
            'distanceKm' => $distance,
            'durationMinutes' => $travelTimeMinutes,
            'formattedDistance' => sprintf('%.1f km', $distance),
            'formattedDuration' => $this->formatDuration($travelTimeMinutes),
            'polyline' => ''
        ];
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadiusKm * $c;
    }

    private function calculateTravelTime(float $distanceKm, string $transportMode): float
    {
        // Average speeds in km/h for different transport modes
        $speed = match(strtolower($transportMode)) {
            'walking' => 5.0,
            'cycling' => 15.0,
            default => 50.0 // driving
        };
        
        // Calculate travel time in minutes
        return ($distanceKm / $speed) * 60;
    }

    private function formatDuration(float $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = floor($minutes % 60);
        
        if ($hours > 0) {
            return sprintf('%d hour%s %d min%s', 
                $hours, 
                $hours != 1 ? 's' : '', 
                $mins, 
                $mins != 1 ? 's' : ''
            );
        }
        
        return sprintf('%d min%s', $mins, $mins != 1 ? 's' : '');
    }
    
    public function getPublicTransportOptions(
        float $fromLat, 
        float $fromLon, 
        float $toLat, 
        float $toLon, 
        string $departureTime = 'now'
    ) {
        // Format departure time for Google API
        $formattedDepartureTime = ($departureTime === 'now')
            ? 'now'
            : strtotime($departureTime);
            
        $url = 'https://maps.googleapis.com/maps/api/directions/json';
        
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'origin' => "$fromLat,$fromLon",
                'destination' => "$toLat,$toLon",
                'mode' => 'transit',
                'departure_time' => $formattedDepartureTime,
                'alternatives' => 'true',
                'key' => $this->googleApiKey
            ]
        ]);
        
        return $response->toArray();
    }
}