/**
 * Transport Modes Management JavaScript
 * This file handles all the CRUD operations for transport modes
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("Transport modes JS loaded");
    
    // Setup form elements
    const transportModeForm = document.getElementById('transportModeForm');
    const modeIdInput = document.getElementById('modeId');
    const modeNameInput = document.getElementById('modeName');
    const formTitle = document.getElementById('formTitle');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');
    
    console.log("Form found:", !!transportModeForm);
    
    // Setup modals
    const deleteModalElement = document.getElementById('deleteModal');
    const loadingModalElement = document.getElementById('loadingModal');
    const deleteModal = deleteModalElement ? new bootstrap.Modal(deleteModalElement) : null;
    const loadingModal = loadingModalElement ? new bootstrap.Modal(loadingModalElement) : null;
    const confirmDeleteButton = document.getElementById('confirmDelete');
    let modeToDelete = null;
    
    // Setup edit buttons
    const editButtons = document.querySelectorAll('.edit-mode');
    editButtons.forEach(button => {
      button.addEventListener('click', function() {
        console.log("Edit button clicked");
        const modeId = this.getAttribute('data-mode-id');
        const modeName = this.getAttribute('data-mode-name');
        
        console.log("Editing mode:", modeId, modeName);
        
        // Set form for editing
        modeIdInput.value = modeId;
        modeNameInput.value = modeName;
        formTitle.textContent = 'Modifier le mode de transport';
        saveButton.textContent = 'Mettre à jour';
      });
    });
    
    // Setup delete buttons
    const deleteButtons = document.querySelectorAll('.delete-mode');
    deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
        console.log("Delete button clicked");
        modeToDelete = this.getAttribute('data-mode-id');
        console.log("Mode to delete:", modeToDelete);
        if (deleteModal) {
          deleteModal.show();
        }
      });
    });
    
    // Confirm delete handler
    if (confirmDeleteButton) {
      confirmDeleteButton.addEventListener('click', function() {
        console.log("Confirm delete clicked");
        if (modeToDelete) {
          deleteTransportMode(modeToDelete);
          if (deleteModal) {
            deleteModal.hide();
          }
        }
      });
    }
    
    // Cancel button handler
    if (cancelButton) {
      cancelButton.addEventListener('click', function() {
        console.log("Cancel button clicked");
        resetForm();
      });
    }
    
    // Form submission handler
    if (transportModeForm) {
      transportModeForm.addEventListener('submit', function(e) {
        console.log("Form submitted");
        e.preventDefault();
        
        const modeName = modeNameInput.value.trim();
        console.log("Mode name:", modeName);
        
        if (!modeName) {
          alert('Veuillez entrer un nom pour le mode de transport');
          return;
        }
        
        const modeId = modeIdInput.value;
        console.log("Mode ID:", modeId);
        
        if (modeId) {
          // Update existing mode
          console.log("Updating mode");
          updateTransportMode(modeId, modeName);
        } else {
          // Create new mode
          console.log("Creating new mode");
          createTransportMode(modeName);
        }
      });
    }
    
    // Function to create a new transport mode
    function createTransportMode(name) {
      console.log("Creating transport mode:", name);
      if (loadingModal) {
        loadingModal.show();
      }
      
      fetch('/admin/transport-modes/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ name: name })
      })
        .then(response => {
          console.log("Response status:", response.status);
          return response.json();
        })
        .then(data => {
          console.log("Response data:", data);
          if (loadingModal) {
            loadingModal.hide();
          }
          
          if (data.success) {
            // Reload the page to show updated list
            window.location.reload();
          } else {
            alert('Erreur lors de la création: ' + (data.error || 'Erreur inconnue'));
          }
        })
        .catch(error => {
          console.error('Error creating transport mode:', error);
          if (loadingModal) {
            loadingModal.hide();
          }
          alert('Échec de la création du mode de transport');
        });
    }
    
    // Function to update a transport mode
    function updateTransportMode(id, name) {
      console.log("Updating transport mode:", id, name);
      if (loadingModal) {
        loadingModal.show();
      }
      
      fetch(`/admin/transport-modes/update/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ name: name })
      })
        .then(response => {
          console.log("Response status:", response.status);
          return response.json();
        })
        .then(data => {
          console.log("Response data:", data);
          if (loadingModal) {
            loadingModal.hide();
          }
          
          if (data.success) {
            // Reload the page to show updated list
            window.location.reload();
          } else {
            alert('Erreur lors de la mise à jour: ' + (data.error || 'Erreur inconnue'));
          }
        })
        .catch(error => {
          console.error('Error updating transport mode:', error);
          if (loadingModal) {
            loadingModal.hide();
          }
          alert('Échec de la mise à jour du mode de transport');
        });
    }
    
    // Function to delete a transport mode
    function deleteTransportMode(id) {
      console.log("Deleting transport mode:", id);
      if (loadingModal) {
        loadingModal.show();
      }
      
      fetch(`/admin/transport-modes/delete/${id}`, {
        method: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(response => {
          console.log("Response status:", response.status);
          return response.json();
        })
        .then(data => {
          console.log("Response data:", data);
          if (loadingModal) {
            loadingModal.hide();
          }
          
          if (data.success) {
            // Reload the page to show updated list
            window.location.reload();
          } else {
            alert('Erreur lors de la suppression: ' + (data.error || 'Erreur inconnue'));
          }
        })
        .catch(error => {
          console.error('Error deleting transport mode:', error);
          if (loadingModal) {
            loadingModal.hide();
          }
          alert('Échec de la suppression du mode de transport');
        });
    }
    
    // Function to reset the form
    function resetForm() {
      console.log("Resetting form");
      modeIdInput.value = '';
      modeNameInput.value = '';
      formTitle.textContent = 'Ajouter un mode de transport';
      saveButton.textContent = 'Enregistrer';
    }
  });