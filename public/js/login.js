// Variables
let container = document.getElementById('container');

// Function to update file name display
function updateFileName(input) {
    const fileName = input.files[0]?.name || "Choose profile picture";
    const fileLabel = input.closest('.input-file-group').querySelector('.file-text');
    fileLabel.textContent = fileName;
}

// Initialize view based on form state and messages
document.addEventListener('DOMContentLoaded', function() {
    // Check URL parameters for redirect instructions
    const urlParams = new URLSearchParams(window.location.search);
    const registrationSuccess = urlParams.get('registration');
    
    // Check for flash messages
    const successMessages = document.querySelectorAll('.sign-in .flash-success');
    const signupErrors = document.querySelectorAll('.sign-up .flash-danger');
    
    // Determine which form to show
    let showSignUp = false;
    
    // If there's a registration success parameter or success messages, show login form
    if (registrationSuccess === 'success' || successMessages.length > 0) {
        showSignUp = false;
    }
    // If there are signup errors, show signup form
    else if (signupErrors.length > 0) {
        showSignUp = true;
    }
    
    // Apply the appropriate classes
    if (showSignUp) {
        container.classList.add('sign-up');
        container.classList.remove('sign-in');
    } else {
        container.classList.add('sign-in');
        container.classList.remove('sign-up');
    }
    
    // Add animation to flash messages and auto-hide after delay
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        // Ensure the message is visible with animation
        message.style.animation = 'fadeIn 0.5s';
        message.style.opacity = '1';
        
        // Auto-hide flash messages after 7 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500); // Wait for fade out animation to complete
        }, 1000);
    });
});

// Toggle function between sign-in and sign-up
function toggle() {
    container.classList.toggle('sign-in');
    container.classList.toggle('sign-up');
    
    // Clear form fields when switching
    clearFormFields();
}

// Clear form fields
function clearFormFields() {
    const inputs = document.querySelectorAll('input:not([type="hidden"])');
    inputs.forEach(input => {
        if (input.type !== 'submit' && input.type !== 'file') {
            input.value = '';
        }
    });
    
    // Reset file input display
    const fileTexts = document.querySelectorAll('.file-text');
    fileTexts.forEach(text => {
        text.textContent = 'Choose profile picture';
    });
}