// main.js - Main module that orchestrates the navigation functionality
// Version: stable3

import { initMap } from './map.js';
import { loadSavedRoutes, loadSavedRoute, deleteRoute } from './routes.js';

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Check for URL parameter to load a route
    const urlParams = new URLSearchParams(window.location.search);
    const routeId = urlParams.get('route');
    
    if (routeId) {
        const savedRoutesComboBox = document.getElementById('savedRoutesComboBox');
        if (savedRoutesComboBox) {
            savedRoutesComboBox.value = routeId;
            loadSavedRoute(routeId);
        }
    }
});