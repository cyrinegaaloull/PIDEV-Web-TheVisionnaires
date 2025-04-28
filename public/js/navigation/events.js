// events.js - Event listeners and handlers
// Version: stable3

import { calculateRoute, handleClearRoute, findPublicTransport } from './routing.js';
import { updateButtonState, directionsRenderer } from './map.js';
import { getCurrentLocation, swapLocations, searchLocation } from './locations.js';
import { loadSavedRoute, deleteRoute } from './routes.js';

// Initialize all event listeners
export function setupEventListeners() {
    // DOM elements
    const showRouteButton = document.getElementById('showRouteButton');
    const clearRouteButton = document.getElementById('clearRouteButton');
    const currentLocationButton = document.getElementById('currentLocationButton');
    const swapLocationsButton = document.getElementById('swapLocationsButton');
    const transportModeComboBox = document.getElementById('transportModeComboBox');
    const savedRoutesComboBox = document.getElementById('savedRoutesComboBox');
    const deleteRouteButton = document.getElementById('deleteRouteButton');
    const departurePlaceField = document.getElementById('departurePlaceField');
    const arrivalPlaceField = document.getElementById('arrivalPlaceField');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    const publicTransportComboBox = document.getElementById('publicTransportComboBox');
    const dateTimeSection = document.getElementById('dateTimeSection');
    
    // Show route button
    if (showRouteButton) {
        showRouteButton.addEventListener('click', calculateRoute);
    }
    
    // Clear route button
    if (clearRouteButton) {
        clearRouteButton.addEventListener('click', handleClearRoute);
    }
    
    // Current location button
    if (currentLocationButton) {
        currentLocationButton.addEventListener('click', getCurrentLocation);
    }
    
    // Swap locations button
    if (swapLocationsButton) {
        swapLocationsButton.addEventListener('click', swapLocations);
    }
    
    // Transport mode change
    if (transportModeComboBox) {
        transportModeComboBox.addEventListener('change', function() {
            const isPublicTransport = this.value === 'public_transport';
            if (dateTimeSection) {
                dateTimeSection.style.display = isPublicTransport ? 'block' : 'none';
            }
            
            if (isPublicTransport && departureLatField && departureLatField.value && 
                departureLonField && departureLonField.value && 
                arrivalLatField && arrivalLatField.value && 
                arrivalLonField && arrivalLonField.value) {
                findPublicTransport();
            }
        });
    }
    
    // Public transport option change
    if (publicTransportComboBox) {
        publicTransportComboBox.addEventListener('change', function() {
            console.log('Public transport option changed to index:', this.selectedIndex);
            handlePublicTransportChange();
        });
    }
    
    // Saved routes dropdown change
    if (savedRoutesComboBox) {
        savedRoutesComboBox.addEventListener('change', function() {
            const routeId = this.value;
            if (routeId) {
                loadSavedRoute(routeId);
            }
        });
    }
    
    // Delete route button
    if (deleteRouteButton) {
        deleteRouteButton.addEventListener('click', deleteRoute);
    }
    
    // Location search debounce
    let departureSearchTimeout = null;
    let arrivalSearchTimeout = null;
    
    // Departure place input
    if (departurePlaceField) {
        departurePlaceField.addEventListener('input', function() {
            clearTimeout(departureSearchTimeout);
            departureSearchTimeout = setTimeout(() => {
                searchLocation(this.value, true);
            }, 500);
        });
        
        departurePlaceField.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                searchLocation(this.value, true);
            }
        });
        
        // Hide suggestions when clicking outside
        const departureSuggestions = document.getElementById('departureSuggestions');
        if (departureSuggestions) {
            document.addEventListener('click', function(event) {
                if (!departurePlaceField.contains(event.target) && 
                    !departureSuggestions.contains(event.target)) {
                    departureSuggestions.style.display = 'none';
                }
            });
        }
    }
    
    // Arrival place input
    if (arrivalPlaceField) {
        arrivalPlaceField.addEventListener('input', function() {
            clearTimeout(arrivalSearchTimeout);
            arrivalSearchTimeout = setTimeout(() => {
                searchLocation(this.value, false);
            }, 500);
        });
        
        arrivalPlaceField.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                searchLocation(this.value, false);
            }
        });
        
        // Hide suggestions when clicking outside
        const arrivalSuggestions = document.getElementById('arrivalSuggestions');
        if (arrivalSuggestions) {
            document.addEventListener('click', function(event) {
                if (!arrivalPlaceField.contains(event.target) && 
                    !arrivalSuggestions.contains(event.target)) {
                    arrivalSuggestions.style.display = 'none';
                }
            });
        }
    }
    
    // Listen for coordinate field changes to update button state
    [departureLatField, departureLonField, arrivalLatField, arrivalLonField].forEach(field => {
        if (field) {
            field.addEventListener('change', updateButtonState);
        }
    });
}

// Handle public transport option change
function handlePublicTransportChange() {
    const publicTransportComboBox = document.getElementById('publicTransportComboBox');
    const arrivalTimeLabel = document.getElementById('arrivalTimeLabel');
    
    // Exit if no selection
    if (!publicTransportComboBox || publicTransportComboBox.selectedIndex < 0) return;
    
    const selectedOption = publicTransportComboBox.options[publicTransportComboBox.selectedIndex];
    if (!selectedOption || !selectedOption.dataset) return;
    
    console.log("Public transport option changed:", selectedOption.value);
    console.log("Selected route index:", selectedOption.dataset.index);
    
    if (window.transitResponse && selectedOption.dataset.index !== undefined) {
        // Get the route index
        const routeIndex = parseInt(selectedOption.dataset.index);
        console.log("Setting route index to:", routeIndex);
        
        try {
            // Force refresh of the directions renderer
            if (directionsRenderer) {
                // First clear the current directions
                directionsRenderer.set('directions', null);
                
                // Then set the new directions and route index
                directionsRenderer.setDirections(window.transitResponse);
                directionsRenderer.setRouteIndex(routeIndex);
                
                console.log("Route updated on map");
            } else {
                console.error("directionsRenderer is not available");
            }
        } catch (error) {
            console.error("Error updating route:", error);
        }
        
        // Update arrival time label if data is available
        try {
            const route = window.transitResponse.routes[routeIndex];
            if (route && route.legs && route.legs[0]) {
                const leg = route.legs[0];
                const depTime = leg.departure_time ? leg.departure_time.text : 'N/A';
                const arrTime = leg.arrival_time ? leg.arrival_time.text : 'N/A';
                const dur = leg.duration ? leg.duration.text : 'Unknown';
                
                // Get transport type and icon from the option
                const transportType = selectedOption.dataset.transportType || 'Public Transport';
                const transportIcon = selectedOption.dataset.transportIcon || '🚍';
                
                arrivalTimeLabel.textContent = `${transportIcon} ${transportType} | Departure: ${depTime} | Arrival: ${arrTime} | Duration: ${dur}`;
                console.log("Updated arrival time label");
            }
        } catch (error) {
            console.error("Error updating arrival time label:", error);
        }
    } else if (selectedOption.dataset.depTime) {
        // Fallback to saved dataset values
        const transportType = selectedOption.dataset.transportType || 'Bus';
        const transportIcon = selectedOption.dataset.transportIcon || '🚍';
        arrivalTimeLabel.textContent = `${transportIcon} ${transportType} | Departure: ${selectedOption.dataset.depTime} | Arrival: ${selectedOption.dataset.arrTime} | Duration: ${selectedOption.dataset.duration}`;
    } else {
        console.warn("No transit response or route index available");
    }
}