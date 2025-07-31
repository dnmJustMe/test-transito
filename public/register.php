<?php
/**
 * Página de Registro de Usuarios
 */

require_once '../config/config.php';

$auth = new Auth();

// Si el usuario ya está logueado, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$currentUser = null;
$currentPage = 'register';

$title = 'Registro - Test Licencia Cuba';
$description = 'Regístrate en Test Licencia Cuba y comienza a practicar para tu examen de conducir';

ob_start();
?>

<div class="app-container">
    <div class="app-box animate__animated animate__fadeIn">
        <div class="text-center mb-4">
            <h2 class="text-gradient">
                <i class="bi bi-person-plus-fill me-2"></i>
                Crear Cuenta
            </h2>
            <p class="text-muted">Únete a Test Licencia Cuba y comienza a practicar</p>
        </div>

        <!-- Formulario de Registro -->
        <form id="registerForm" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">
                            <i class="bi bi-person me-1"></i>Nombre
                        </label>
                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                        <div class="invalid-feedback">
                            Por favor ingresa tu nombre.
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="lastName" class="form-label">
                            <i class="bi bi-person me-1"></i>Apellidos
                        </label>
                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                        <div class="invalid-feedback">
                            Por favor ingresa tus apellidos.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-at me-1"></i>Nombre de Usuario
                </label>
                <input type="text" class="form-control" id="username" name="username" required>
                <div class="invalid-feedback">
                    Por favor ingresa un nombre de usuario.
                </div>
                <div class="form-text">
                    Solo letras, números y guiones bajos. Mínimo 3 caracteres.
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-1"></i>Correo Electrónico
                </label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">
                    Por favor ingresa un correo electrónico válido.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Contraseña
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    La contraseña debe tener al menos 8 caracteres.
                </div>
                <div class="form-text">
                    Mínimo 8 caracteres, incluye al menos una mayúscula, una minúscula y un número.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirmPassword" class="form-label">
                    <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña
                </label>
                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                <div class="invalid-feedback">
                    Las contraseñas no coinciden.
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="acceptTerms" required>
                <label class="form-check-label" for="acceptTerms">
                    Acepto los <a href="#" onclick="showTerms()">términos y condiciones</a> y la 
                    <a href="#" onclick="showPrivacy()">política de privacidad</a>
                </label>
                <div class="invalid-feedback">
                    Debes aceptar los términos y condiciones.
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-person-plus me-2"></i>
                    Crear Cuenta
                </button>
            </div>
        </form>

        <!-- Enlaces -->
        <div class="text-center mt-4">
            <p class="mb-0">
                ¿Ya tienes cuenta? 
                <a href="<?= BASE_URL ?>/login" class="text-decoration-none">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Iniciar Sesión
                </a>
            </p>
        </div>

        <!-- Información adicional -->
        <div class="mt-4 p-3 bg-light rounded">
            <h6 class="mb-2">
                <i class="bi bi-info-circle me-2"></i>
                ¿Por qué registrarse?
            </h6>
            <ul class="list-unstyled mb-0 small">
                <li><i class="bi bi-check-circle text-success me-2"></i>Guarda tu progreso y estadísticas</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Accede a tu historial de tests</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Recibe recomendaciones personalizadas</li>
                <li><i class="bi bi-check-circle text-success me-2"></i>Compite en rankings</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeRegisterPage();
});

function initializeRegisterPage() {
    const form = document.getElementById('registerForm');
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        const icon = document.getElementById('togglePasswordIcon');
        icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    // Validación en tiempo real de contraseñas
    confirmPassword.addEventListener('input', function() {
        if (this.value !== password.value) {
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            this.setCustomValidity('');
        }
    });

    password.addEventListener('input', function() {
        if (confirmPassword.value && confirmPassword.value !== this.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });

    // Submit del formulario
    form.addEventListener('submit', handleRegister);
}

async function handleRegister(e) {
    e.preventDefault();
    
    const form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Validaciones adicionales
    if (data.password !== data.confirm_password) {
        showAlert('Las contraseñas no coinciden', 'error');
        return;
    }

    if (data.password.length < 8) {
        showAlert('La contraseña debe tener al menos 8 caracteres', 'error');
        return;
    }

    try {
        showLoading(true);
        
        const response = await apiRequest('/api/v1/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken
            },
            body: JSON.stringify({
                username: data.username,
                email: data.email,
                password: data.password,
                first_name: data.first_name,
                last_name: data.last_name
            })
        });

        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Registro Exitoso!',
                text: 'Tu cuenta ha sido creada correctamente. Ahora puedes iniciar sesión.',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = window.App.baseUrl + '/login';
            });
        } else {
            throw new Error(response.message || 'Error en el registro');
        }
    } catch (error) {
        console.error('Error en registro:', error);
        showAlert(error.message || 'Error al crear la cuenta. Inténtalo de nuevo.', 'error');
    } finally {
        showLoading(false);
    }
}

function showTerms() {
    Swal.fire({
        title: 'Términos y Condiciones',
        html: `
            <div class="text-start">
                <h6>1. Uso del Servicio</h6>
                <p>Este servicio está destinado para la práctica de exámenes de conducir en Cuba.</p>
                
                <h6>2. Responsabilidades del Usuario</h6>
                <p>El usuario se compromete a usar el servicio de manera responsable y proporcionar información veraz.</p>
                
                <h6>3. Privacidad</h6>
                <p>Respetamos tu privacidad y protegemos tus datos personales según nuestra política de privacidad.</p>
                
                <h6>4. Modificaciones</h6>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento.</p>
            </div>
        `,
        confirmButtonText: 'Entendido',
        customClass: {
            popup: 'swal-wide'
        }
    });
}

function showPrivacy() {
    Swal.fire({
        title: 'Política de Privacidad',
        html: `
            <div class="text-start">
                <h6>Recopilación de Datos</h6>
                <p>Recopilamos únicamente los datos necesarios para proporcionar nuestros servicios.</p>
                
                <h6>Uso de Datos</h6>
                <p>Tus datos se utilizan para mejorar tu experiencia de aprendizaje y generar estadísticas.</p>
                
                <h6>Protección</h6>
                <p>Implementamos medidas de seguridad para proteger tu información personal.</p>
                
                <h6>Contacto</h6>
                <p>Para cualquier consulta sobre privacidad, contacta: admin@testlicencia.cu</p>
            </div>
        `,
        confirmButtonText: 'Entendido',
        customClass: {
            popup: 'swal-wide'
        }
    });
}
</script>

<style>
.swal-wide {
    width: 600px !important;
}
</style>

<?php
$content = ob_get_clean();
include '../templates/base.php';
?>