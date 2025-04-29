<?php

namespace App\Service;

use Geocoder\ProviderAggregator;
use Geocoder\StatefulGeocoder;

class GeocodingService
{
    private StatefulGeocoder $geocoder;

    public function __construct(ProviderAggregator $providerAggregator)
    {
        $this->geocoder = new StatefulGeocoder($providerAggregator, 'fr');
    }

    public function reverseGeocode(float $lat, float $lon)
    {
        return $this->geocoder->reverse($lat, $lon);
    }
}
