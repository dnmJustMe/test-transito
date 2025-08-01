// Inicialización de la aplicación
$(function() {
    console.log('Inicializando aplicación...');
    
    // Asegurar estado inicial correcto
    $('#userMenu').hide();
    $('#userMenu').css('display', 'none');
    $('.dropdown-menu').removeClass('show');
    $('.dropdown-toggle').removeClass('show');
    
    checkAuthStatus();
    showSection('home');
    setupEventListeners();
    
    // Forzar actualización de UI al cargar
    if (!currentUser) {
        updateUIForGuest();
    }
});