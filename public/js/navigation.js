// navigation.js - Updated with Google Maps implementation
//stable3
// Initialize Google Maps
function initNavigation() {
    // Check if Google Maps API is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API not loaded! Retrying in 500ms...');
        setTimeout(initNavigation, 500);
        return;
    }

    // Check if map container exists
    const mapContainer = document.getElementById('navigationMap');
    if (!mapContainer) {
        console.error('Map container not found! Retrying in 500ms...');
        setTimeout(initNavigation, 500);
        return;
    }

    console.log('Initializing Google Maps...');

    // Initialize the map with Tunis coordinates
    const map = new google.maps.Map(mapContainer, {
        center: { lat: 36.8065, lng: 10.1815 },
        zoom: 6,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        fullscreenControl: true,
        streetViewControl: false,
        mapTypeControl: true,
        zoomControl: true
    });
    
    // Global variables
    let markers = [];
    let routeLines = [];
    let currentRouteId = null;
    let mapInitialized = true;
    let directionsService = new google.maps.DirectionsService();
    let directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true, // We'll handle markers manually
        preserveViewport: false
    });
    directionsRenderer.setMap(map);
    setupMapClickPositioning();

    // DOM elements
    const departurePlaceField = document.getElementById('departurePlaceField');
    const departureLatField = document.getElementById('departureLatField');
    const departureLonField = document.getElementById('departureLonField');
    const arrivalPlaceField = document.getElementById('arrivalPlaceField');
    const arrivalLatField = document.getElementById('arrivalLatField');
    const arrivalLonField = document.getElementById('arrivalLonField');
    const transportModeComboBox = document.getElementById('transportModeComboBox');
    const showRouteButton = document.getElementById('showRouteButton');
    const clearRouteButton = document.getElementById('clearRouteButton');
    const currentLocationButton = document.getElementById('currentLocationButton');
    const swapLocationsButton = document.getElementById('swapLocationsButton');
    const departureDatePicker = document.getElementById('departureDatePicker');
    const departureTimeComboBox = document.getElementById('departureTimeComboBox');
    const dateTimeSection = document.getElementById('dateTimeSection');
    const arrivalTimeLabel = document.getElementById('arrivalTimeLabel');
    const routeNameField = document.getElementById('routeNameField');
    const progressIndicator = document.getElementById('progressIndicator');
    const savedRoutesComboBox = document.getElementById('savedRoutesComboBox');
    const deleteRouteButton = document.getElementById('deleteRouteButton');
    const publicTransportOptionsPanel = document.getElementById('publicTransportOptionsPanel');
    const publicTransportComboBox = document.getElementById('publicTransportComboBox');
    
    // Location suggestions elements
    const departureSuggestions = document.getElementById('departureSuggestions');
    const arrivalSuggestions = document.getElementById('arrivalSuggestions');
    
    // Add autocomplete for places
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
    
    // Set today's date as default
    if (departureDatePicker) {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        departureDatePicker.value = formattedDate;
    }
    
    // ******************************************
    // Map Functions
    // ******************************************
    
    // Function to clear existing route
    function clearRoute() {
        // Remove markers
        markers.forEach(marker => marker.setMap(null));
        markers = [];
        
        // Remove route lines
        routeLines.forEach(line => line.setMap(null));
        routeLines = [];
        
        // Clear directions
        directionsRenderer.setDirections({routes: []});
    }
    
    // Function to show simple route on map (straight line)
    function showSimpleRoute(depLat, depLon, arrLat, arrLon, transportMode) {
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
    function showRoute(depLat, depLon, arrLat, arrLon, transportMode) {
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
    
    // ******************************************
    // API Functions
    // ******************************************
    
    // Search for places using Google Places Autocomplete
// Search for places using Nominatim Autocomplete
function searchLocation(query, isForDeparture) {
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
                        departurePlaceField.value = place.display_name;
                        departureLatField.value = place.lat;
                        departureLonField.value = place.lon;
                        departureSuggestions.style.display = 'none';
                    } else {
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
    function getCurrentLocation() {
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
    
    // Calculate route
    function calculateRoute(save = true) {
        console.log("testing calculateRoute with save =", save);
        
        try {
            // Check map initialization
            console.log("Map initialized:", mapInitialized);
            if (!mapInitialized) {
                showError('Map not ready', 'Please wait for the map to finish loading.');
                return;
            }
        
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
                console.log("Checking coordinate validity:", {depLat, depLon, arrLat, arrLon});
                console.log("isValidLatitude(depLat):", isValidLatitude(depLat));
                console.log("isValidLongitude(depLon):", isValidLongitude(depLon));
                console.log("isValidLatitude(arrLat):", isValidLatitude(arrLat));
                console.log("isValidLongitude(arrLon):", isValidLongitude(arrLon));
                
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
                    id: currentRouteId,
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
    
    
    // Find public transport options
    function findPublicTransport(save = true) {
        try {
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
                        
                        // Display the route
                        directionsRenderer.setDirections(response);
                        directionsRenderer.setRouteIndex(parseInt(selected.dataset.index));
                        
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
                            id: currentRouteId,
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
    
    // Clear only markers
    function clearMarkers() {
        markers.forEach(marker => marker.setMap(null));
        markers = [];
    }
    
    // Load saved routes from server
    function loadSavedRoutes() {
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
    function loadSavedRoute(routeId) {
        if (!routeId) return;
        
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
                currentRouteId = routeId;

                // Set transport mode if it exists in the dropdown
                const transportModeExists = Array.from(transportModeComboBox.options)
                    .some(option => option.value === route.transportMode);
                
                if (transportModeExists) {
                    transportModeComboBox.value = route.transportMode;
                }
                
                routeNameField.value = route.name;
                showRouteButton.innerText = "Update"
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
    function deleteRoute() {
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
                    currentRouteId = null;
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
    
    // ******************************************
    // Helper Functions
    // ******************************************
    
    // Check if coordinates are valid
    function isValidCoordinates(lat1, lon1, lat2, lon2) {
        return isValidLatitude(lat1) && isValidLongitude(lon1) &&
               isValidLatitude(lat2) && isValidLongitude(lon2);
    }
    
    function isValidLatitude(lat) {
        return lat >= -90 && lat <= 90;
    }
    
    function isValidLongitude(lon) {
        return lon >= -180 && lon <= 180;
    }
    
    // Swap departure and arrival locations
    function swapLocations() {
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
    
    // Clear all location fields
    function handleClearRoute() {
        clearRoute();
        
        // Clear input fields
        departurePlaceField.value = '';
        departureLatField.value = '';
        departureLonField.value = '';
        arrivalPlaceField.value = '';
        arrivalLatField.value = '';
        arrivalLonField.value = '';
        currentRouteId = null;
        // Reset dropdowns to first option
        if (transportModeComboBox.options.length > 0) {
            transportModeComboBox.selectedIndex = 0;
        }
        
        if (savedRoutesComboBox.options.length > 0) {
            savedRoutesComboBox.selectedIndex = 0;
        }
        
        // Hide public transport options
        publicTransportOptionsPanel.style.display = 'none';
        
        // Reset arrival time label
        arrivalTimeLabel.textContent = 'Route information will appear here';
        
        // Clear route name
        routeNameField.value = '';
        showRouteButton.innerText = "Show Route"
        // Disable show route button
        updateButtonState();
    }
    
    // Update button state based on form fields
    function updateButtonState() {
        const hasDepCoords = departureLatField.value && departureLonField.value;
        const hasArrCoords = arrivalLatField.value && arrivalLonField.value;
        
        showRouteButton.disabled = !(hasDepCoords && hasArrCoords);
    }
    
    // Show error dialog
    function showError(title, message) {
        alert(`${title}: ${message}`);
    }
    
    // Check for URL parameter to load a route
    function checkForRouteParam() {
        const urlParams = new URLSearchParams(window.location.search);
        const routeId = urlParams.get('route');
        
        if (routeId) {
            savedRoutesComboBox.value = routeId;
            loadSavedRoute(routeId);
        }
    }
    
    // ******************************************
    // Event Listeners
    // ******************************************
    
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
            dateTimeSection.style.display = isPublicTransport ? 'block' : 'none';
            
            if (isPublicTransport && departureLatField.value && departureLonField.value && 
                arrivalLatField.value && arrivalLonField.value) {
                findPublicTransport();
            }
        });
    }
    
    // Public transport option change
    if (publicTransportComboBox) {
        publicTransportComboBox.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || !selectedOption.dataset) return;
            
            if (window.transitResponse && selectedOption.dataset.index) {
                // Use the stored transit response
                directionsRenderer.setRouteIndex(parseInt(selectedOption.dataset.index));
                
                // Update arrival time label if data is available
                const routeIndex = parseInt(selectedOption.dataset.index);
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
                }
            } else if (selectedOption.dataset.depTime) {
                // Fallback to saved dataset values
                const transportType = selectedOption.dataset.transportType || 'Bus';
                const transportIcon = selectedOption.dataset.transportIcon || '🚍';
                arrivalTimeLabel.textContent = `${transportIcon} ${transportType} | Departure: ${selectedOption.dataset.depTime} | Arrival: ${selectedOption.dataset.arrTime} | Duration: ${selectedOption.dataset.duration}`;
            }
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
        document.addEventListener('click', function(event) {
            if (!departurePlaceField.contains(event.target) && 
                !departureSuggestions.contains(event.target)) {
                departureSuggestions.style.display = 'none';
            }
        });
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
        document.addEventListener('click', function(event) {
            if (!arrivalPlaceField.contains(event.target) && 
                !arrivalSuggestions.contains(event.target)) {
                arrivalSuggestions.style.display = 'none';
            }
        });
    }
    
    // Listen for coordinate field changes to update button state
    [departureLatField, departureLonField, arrivalLatField, arrivalLonField].forEach(field => {
        if (field) {
            field.addEventListener('change', updateButtonState);
        }
    });
    
    // Initialize
    updateButtonState();
    checkForRouteParam();
    // Function to handle map clicks for setting departure and arrival points
function setupMapClickPositioning() {
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
}

// Trigger the initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Google Maps when DOM is ready
    initNavigation();
});