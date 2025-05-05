// routing.js - Route calculation and display
// Version: stable3

import { map, markers, routeLines, directionsService, directionsRenderer, mapInitialized, updateButtonState, getCurrentRouteId, setCurrentRouteId } from './map.js';
import { showError, isValidCoordinates, isValidLatitude, isValidLongitude } from './utils.js';
import { loadSavedRoutes } from './routes.js';

// Function to clear existing route from map
export function clearRoute() {
    // Remove markers
    markers.forEach(marker => marker.setMap(null));
    markers.length = 0; // Clear the array
    
    // Remove route lines
    routeLines.forEach(line => line.setMap(null));
    routeLines.length = 0; // Clear the array
    
    // Clear directions
    if (directionsRenderer) {
        directionsRenderer.setDirections({routes: []});
    }
}

// Function to clear location markers only
export function clearMarkers() {
    markers.forEach(marker => marker.setMap(null));
    markers.length = 0; // Clear the array
}

// Function to show simple route on map (straight line)
export function showSimpleRoute(depLat, depLon, arrLat, arrLon, transportMode) {
    clearRoute();
    
    // Create markers for start and end points
    const startMarker = new google.maps.Marker({
        position: { lat: depLat, lng: depLon },
        map: map,
        title: 'Departure',
        animation: google.maps.Animation.DROP,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
        }
    });
        
    const endMarker = new google.maps.Marker({
        position: { lat: arrLat, lng: arrLon },
        map: map,
        title: 'Arrival',
        animation: google.maps.Animation.DROP,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
        }
    });
        
    markers.push(startMarker, endMarker);
    
    // Choose color based on transport mode
    const colors = {
        'driving': '#4CAF50',
        'walking': '#2196F3',
        'cycling': '#FF9800',
        'public_transport': '#9C27B0'
    };
    
    const color = colors[transportMode] || '#4CAF50';
    
    // Create a line between the points
    const routeLine = new google.maps.Polyline({
        path: [
            { lat: depLat, lng: depLon },
            { lat: arrLat, lng: arrLon }
        ],
        geodesic: true,
        strokeColor: color,
        strokeOpacity: 0.7,
        strokeWeight: 5
    });
    
    routeLine.setMap(map);
    routeLines.push(routeLine);
    
    // Fit the map to show the entire route
    const bounds = new google.maps.LatLngBounds();
    bounds.extend({ lat: depLat, lng: depLon });
    bounds.extend({ lat: arrLat, lng: arrLon });
    map.fitBounds(bounds);
}

// Function to show route with directions
export function showRoute(depLat, depLon, arrLat, arrLon, transportMode) {
    clearRoute();
    
    // Convert transport mode to Google Maps format
    const googleTransportMode = convertTransportMode(transportMode);
    
    // Create request for directions service
    const request = {
        origin: { lat: depLat, lng: depLon },
        destination: { lat: arrLat, lng: arrLon },
        travelMode: googleTransportMode,
        provideRouteAlternatives: false
    };
    
    // Call directions service
    directionsService.route(request, function(result, status) {
        if (status === 'OK') {
            // Add custom markers
            const startMarker = new google.maps.Marker({
                position: { lat: depLat, lng: depLon },
                map: map,
                title: 'Departure',
                animation: google.maps.Animation.DROP,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                }
            });
            
            const endMarker = new google.maps.Marker({
                position: { lat: arrLat, lng: arrLon },
                map: map,
                title: 'Arrival',
                animation: google.maps.Animation.DROP,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                }
            });
            
            markers.push(startMarker, endMarker);
            
            // Display the route
            directionsRenderer.setDirections(result);
        } else {
            console.error('Directions request failed due to ' + status);
            // Fallback to simple route
            showSimpleRoute(depLat, depLon, arrLat, arrLon, transportMode);
        }
    });
}

// Convert app transport mode to Google Maps travel mode
function convertTransportMode(appMode) {
    switch(appMode) {
        case 'driving':
            return google.maps.TravelMode.DRIVING;
        case 'walking':
            return google.maps.TravelMode.WALKING;
        case 'cycling':
            return google.maps.TravelMode.BICYCLING;
        case 'public_transport':
            return google.maps.TravelMode.TRANSIT;
        default:
            return google.maps.TravelMode.DRIVING;
    }
}

