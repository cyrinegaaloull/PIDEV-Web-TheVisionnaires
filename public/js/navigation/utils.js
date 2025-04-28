// utils.js - Utility functions for navigation
// Version: stable3

// Show error dialog
export function showError(title, message) {
    alert(`${title}: ${message}`);
}

// Check if coordinates are valid
export function isValidCoordinates(lat1, lon1, lat2, lon2) {
    return isValidLatitude(lat1) && isValidLongitude(lon1) &&
           isValidLatitude(lat2) && isValidLongitude(lon2);
}

// Check if latitude is valid
export function isValidLatitude(lat) {
    return lat >= -90 && lat <= 90;
}

// Check if longitude is valid
export function isValidLongitude(lon) {
    return lon >= -180 && lon <= 180;
}