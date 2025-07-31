/**
 * Test Licencia Cuba - JavaScript Principal
 * Funcionalidades generales de la aplicación
 */

// Inicialización global
document.addEventListener('DOMContentLoaded', function() {
    // Configurar AJAX globalmente
    setupAjax();
    
    // Inicializar componentes
    initializeComponents();
    
    // Configurar validaciones globales
    setupGlobalValidations();
    
    // Configurar eventos globales
    setupGlobalEvents();
    
    console.log('App initialized successfully');
});

/**
 * Configuración global de AJAX
 */
function setupAjax() {
    // Configurar jQuery AJAX si está disponible
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': window.App.csrfToken,
                'Content-Type': 'application/json'
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                hideLoading();
            },
            error: function(xhr, status, error) {
                handleAjaxError(xhr, status, error);
            }
        });
    }
    
    // Configurar Fetch API globalmente
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        // Agregar headers por defecto
        if (args[1]) {
            args[1].headers = {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken,
                ...args[1].headers
            };
        }
        
        return originalFetch.apply(this, args);
    };
}

/**
 * Inicializar componentes de la aplicación
 */
function initializeComponents() {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers de Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Configurar animaciones
    setupAnimations();
}

/**
 * Configurar validaciones globales
 */
function setupGlobalValidations() {
    // Validación de formularios con Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Mostrar primer error
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    showAlert('Por favor, corrige los errores en el formulario', 'warning');
                }
            }
            form.classList.add('was-validated');
        });
    });
    
    // Validaciones en tiempo real
    setupRealTimeValidation();
}

/**
 * Configurar eventos globales
 */
function setupGlobalEvents() {
    // Prevenir envío doble de formularios
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('submitting')) {
            e.preventDefault();
            return false;
        }
        form.classList.add('submitting');
        
        // Remover clase después de 3 segundos
        setTimeout(() => {
            form.classList.remove('submitting');
        }, 3000);
    });
    
    // Manejar enlaces con confirmación
    document.addEventListener('click', function(e) {
        const link = e.target.closest('[data-confirm]');
        if (link) {
            e.preventDefault();
            const message = link.getAttribute('data-confirm') || '¿Estás seguro?';
            confirmAction(message, () => {
                if (link.href) {
                    window.location.href = link.href;
                } else if (link.onclick) {
                    link.onclick();
                }
            });
        }
    });
    
    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            fadeOut(alert, 500);
        });
    }, 5000);
}

/**
 * Configurar animaciones
 */
function setupAnimations() {
    // Intersection Observer para animaciones al scroll
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, { threshold: 0.1 });
        
        // Observar elementos con clase animate-on-scroll
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }
}

/**
 * Validaciones en tiempo real
 */
function setupRealTimeValidation() {
    // Validación de email
    document.addEventListener('input', function(e) {
        if (e.target.type === 'email') {
            const email = e.target.value;
            const isValid = validateEmail(email);
            toggleFieldValidation(e.target, isValid, 'Email no válido');
        }
    });
    
    // Validación de contraseñas
    document.addEventListener('input', function(e) {
        if (e.target.type === 'password' && e.target.name === 'password') {
            const password = e.target.value;
            const strength = getPasswordStrength(password);
            showPasswordStrength(e.target, strength);
        }
    });
    
    // Confirmación de contraseña
    document.addEventListener('input', function(e) {
        if (e.target.name === 'confirm_password' || e.target.name === 'password_confirmation') {
            const password = document.querySelector('input[name="password"]');
            const confirmPassword = e.target;
            const isValid = password && password.value === confirmPassword.value;
            toggleFieldValidation(confirmPassword, isValid, 'Las contraseñas no coinciden');
        }
    });
}

/**
 * Mostrar/Ocultar loading spinner
 */
function showLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.remove('d-none');
    }
}

function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.add('d-none');
    }
}

/**
 * Mostrar alertas
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto-hide
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, duration);
    }
}

/**
 * Obtener icono para tipo de alerta
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle-fill',
        'danger': 'exclamation-triangle-fill',
        'warning': 'exclamation-triangle-fill',
        'info': 'info-circle-fill',
        'primary': 'info-circle-fill',
        'secondary': 'info-circle-fill'
    };
    return icons[type] || 'info-circle-fill';
}

/**
 * Confirmar acción
 */
function confirmAction(message, onConfirm, onCancel = null) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            onConfirm();
        } else if (onCancel) {
            onCancel();
        }
    });
}

/**
 * Manejar errores de AJAX
 */