// Calculate route based on form inputs
export function calculateRoute(save = true) {
    console.log("Calculating route with save =", save);
    
    try {
        // Check map initialization
        if (!mapInitialized) {
            showError('Map not ready', 'Please wait for the map to finish loading.');
            return;
        }
        
        // Get form elements
        const departureLatField = document.getElementById('departureLatField');
        const departureLonField = document.getElementById('departureLonField');
        const arrivalLatField = document.getElementById('arrivalLatField');
        const arrivalLonField = document.getElementById('arrivalLonField');
        const departurePlaceField = document.getElementById('departurePlaceField');
        const arrivalPlaceField = document.getElementById('arrivalPlaceField');
        const transportModeComboBox = document.getElementById('transportModeComboBox');
        const departureDatePicker = document.getElementById('departureDatePicker');
        const departureTimeComboBox = document.getElementById('departureTimeComboBox');
        const routeNameField = document.getElementById('routeNameField');
        const progressIndicator = document.getElementById('progressIndicator');
        const arrivalTimeLabel = document.getElementById('arrivalTimeLabel');
        
        try {
            // Debug each value before parsing
            console.log("Raw field values:");
            console.log("departureLatField:", departureLatField?.value);
            console.log("departureLonField:", departureLonField?.value);
            console.log("arrivalLatField:", arrivalLatField?.value);
            console.log("arrivalLonField:", arrivalLonField?.value);
            
            // Parse values
            const depLat = parseFloat(departureLatField?.value?.trim());
            const depLon = parseFloat(departureLonField?.value?.trim());
            const arrLat = parseFloat(arrivalLatField?.value?.trim());
            const arrLon = parseFloat(arrivalLonField?.value?.trim());
            
            console.log("Parsed values:", {depLat, depLon, arrLat, arrLon});
    
            // Check for NaN
            if (isNaN(depLat) || isNaN(depLon) || isNaN(arrLat) || isNaN(arrLon)) {
                console.log("NaN values detected:", {depLat, depLon, arrLat, arrLon});
                showError('Invalid Input', 'Please enter valid coordinates.');
                return;
            }
    
            // Validate coordinates
            const isValid = isValidCoordinates(depLat, depLon, arrLat, arrLon);
            console.log("Coordinates valid:", isValid);
            
            if (!isValid) {
                showError('Invalid Coordinates', 
                    'Please ensure coordinates are within valid ranges:\n' +
                    'Latitude: -90 to 90\n' +
                    'Longitude: -180 to 180');
                return;
            }
    
            // Check transport mode
            console.log("Transport mode:", transportModeComboBox?.value);
            const transportMode = transportModeComboBox?.value;
    
            if (transportMode === 'public_transport') {
                console.log("Using public transport mode");
                findPublicTransport(save);
                return;
            }
    
            console.log("Showing progress indicator");
            progressIndicator.style.display = 'block';
    
            console.log("Creating route data");
            // Check if departureTime is defined
            if (typeof departureTime === 'undefined') {
                console.log("departureTime is undefined, creating new Date");
                var departureTime = new Date();
            }
            
            const routeData = {
                departureLat: depLat,
                departureLon: depLon,
                arrivalLat: arrLat,
                arrivalLon: arrLon,
                transportMode: transportMode,
                departurePlaceName: departurePlaceField?.value || "",
                arrivalPlaceName: arrivalPlaceField?.value || "",
                routeName: routeNameField?.value || "",
                saveRoute: save,
                id: getCurrentRouteId(),
                departureTime: departureTime.toISOString()
            };
            
            console.log("Route data created:", routeData);
    
            console.log("About to call showRoute");
            showRoute(depLat, depLon, arrLat, arrLon, transportMode);
            console.log("showRoute completed");
    
            if (save) {               
                console.log("Saving route to server");
                // Only save if save == true
                fetch('/navigation/calculate-route', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(routeData)
                })
                .then(response => {
                    console.log("Got response from server");
                    return response.json();
                })
                .then(data => {
                    console.log("Processed server response:", data);
                    if (data.error) {
                        showError('Route Calculation Error', data.error);
                        progressIndicator.style.display = 'none';
                        return;
                    }
    
                    let departureTime;
                    const dateStr = departureDatePicker?.value?.trim();
                    const timeStr = departureTimeComboBox?.value?.trim();
                    console.log("Date/time strings:", dateStr, timeStr);
    
                    if (dateStr && timeStr) {
                        const departureTimeCandidate = new Date(`${dateStr}T${timeStr}`);
                        if (!isNaN(departureTimeCandidate.getTime())) {
                            departureTime = departureTimeCandidate;
                        } else {
                            departureTime = new Date();
                        }
                    } else {
                        departureTime = new Date();
                    }
                    console.log("Departure time:", departureTime);
    
                    let arrivalTime;
                    const durationInMinutes = Number(data.durationMinutes);
                    console.log("Duration in minutes:", durationInMinutes);
    
                    if (!isNaN(durationInMinutes)) {
                        arrivalTime = new Date(departureTime.getTime() + durationInMinutes * 60000);
                    } else {
                        arrivalTime = departureTime;
                    }
                    console.log("Arrival time:", arrivalTime);
    
                    const formatter = new Intl.DateTimeFormat('en-US', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
    
                    const formattedText = `Distance: ${data.formattedDistance} | Travel time: ${data.formattedDuration} | Arrival: ${formatter.format(arrivalTime)}`;
                    console.log("Setting arrival time label:", formattedText);
                    arrivalTimeLabel.textContent = formattedText;
    
                    if (data.savedRouteId) {
                        console.log("Loading saved routes");
                        loadSavedRoutes();
                    }
    
                    console.log("Hiding progress indicator");
                    progressIndicator.style.display = 'none';
                })
                .catch(error => {
                    console.error('Error calculating route:', error);
                    progressIndicator.style.display = 'none';
                    showError('Routing Error', 'Could not calculate route: ' + error.message);
                });
            } else {
                console.log("Not saving route, hiding progress indicator");
                progressIndicator.style.display = 'none';
            }
    
        } catch (innerError) {
            console.error('Inner try/catch error:', innerError);
            showError('Processing Error', 'Error processing coordinates: ' + (innerError.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Main try/catch error:', error);
        showError('Invalid Input', 'Please enter valid numbers for all coordinates: ' + (error.message || 'Unknown error'));
    }
}

// Clear all location fields and reset form
export function handleClearRoute() {
    clearRoute();
    
    // Get form elements
    const departurePlaceField = document.getElementById('departurePlaceField');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalPlaceField = document.getElementById('arrivalPlaceField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    const transportModeComboBox = document.getElementById('transportModeComboBox');
    const savedRoutesComboBox = document.getElementById('savedRoutesComboBox');
    const publicTransportOptionsPanel = document.getElementById('publicTransportOptionsPanel');
    const arrivalTimeLabel = document.getElementById('arrivalTimeLabel');
    const routeNameField = document.getElementById('routeNameField');
    const showRouteButton = document.getElementById('showRouteButton');
    
    // Clear input fields
    if (departurePlaceField) departurePlaceField.value = '';
    if (departureLatField) departureLatField.value = '';
    if (departureLonField) departureLonField.value = '';
    if (arrivalPlaceField) arrivalPlaceField.value = '';
    if (arrivalLatField) arrivalLatField.value = '';
    if (arrivalLonField) arrivalLonField.value = '';
    setCurrentRouteId(null);
    
    // Reset dropdowns to first option
    if (transportModeComboBox && transportModeComboBox.options.length > 0) {
        transportModeComboBox.selectedIndex = 0;
    }
    
    if (savedRoutesComboBox && savedRoutesComboBox.options.length > 0) {
        savedRoutesComboBox.selectedIndex = 0;
    }
    
    // Hide public transport options
    if (publicTransportOptionsPanel) publicTransportOptionsPanel.style.display = 'none';
    
    // Reset arrival time label
    if (arrivalTimeLabel) arrivalTimeLabel.textContent = 'Route information will appear here';
    
    // Clear route name
    if (routeNameField) routeNameField.value = '';
    if (showRouteButton) showRouteButton.innerText = "Show Route";
    
    // Disable show route button
    updateButtonState();
}

// Function to handle public transport routing
export function findPublicTransport(save = true) {
    try {
        const departureLatField = document.getElementById('departureLatField');
        const departureLonField = document.getElementById('departureLonField');
        const arrivalLatField = document.getElementById('arrivalLatField');
        const arrivalLonField = document.getElementById('arrivalLonField');
        const departurePlaceField = document.getElementById('departurePlaceField');
        const arrivalPlaceField = document.getElementById('arrivalPlaceField');
        const departureDatePicker = document.getElementById('departureDatePicker');
        const departureTimeComboBox = document.getElementById('departureTimeComboBox');
        const routeNameField = document.getElementById('routeNameField');
        const progressIndicator = document.getElementById('progressIndicator');
        const publicTransportOptionsPanel = document.getElementById('publicTransportOptionsPanel');
        const publicTransportComboBox = document.getElementById('publicTransportComboBox');
        const arrivalTimeLabel = document.getElementById('arrivalTimeLabel');
        
        const depLat = parseFloat(departureLatField.value.trim());
        const depLon = parseFloat(departureLonField.value.trim());
        const arrLat = parseFloat(arrivalLatField.value.trim());
        const arrLon = parseFloat(arrivalLonField.value.trim());
        
        if (isNaN(depLat) || isNaN(depLon) || isNaN(arrLat) || isNaN(arrLon)) {
            showError('Invalid Input', 'Please enter valid coordinates.');
            return;
        }
        
        if (!isValidCoordinates(depLat, depLon, arrLat, arrLon)) {
            showError('Invalid Coordinates', 'Please ensure all coordinates are valid.');
            return;
        }
        
        progressIndicator.style.display = 'block';
        publicTransportOptionsPanel.style.display = 'block';
        publicTransportComboBox.innerHTML = '<option value="">Loading options...</option>';
        
        // Format departure time for transit request
        let departureTime = new Date();
        if (departureDatePicker.value && departureTimeComboBox.value) {
            const dateTimeStr = `${departureDatePicker.value}T${departureTimeComboBox.value}:00`;
            const parsedDate = new Date(dateTimeStr);
            if (!isNaN(parsedDate.getTime())) {
                departureTime = parsedDate;
            }
        }
        
        // Create a DirectionsService request for transit
        const request = {
            origin: { lat: depLat, lng: depLon },
            destination: { lat: arrLat, lng: arrLon },
            travelMode: google.maps.TravelMode.TRANSIT,
            transitOptions: {
                departureTime: departureTime
            },
            provideRouteAlternatives: true
        };
        
        // Call the Directions service directly
        directionsService.route(request, function(response, status) {
            if (status === 'OK') {
                publicTransportComboBox.innerHTML = '';
                
                if (response.routes.length === 0) {
                    publicTransportComboBox.innerHTML = '<option value="">No routes found</option>';
                    progressIndicator.style.display = 'none';
                    return;
                }
                
                // Process routes and add them to dropdown
                response.routes.forEach((route, index) => {
                    const leg = route.legs[0];
                    
                    // Extract departure and arrival times
                    const departure = leg.departure_time ? leg.departure_time.text : 'N/A';
                    const arrival = leg.arrival_time ? leg.arrival_time.text : 'N/A';
                    const duration = leg.duration ? leg.duration.text : 'Unknown';
                    
                    // Create option element
                    const option = document.createElement('option');
                    option.value = index;
                    option.dataset.index = index;
                    
                    // Determine the transport type and corresponding icon
                    let transportIcon = '🚍'; // Default bus icon
                    let transportType = 'Bus';
                    
                    if (leg.steps && leg.steps.length > 0) {
                        // Find the first transit step to determine main transport type
                        for (const step of leg.steps) {
                            if (step.travel_mode === 'TRANSIT' && step.transit) {
                                const vehicle = step.transit.line.vehicle;
                                if (vehicle && vehicle.type) {
                                    switch (vehicle.type.toLowerCase()) {
                                        case 'subway':
                                        case 'metro_rail':
                                            transportIcon = '🚇';
                                            transportType = 'Metro';
                                            break;
                                        case 'rail':
                                        case 'heavy_rail':
                                        case 'commuter_train':
                                            transportIcon = '🚆';
                                            transportType = 'Train';
                                            break;
                                        case 'tram':
                                        case 'light_rail':
                                            transportIcon = '🚋';
                                            transportType = 'Tram';
                                            break;
                                        case 'ferry':
                                            transportIcon = '⛴️';
                                            transportType = 'Ferry';
                                            break;
                                        case 'cable_car':
                                        case 'gondola_lift':
                                            transportIcon = '🚠';
                                            transportType = 'Cable Car';
                                            break;
                                        case 'bus':
                                        default:
                                            transportIcon = '🚍';
                                            transportType = 'Bus';
                                            break;
                                    }
                                    break; // Found main transport type, stop looking
                                }
                            }
                        }
                    }
                    
                    // Set the option text with icon and details
                    option.innerHTML = `${transportIcon} <span>${transportType}</span> | Departure: ${departure} | Arrival: ${arrival} | Duration: ${duration}`;
                    
                    // Store transport type in dataset for later use
                    option.dataset.transportType = transportType;
                    option.dataset.transportIcon = transportIcon;
                    
                    publicTransportComboBox.appendChild(option);
                });
                
                // Store the response for later use
                window.transitResponse = response;
                
                // Select first option by default
                if (publicTransportComboBox.options.length > 0) {
                    publicTransportComboBox.selectedIndex = 0;
                    const selected = publicTransportComboBox.options[0];
                    
                    // Display the route - important to follow this order:
                    // 1. First set the directions
                    directionsRenderer.setDirections(response);
                    // 2. Then set the route index
                    directionsRenderer.setRouteIndex(parseInt(selected.dataset.index || '0'));
                    
                    console.log("Set public transport route:", parseInt(selected.dataset.index || '0'));
                    
                    // Create custom markers
                    clearMarkers();
                    const startMarker = new google.maps.Marker({
                        position: { lat: depLat, lng: depLon },
                        map: map,
                        title: 'Departure',
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                        }
                    });
                    
                    const endMarker = new google.maps.Marker({
                        position: { lat: arrLat, lng: arrLon },
                        map: map,
                        title: 'Arrival',
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                        }
                    });
                    
                    markers.push(startMarker, endMarker);
                    
                    // Update arrival time label with transport type
                    const leg = response.routes[0].legs[0];
                    const depTime = leg.departure_time ? leg.departure_time.text : 'N/A';
                    const arrTime = leg.arrival_time ? leg.arrival_time.text : 'N/A';
                    const dur = leg.duration ? leg.duration.text : 'Unknown';
                    
                    const transportType = selected.dataset.transportType || 'Public Transport';
                    const transportIcon = selected.dataset.transportIcon || '🚍';
                    
                    arrivalTimeLabel.textContent = `${transportIcon} ${transportType} | Departure: ${depTime} | Arrival: ${arrTime} | Duration: ${dur}`;
                }
                if (routeNameField.value.trim() !== '' && save) {
                    console.log(departureTime, departureTime.toISOString());
                    
                    const routeData = {
                        departureLat: depLat,
                        departureLon: depLon,
                        arrivalLat: arrLat,
                        arrivalLon: arrLon,
                        transportMode: 'public_transport',
                        departurePlaceName: departurePlaceField.value,
                        arrivalPlaceName: arrivalPlaceField.value,
                        routeName: routeNameField.value,
                        saveRoute: save,
                                                    id: getCurrentRouteId(),
                        departureTime: departureTime.toISOString() 
                    };
                    
                    // Call the API to save the route
                    fetch('/navigation/calculate-route', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(routeData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Route saving error:', data.error);
                            return;
                        }
                        
                        // If the route was saved, refresh the saved routes dropdown
                        if (data.savedRouteId) {
                            console.log('Public transport route saved with ID:', data.savedRouteId);
                            loadSavedRoutes();
                        }
                    })
                    .catch(error => {
                        console.error('Error saving public transport route:', error);
                    });
                }
                progressIndicator.style.display = 'none';
                
            } else {
                console.error('Directions request failed due to ' + status);
                
                // Try the backend API as fallback
                fallbackToBackendPublicTransport(depLat, depLon, arrLat, arrLon, departureTime);
            }
        });
        
    } catch (error) {
        showError('Invalid Input', 'Please enter valid numbers for all coordinates.');
        console.error(error);
        progressIndicator.style.display = 'none';
    }
}

// Fallback to backend API for public transport options with icons
function fallbackToBackendPublicTransport(depLat, depLon, arrLat, arrLon, departureTime) {
    const progressIndicator = document.getElementById('progressIndicator');
    const publicTransportOptionsPanel = document.getElementById('publicTransportOptionsPanel');
    const publicTransportComboBox = document.getElementById('publicTransportComboBox');
    const departureDatePicker = document.getElementById('departureDatePicker');
    const departureTimeComboBox = document.getElementById('departureTimeComboBox');
    const arrivalTimeLabel = document.getElementById('arrivalTimeLabel');
    
    // Format departure time
    let depTimeStr = 'now';
    if (departureDatePicker.value && departureTimeComboBox.value) {
        depTimeStr = `${departureDatePicker.value}T${departureTimeComboBox.value}:00`;
    }
    
    // Prepare data to send
    const transportData = {
        fromLat: depLat,
        fromLon: depLon,
        toLat: arrLat,
        toLon: arrLon,
        departureTime: depTimeStr
    };
    
    // Call the API
    fetch('/navigation/public-transport', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(transportData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            showError('Public Transport Error', data.error);
            publicTransportOptionsPanel.style.display = 'none';
            progressIndicator.style.display = 'none';
            return;
        }
        
        if (data.status !== 'OK') {
            showError('Google API Error', `Error: ${data.status}`);
            publicTransportOptionsPanel.style.display = 'none';
            progressIndicator.style.display = 'none';
            return;
        }
        
        const routes = data.routes;
        publicTransportComboBox.innerHTML = '';
        
        if (routes.length === 0) {
            publicTransportComboBox.innerHTML = '<option value="">No routes found</option>';
            progressIndicator.style.display = 'none';
            return;
        }
        
        // Process routes and add them to dropdown
        routes.forEach((route, index) => {
            const leg = route.legs[0];
            const departure = leg.departure_time.text;
            const arrival = leg.arrival_time.text;
            const duration = leg.duration.text;
            
            // For fallback we'll use generic icons based on route index
            // This creates variety even when we don't have actual vehicle types
            const transportIcons = ['🚍', '🚆', '🚇', '🚋', '⛴️']; 
            const transportTypes = ['Bus', 'Train', 'Metro', 'Tram', 'Ferry'];
            
            // Use index to pick an icon, but cycle through the available icons
            const iconIndex = index % transportIcons.length;
            const transportIcon = transportIcons[iconIndex];
            const transportType = transportTypes[iconIndex];
            
            const option = document.createElement('option');
            option.value = index;
            option.innerHTML = `${transportIcon} <span>${transportType}</span> | Departure: ${departure} | Arrival: ${arrival} | Duration: ${duration}`;
            option.dataset.depTime = departure;
            option.dataset.arrTime = arrival;
            option.dataset.duration = duration;
            option.dataset.transportType = transportType;
            option.dataset.transportIcon = transportIcon;
            
            publicTransportComboBox.appendChild(option);
        });
        
        // Select first option by default
        if (publicTransportComboBox.options.length > 0) {
            publicTransportComboBox.selectedIndex = 0;
            const selected = publicTransportComboBox.options[0];
            
            // Display the route with a simple line for fallback
            showSimpleRoute(depLat, depLon, arrLat, arrLon, 'public_transport');
            
            // Update arrival time label with transport type
            const transportType = selected.dataset.transportType || 'Bus';
            const transportIcon = selected.dataset.transportIcon || '🚍';
            arrivalTimeLabel.textContent = `${transportIcon} ${transportType} | Departure: ${selected.dataset.depTime} | Arrival: ${selected.dataset.arrTime} | Duration: ${selected.dataset.duration}`;
        }
        
        progressIndicator.style.display = 'none';
    })
    .catch(error => {
        console.error('Error finding public transport:', error);
        publicTransportOptionsPanel.style.display = 'none';
        progressIndicator.style.display = 'none';
        showError('Error', 'Could not find public transport options: ' + error.message);
        
        // Show a simple route as fallback
        showSimpleRoute(depLat, depLon, arrLat, arrLon, 'public_transport');
    });
}