// mapControls.js - Custom map controls and interactions
// Version: stable3

import { updateButtonState } from './map.js';

// Setup map click positioning functionality
export function setupMapClickPositioning(map) {
    // Variables to track click state
    let isFirstClick = true;
    let mapClicksEnabled = false;
    
    // Create a toggle button for map clicks mode
    const mapClickModeButton = document.createElement('button');
    mapClickModeButton.type = 'button';
    mapClickModeButton.className = 'map-control-button';
    mapClickModeButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Set Locations by Map Click';
    mapClickModeButton.title = 'Click on map to set departure and arrival points';
    
    // Style the button
    mapClickModeButton.style.position = 'absolute';
    mapClickModeButton.style.top = '10px';
    mapClickModeButton.style.left = '250px';
    mapClickModeButton.style.zIndex = '1000';
    mapClickModeButton.style.backgroundColor = 'white';
    mapClickModeButton.style.border = '2px solid rgba(0,0,0,0.2)';
    mapClickModeButton.style.borderRadius = '4px';
    mapClickModeButton.style.padding = '6px 10px';
    mapClickModeButton.style.cursor = 'pointer';
    mapClickModeButton.style.fontSize = '14px';
    mapClickModeButton.style.boxShadow = '0 1px 5px rgba(0,0,0,0.4)';
    
    // Add hover effect
    mapClickModeButton.onmouseover = function() {
        this.style.backgroundColor = '#f4f4f4';
    };
    mapClickModeButton.onmouseout = function() {
        if (!mapClicksEnabled) {
            this.style.backgroundColor = 'white';
        }
    };
    
    // Add the button directly to the map container
    const mapContainer = document.getElementById('navigationMap');
    mapContainer.appendChild(mapClickModeButton);
    
    // Toggle map click mode
    mapClickModeButton.addEventListener('click', function() {
        mapClicksEnabled = !mapClicksEnabled;
        
        if (mapClicksEnabled) {
            mapClickModeButton.style.backgroundColor = '#e0f7fa';
            mapClickModeButton.style.borderColor = '#0288d1';
            mapClickModeButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Map Click Mode: ON';
            isFirstClick = true;
            
            // Show instruction toast
            showToast('Click on the map to set departure point');
        } else {
            mapClickModeButton.style.backgroundColor = 'white';
            mapClickModeButton.style.borderColor = 'rgba(0,0,0,0.2)';
            mapClickModeButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Set Locations by Map Click';
        }
    });
    
    // Add click listener to the map
    map.addListener('click', function(event) {
        if (!mapClicksEnabled) return;
        
        // Get precise numeric values for coordinates
        const lat = parseFloat(event.latLng.lat().toFixed(6));
        const lng = parseFloat(event.latLng.lng().toFixed(6));
        
        // Reverse geocode to get address
        const geocoder = new google.maps.Geocoder();
        const latlng = {lat: lat, lng: lng};
        
        geocoder.geocode({'location': latlng}, function(results, status) {
            let locationName = 'Selected Location';
            
            if (status === 'OK' && results[0]) {
                locationName = results[0].formatted_address;
            }
            
            const departureLatField = document.getElementById('departureLatField');
            const departureLonField = document.getElementById('departureLonField');
            const departurePlaceField = document.getElementById('departurePlaceField');
            const arrivalLatField = document.getElementById('arrivalLatField');
            const arrivalLonField = document.getElementById('arrivalLonField');
            const arrivalPlaceField = document.getElementById('arrivalPlaceField');
            
            if (isFirstClick) {
                // Set departure location with numeric values
                departureLatField.value = lat;
                departureLonField.value = lng;
                departurePlaceField.value = locationName;
                
                // Add marker for departure
                clearMarkers();
                const marker = new google.maps.Marker({
                    position: {lat: lat, lng: lng},
                    map: map,
                    title: 'Departure',
                    animation: google.maps.Animation.DROP,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    }
                });
                markers.push(marker);
                
                isFirstClick = false;
                showToast('Departure point set. Now click for arrival point.');
            } else {
                // Set arrival location with numeric values
                arrivalLatField.value = lat;
                arrivalLonField.value = lng;
                arrivalPlaceField.value = locationName;
                
                // Add marker for arrival
                const marker = new google.maps.Marker({
                    position: {lat: lat, lng: lng},
                    map: map,
                    title: 'Arrival',
                    animation: google.maps.Animation.DROP,
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    }
                });
                markers.push(marker);
                
                isFirstClick = true;
                showToast('Arrival point set. You can now show route or update departure.');
            }
            
            // Update button state
            updateButtonState();
        });
    });
    
    // Helper function to show toast notifications
    function showToast(message) {
        // Check if toast container exists, if not create it
        let toastContainer = document.getElementById('map-toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'map-toast-container';
            toastContainer.style.position = 'absolute';
            toastContainer.style.top = '60px';  // Position below the button
            toastContainer.style.left = '50%';
            toastContainer.style.transform = 'translateX(-50%)';
            toastContainer.style.zIndex = '1000';
            document.getElementById('navigationMap').appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'map-toast';
        toast.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
        toast.style.color = 'white';
        toast.style.padding = '10px 15px';
        toast.style.borderRadius = '4px';
        toast.style.marginBottom = '10px';
        toast.style.boxShadow = '0 2px 5px rgba(0,0,0,0.3)';
        toast.textContent = message;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (toastContainer.contains(toast)) {
                    toastContainer.removeChild(toast);
                }
            }, 500);
        }, 3000);
    }
}

// Import markers array and clearMarkers function
import { markers } from './map.js';
import { clearMarkers } from './routing.js';