function handleAjaxError(xhr, status, error) {
    console.error('AJAX Error:', { xhr, status, error });
    
    let message = 'Error de conexión';
    
    if (xhr.responseJSON) {
        message = xhr.responseJSON.message || message;
    } else if (xhr.status === 0) {
        message = 'Sin conexión a internet';
    } else if (xhr.status === 404) {
        message = 'Recurso no encontrado';
    } else if (xhr.status === 500) {
        message = 'Error interno del servidor';
    } else if (xhr.status === 403) {
        message = 'Acceso denegado';
    } else if (xhr.status === 401) {
        message = 'Sesión expirada';
        // Redirigir al login después de 2 segundos
        setTimeout(() => {
            window.location.href = window.App.baseUrl + '/login';
        }, 2000);
    }
    
    showAlert(message, 'danger');
}

/**
 * Validaciones
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 6;
}

function getPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

function showPasswordStrength(field, strength) {
    let strengthText = '';
    let strengthClass = '';
    
    if (strength < 2) {
        strengthText = 'Muy débil';
        strengthClass = 'text-danger';
    } else if (strength < 4) {
        strengthText = 'Débil';
        strengthClass = 'text-warning';
    } else if (strength < 5) {
        strengthText = 'Buena';
        strengthClass = 'text-info';
    } else {
        strengthText = 'Fuerte';
        strengthClass = 'text-success';
    }
    
    // Buscar o crear indicador de fuerza
    let indicator = field.parentNode.querySelector('.password-strength');
    if (!indicator) {
        indicator = document.createElement('small');
        indicator.className = 'password-strength form-text';
        field.parentNode.appendChild(indicator);
    }
    
    indicator.textContent = `Fuerza: ${strengthText}`;
    indicator.className = `password-strength form-text ${strengthClass}`;
}

function toggleFieldValidation(field, isValid, errorMessage) {
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        
        feedback.textContent = errorMessage;
        feedback.style.display = 'block';
    }
}

/**
 * Utilidades de animación
 */
function fadeIn(element, duration = 300) {
    element.style.opacity = 0;
    element.style.display = 'block';
    
    let opacity = 0;
    const timer = setInterval(() => {
        opacity += 50 / duration;
        if (opacity >= 1) {
            clearInterval(timer);
            opacity = 1;
        }
        element.style.opacity = opacity;
    }, 50);
}

function fadeOut(element, duration = 300) {
    let opacity = 1;
    const timer = setInterval(() => {
        opacity -= 50 / duration;
        if (opacity <= 0) {
            clearInterval(timer);
            element.style.display = 'none';
            opacity = 0;
        }
        element.style.opacity = opacity;
    }, 50);
}

/**
 * Utilidades generales
 */
function formatDate(date) {
    if (!date) return '';
    
    const d = new Date(date);
    return d.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatDateTime(date) {
    if (!date) return '';
    
    const d = new Date(date);
    return d.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatTime(seconds) {
    if (!seconds) return '0:00';
    
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Copiado al portapapeles', 'success', 2000);
        });
    } else {
        // Fallback para navegadores antiguos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('Copiado al portapapeles', 'success', 2000);
    }
}

function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * API Helper Functions
 */
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(window.App.apiUrl + url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken,
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Error en la solicitud');
        }
        
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

async function apiGet(url) {
    return apiRequest(url, { method: 'GET' });
}

async function apiPost(url, data) {
    return apiRequest(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

async function apiPut(url, data) {
    return apiRequest(url, {
        method: 'PUT',
        body: JSON.stringify(data)
    });
}

async function apiDelete(url) {
    return apiRequest(url, { method: 'DELETE' });
}

/**
 * Local Storage Helpers
 */
function setLocalStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error('Error saving to localStorage:', error);
        return false;
    }
}

function getLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Error reading from localStorage:', error);
        return defaultValue;
    }
}

function removeLocalStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('Error removing from localStorage:', error);
        return false;
    }
}

/**
 * Exportar funciones globales
 */
window.AppUtils = {
    showAlert,
    showLoading,
    hideLoading,
    confirmAction,
    validateEmail,
    validatePassword,
    formatDate,
    formatDateTime,
    formatTime,
    copyToClipboard,
    debounce,
    throttle,
    apiRequest,
    apiGet,
    apiPost,
    apiPut,
    apiDelete,
    setLocalStorage,
    getLocalStorage,
    removeLocalStorage
};

// Registro de Service Worker (si está disponible)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}