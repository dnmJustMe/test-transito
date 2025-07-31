// Inicialización de la aplicación
$(function() {
    console.log('Inicializando aplicación...');
    checkAuthStatus();
    showSection('home');
    setupEventListeners();
    
    // Forzar actualización de UI al cargar
    if (!currentUser) {
        updateUIForGuest();
    }
});