// routes.js - Saved routes management
// Version: stable3

import { getCurrentRouteId, setCurrentRouteId, updateButtonState } from './map.js';
import { showError } from './utils.js';
import { handleClearRoute, calculateRoute } from './routing.js';

// Load saved routes from server
export function loadSavedRoutes() {
    fetch('/navigation/get-routes')
        .then(response => response.json())
        .then(routes => {
            const dropdown = document.getElementById('savedRoutesComboBox');
            
            // Clear existing options except the first one (the placeholder)
            while (dropdown.options.length > 1) {
                dropdown.remove(1); // This will remove the second option and onward
            }
            
            // Add new routes
            routes.forEach(route => {
                const option = document.createElement('option');
                option.value = route.id;
                option.textContent = route.name;
                dropdown.appendChild(option);
            });
            
            console.log('Saved routes list refreshed with ' + routes.length + ' routes');
        })
        .catch(error => {
            console.error('Error fetching saved routes:', error);
        });
}

// Load a saved route by ID
export function loadSavedRoute(routeId) {
    if (!routeId) return;
    
    const progressIndicator = document.getElementById('progressIndicator');
    const departurePlaceField = document.getElementById('departurePlaceField');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalPlaceField = document.getElementById('arrivalPlaceField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    const transportModeComboBox = document.getElementById('transportModeComboBox');
    const routeNameField = document.getElementById('routeNameField');
    const showRouteButton = document.getElementById('showRouteButton');
    const dateTimeSection = document.getElementById('dateTimeSection');
    
    progressIndicator.style.display = 'block';
    
    fetch(`/navigation/history/${routeId}`)
        .then(response => response.json())
        .then(route => {
            if (route.error) {
                showError('Error', route.error);
                progressIndicator.style.display = 'none';
                return;
            }
            
            // Fill form fields with route data
            departurePlaceField.value = route.departurePlaceName || '';
            departureLatField.value = route.departureLat;
            departureLonField.value = route.departureLon;
            arrivalPlaceField.value = route.arrivalPlaceName || '';
            arrivalLatField.value = route.arrivalLat;
            arrivalLonField.value = route.arrivalLon;
            setCurrentRouteId(routeId);

            // Set transport mode if it exists in the dropdown
            const transportModeExists = Array.from(transportModeComboBox.options)
                .some(option => option.value === route.transportMode);
            
            if (transportModeExists) {
                transportModeComboBox.value = route.transportMode;
            }
            
            routeNameField.value = route.name;
            showRouteButton.innerText = "Update";
            
            // Update date/time section visibility
            dateTimeSection.style.display = 
                (route.transportMode === 'public_transport') ? 'block' : 'none';
            
            // Update button state
            updateButtonState();
            
            // Calculate route
            calculateRoute(false);
            
            progressIndicator.style.display = 'none';
        })
        .catch(error => {
            console.error('Error loading saved route:', error);
            progressIndicator.style.display = 'none';
            showError('Error', 'Could not load saved route: ' + error.message);
        });
}

// Delete a saved route
export function deleteRoute() {
    const savedRoutesComboBox = document.getElementById('savedRoutesComboBox');
    const progressIndicator = document.getElementById('progressIndicator');
    
    const routeId = savedRoutesComboBox.value;
    
    if (!routeId) {
        showError('No Selection', 'Please select a saved route to delete.');
        return;
    }
    
    if (confirm('Are you sure you want to delete this route?')) {
        progressIndicator.style.display = 'block';
        
        fetch(`/navigation/history/delete/${routeId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showError('Delete Error', data.error);
            } else {
                // Remove option from dropdown
                const option = savedRoutesComboBox.querySelector(`option[value="${routeId}"]`);
                if (option) option.remove();
                
                // Clear fields if the deleted route was selected
                if (savedRoutesComboBox.value === '') {
                    handleClearRoute();
                }
                setCurrentRouteId(null);
                
                // Show success message
                alert('Route successfully deleted');
            }
            progressIndicator.style.display = 'none';
        })
        .catch(error => {
            console.error('Error deleting route:', error);
            progressIndicator.style.display = 'none';
            showError('Error', 'Could not delete route: ' + error.message);
        });
    }
}