// map.js - Map initialization and management
// Version: stable3

import { setupEventListeners } from './events.js';
import { showError } from './utils.js';
import { setupMapClickPositioning } from './mapControls.js';
import { clearRoute, showRoute, showSimpleRoute } from './routing.js';

// Global variables that need to be accessible across modules
export let map;
export let markers = [];
export let routeLines = [];
export let mapInitialized = false;
export let directionsService;
export let directionsRenderer;

// Create a mutable currentRouteId with getter/setter
let _currentRouteId = null;
export function getCurrentRouteId() {
    return _currentRouteId;
}
export function setCurrentRouteId(id) {
    _currentRouteId = id;
}

// Initialize Google Maps
export function initMap() {
    // Check if Google Maps API is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API not loaded! Retrying in 500ms...');
        setTimeout(initMap, 500);
        return;
    }

    // Check if map container exists
    const mapContainer = document.getElementById('navigationMap');
    if (!mapContainer) {
        console.error('Map container not found! Retrying in 500ms...');
        setTimeout(initMap, 500);
        return;
    }

    console.log('Initializing Google Maps...');

    // Initialize the map with Tunis coordinates
    map = new google.maps.Map(mapContainer, {
        center: { lat: 36.8065, lng: 10.1815 },
        zoom: 6,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        fullscreenControl: true,
        streetViewControl: false,
        mapTypeControl: true,
        zoomControl: true
    });
    
    // Initialize directions service and renderer
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true, // We'll handle markers manually
        preserveViewport: false
    });
    directionsRenderer.setMap(map);
    
    // Make directionsRenderer globally available
    window.directionsRenderer = directionsRenderer;
    
    // Add place autocomplete for input fields
    setupAutocomplete();
    
    // Setup map click functionality
    setupMapClickPositioning(map);
    
    // Set today's date as default
    const departureDatePicker = document.getElementById('departureDatePicker');
    if (departureDatePicker) {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        departureDatePicker.value = formattedDate;
    }
    
    // Initialize event listeners
    setupEventListeners();
    
    // Mark as initialized
    mapInitialized = true;
    // Make it globally accessible
    window.mapInitialized = true;
}

// Setup Google Places Autocomplete for location inputs
function setupAutocomplete() {
    const departurePlaceField = document.getElementById('departurePlaceField');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalPlaceField = document.getElementById('arrivalPlaceField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    const departureSuggestions = document.getElementById('departureSuggestions');
    const arrivalSuggestions = document.getElementById('arrivalSuggestions');
    
    if (departurePlaceField) {
        const autocomplete1 = new google.maps.places.Autocomplete(departurePlaceField);
        autocomplete1.addListener('place_changed', function() {
            const place = autocomplete1.getPlace();
            if (place.geometry) {
                departureLatField.value = place.geometry.location.lat();
                departureLonField.value = place.geometry.location.lng();
                departureSuggestions.style.display = 'none';
                updateButtonState();
            }
        });
    }
    
    if (arrivalPlaceField) {
        const autocomplete2 = new google.maps.places.Autocomplete(arrivalPlaceField);
        autocomplete2.addListener('place_changed', function() {
            const place = autocomplete2.getPlace();
            if (place.geometry) {
                arrivalLatField.value = place.geometry.location.lat();
                arrivalLonField.value = place.geometry.location.lng();
                arrivalSuggestions.style.display = 'none';
                updateButtonState();
            }
        });
    }
}

// Update button state based on form fields
export function updateButtonState() {
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    const showRouteButton = document.getElementById('showRouteButton');
    
    if (!departureLatField || !departureLonField || !arrivalLatField || !arrivalLonField || !showRouteButton) {
        return;
    }
    
    const hasDepCoords = departureLatField.value && departureLonField.value;
    const hasArrCoords = arrivalLatField.value && arrivalLonField.value;
    
    showRouteButton.disabled = !(hasDepCoords && hasArrCoords);
}