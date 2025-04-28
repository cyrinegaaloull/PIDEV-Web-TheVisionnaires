// locations.js - Location search and management
// Version: stable3

import { updateButtonState } from './map.js';
import { showError } from './utils.js';

// Search for places using Nominatim API
export function searchLocation(query, isForDeparture) {
    const departureSuggestions = document.getElementById('departureSuggestions');
    const arrivalSuggestions = document.getElementById('arrivalSuggestions');
    const progressIndicator = document.getElementById('progressIndicator');
    
    if (!query || query.trim().length === 0) {
        if (isForDeparture) {
            departureSuggestions.style.display = 'none';
        } else {
            arrivalSuggestions.style.display = 'none';
        }
        return;
    }

    progressIndicator.style.display = 'block';

    // Call Nominatim Search API
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`)
        .then(response => response.json())
        .then(data => {
            const suggestions = isForDeparture ? departureSuggestions : arrivalSuggestions;
            suggestions.innerHTML = '';

            if (!data || data.length === 0) {
                suggestions.style.display = 'none';
                progressIndicator.style.display = 'none';
                return;
            }

            data.forEach(place => {
                const item = document.createElement('div');
                item.classList.add('suggestion-item');
                item.textContent = place.display_name;
                item.addEventListener('click', () => {
                    if (isForDeparture) {
                        const departurePlaceField = document.getElementById('departurePlaceField');
                        const departureLatField = document.getElementById('departureLatField');
                        const departureLonField = document.getElementById('departureLonField');
                        
                        departurePlaceField.value = place.display_name;
                        departureLatField.value = place.lat;
                        departureLonField.value = place.lon;
                        departureSuggestions.style.display = 'none';
                    } else {
                        const arrivalPlaceField = document.getElementById('arrivalPlaceField');
                        const arrivalLatField = document.getElementById('arrivalLatField');
                        const arrivalLonField = document.getElementById('arrivalLonField');
                        
                        arrivalPlaceField.value = place.display_name;
                        arrivalLatField.value = place.lat;
                        arrivalLonField.value = place.lon;
                        arrivalSuggestions.style.display = 'none';
                    }

                    updateButtonState();
                });

                suggestions.appendChild(item);
            });

            suggestions.style.display = 'block';
            progressIndicator.style.display = 'none';
        })
        .catch(error => {
            console.error('Error fetching from Nominatim:', error);
            progressIndicator.style.display = 'none';
        });
}

// Get current location
export function getCurrentLocation() {
    // Import the mapInitialized state from map.js
    const mapInitialized = window.mapInitialized || (typeof google !== 'undefined' && typeof google.maps !== 'undefined');
    const progressIndicator = document.getElementById('progressIndicator');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const departurePlaceField = document.getElementById('departurePlaceField');
    
    if (!mapInitialized) {
        showError('Map not ready', 'Please wait for the map to finish loading.');
        return;
    }
    
    progressIndicator.style.display = 'block';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            // Success callback
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Reverse geocode to get address
                const geocoder = new google.maps.Geocoder();
                const latlng = {lat: lat, lng: lon};
                
                geocoder.geocode({'location': latlng}, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        departureLatField.value = lat;
                        departureLonField.value = lon;
                        departurePlaceField.value = results[0].formatted_address;
                    } else {
                        departureLatField.value = lat;
                        departureLonField.value = lon;
                        departurePlaceField.value = 'Current Location';
                    }
                    updateButtonState();
                    progressIndicator.style.display = 'none';
                });
            },
            // Error callback
            function(error) {
                console.error('Geolocation error:', error);
                progressIndicator.style.display = 'none';
                showError('Location Error', 
                    'Could not determine your location: ' + error.message);
                
                // Fallback to IP-based location
                fetch('/navigation/get-current-location')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            departureLatField.value = data.lat;
                            departureLonField.value = data.lon;
                            
                            let displayName = '';
                            if (data.city) {
                                displayName += data.city;
                                if (data.regionName) displayName += ', ' + data.regionName;
                                if (data.country) displayName += ', ' + data.country;
                            }
                            
                            departurePlaceField.value = displayName || 'Current Location';
                            updateButtonState();
                        }
                    })
                    .catch(e => console.error('Fallback location error:', e));
            }
        );
    } else {
        // Browser doesn't support Geolocation, fall back to IP-based
        fetch('/navigation/get-current-location')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    departureLatField.value = data.lat;
                    departureLonField.value = data.lon;
                    
                    let displayName = '';
                    if (data.city) {
                        displayName += data.city;
                        if (data.regionName) displayName += ', ' + data.regionName;
                        if (data.country) displayName += ', ' + data.country;
                    }
                    
                    departurePlaceField.value = displayName || 'Current Location';
                    updateButtonState();
                } else {
                    showError('Location Error', 
                        'Could not determine your location: ' + (data.message || 'Unknown error'));
                }
                progressIndicator.style.display = 'none';
            })
            .catch(error => {
                console.error('Error getting current location:', error);
                progressIndicator.style.display = 'none';
                showError('Connection Error', 
                    'Could not connect to location service: ' + error.message);
            });
    }
}

// Swap departure and arrival locations
export function swapLocations() {
    const departurePlaceField = document.getElementById('departurePlaceField');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalPlaceField = document.getElementById('arrivalPlaceField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    
    // Save departure values
    const depPlace = departurePlaceField.value;
    const depLat = departureLatField.value;
    const depLon = departureLonField.value;
    
    // Set departure fields to arrival values
    departurePlaceField.value = arrivalPlaceField.value;
    departureLatField.value = arrivalLatField.value;
    departureLonField.value = arrivalLonField.value;
    
    // Set arrival fields to saved departure values
    arrivalPlaceField.value = depPlace;
    arrivalLatField.value = depLat;
    arrivalLonField.value = depLon;
    
    // Update button state
    updateButtonState();
}