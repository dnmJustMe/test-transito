<?php
/**
 * Página de Inicio de Sesión
 */

require_once '../config/config.php';

$auth = new Auth();

// Si ya está logueado, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$currentUser = null;
$currentPage = 'login';
$hideNavigation = true;
$hideFooter = true;

$title = 'Iniciar Sesión - Test Licencia Cuba';
$description = 'Inicia sesión en tu cuenta de Test Licencia Cuba para acceder a los simuladores de examen.';

$customJS = ['auth.js'];

ob_start();
?>

<div class="app-container">
    <div class="app-box animate__animated animate__fadeIn">
        <!-- Header -->
        <div class="text-center mb-4">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" class="mb-3" style="max-height: 80px;">
            <h2 class="text-gradient mb-2">Iniciar Sesión</h2>
            <p class="text-muted">Accede a tu cuenta para continuar practicando</p>
        </div>

        <!-- Formulario de Login -->
        <form id="loginForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i>Correo Electrónico
                </label>
                <input type="email" class="form-control" id="email" name="email" required 
                       placeholder="tu@email.com" autocomplete="email">
                <div class="invalid-feedback">
                    Por favor, ingresa un email válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Contraseña
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required 
                           placeholder="Tu contraseña" autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    La contraseña es requerida.
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                        <label class="form-check-label" for="rememberMe">
                            Recordarme
                        </label>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-decoration-none small" onclick="showForgotPassword()">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>

            <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="text-center mb-3">
            <div class="d-flex align-items-center">
                <hr class="flex-grow-1">
                <span class="px-3 text-muted small">o</span>
                <hr class="flex-grow-1">
            </div>
        </div>

        <!-- Registro -->
        <div class="text-center">
            <p class="mb-2">¿No tienes una cuenta?</p>
            <a href="<?= BASE_URL ?>/register" class="btn btn-outline-success">
                <i class="bi bi-person-plus me-2"></i>Crear Cuenta Gratis
            </a>
        </div>

        <!-- Demo Account Info -->
        <div class="mt-4 p-3 bg-light rounded">
            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Cuenta de Demostración</h6>
            <p class="small mb-2">Puedes probar el sistema con estas credenciales:</p>
            <div class="row">
                <div class="col-md-6">
                    <strong>Usuario:</strong> admin@testlicencia.cu
                </div>
                <div class="col-md-6">
                    <strong>Contraseña:</strong> password
                </div>
            </div>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="fillDemoCredentials()">
                <i class="bi bi-play-circle me-1"></i>Usar Credenciales Demo
            </button>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 pt-3 border-top">
            <a href="<?= BASE_URL ?>" class="text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Volver al Inicio
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeLoginPage();
});

function initializeLoginPage() {
    // Configurar formulario de login
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', handleLogin);
    
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
    
    // Auto-fill from URL parameters (for redirects)
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');
    if (email) {
        document.getElementById('email').value = email;
    }
}

async function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const data = {
        email: formData.get('email'),
        password: formData.get('password'),
        remember_me: formData.get('remember_me') ? true : false
    };
    
    try {
        // Mostrar loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Iniciando sesión...';
        submitBtn.disabled = true;
        
        const response = await fetch(`${window.App.apiUrl}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar token CSRF
            window.App.csrfToken = result.data.csrf_token;
            
            // Mostrar éxito
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Redirigir al dashboard
                const redirectUrl = new URLSearchParams(window.location.search).get('redirect') || '/dashboard';
                window.location.href = window.App.baseUrl + redirectUrl;
            });
        } else {
            throw new Error(result.message);
        }
        
    } catch (error) {
        console.error('Login error:', error);
        
        Swal.fire({
            icon: 'error',
            title: 'Error de Inicio de Sesión',
            text: error.message || 'Error al iniciar sesión. Verifica tus credenciales.',
            confirmButtonText: 'Intentar de nuevo'
        });
        
        // Restaurar botón
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // Limpiar contraseña
        document.getElementById('password').value = '';
        document.getElementById('password').focus();
    }
}

function fillDemoCredentials() {
    document.getElementById('email').value = 'admin@testlicencia.cu';
    document.getElementById('password').value = 'password';
    
    // Trigger validation
    document.getElementById('email').dispatchEvent(new Event('input'));
    document.getElementById('password').dispatchEvent(new Event('input'));
    
    showAlert('Credenciales de demostración cargadas', 'info', 3000);
}

function showForgotPassword() {
    Swal.fire({
        title: 'Recuperar Contraseña',
        html: `
            <div class="text-start">
                <p>Para recuperar tu contraseña, contacta al administrador del sistema:</p>
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-envelope me-3 text-primary"></i>
                    <span>admin@testlicencia.cu</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-telephone me-3 text-primary"></i>
                    <span>+53 7 123-4567</span>
                </div>
                <hr>
                <p class="small text-muted">Proporciona tu nombre de usuario o email registrado para recuperar el acceso.</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}
</script>

<?php
$content = ob_get_clean();
include '../templates/base.php';
?>