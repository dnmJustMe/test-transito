<?php
/**
 * Perfil de Usuario - Gestión de cuenta
 */

require_once '../config/config.php';

$auth = new Auth();

// Verificar que el usuario esté logueado
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$currentUser = $auth->getCurrentUser();
$currentPage = 'profile';

$title = 'Mi Perfil - Test Licencia Cuba';
$description = 'Gestiona tu perfil y configuración de cuenta';

ob_start();
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2>
                <i class="bi bi-person-circle me-2"></i>
                Mi Perfil
            </h2>
            <p class="text-muted mb-0">Gestiona tu información personal y configuración</p>
        </div>
    </div>

    <div class="row">
        <!-- Información del Perfil -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-container mx-auto" style="width: 120px; height: 120px;">
                            <?php if ($currentUser['avatar']): ?>
                                <img src="<?= UPLOADS_URL ?>/avatars/<?= htmlspecialchars($currentUser['avatar']) ?>" 
                                     alt="Avatar" class="rounded-circle img-fluid" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 120px; height: 120px;">
                                    <i class="bi bi-person-fill text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="changeAvatar()">
                            <i class="bi bi-camera me-1"></i>
                            Cambiar Foto
                        </button>
                    </div>
                    
                    <h4><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></h4>
                    <p class="text-muted mb-1">@<?= htmlspecialchars($currentUser['username']) ?></p>
                    <p class="text-muted"><?= htmlspecialchars($currentUser['email']) ?></p>
                    
                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <div class="h5 text-primary mb-0" id="profileTotalTests">-</div>
                            <small class="text-muted">Tests</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 text-success mb-0" id="profileBestScore">-</div>
                            <small class="text-muted">Mejor</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 text-info mb-0" id="profileAverage">-</div>
                            <small class="text-muted">Promedio</small>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Miembro desde <?= date('M Y', strtotime($currentUser['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Logros y Insignias -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-award me-2"></i>
                        Logros
                    </h5>
                </div>
                <div class="card-body">
                    <div id="achievementsContainer">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted small">Cargando logros...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formularios de Configuración -->
        <div class="col-lg-8">
            <!-- Información Personal -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>
                        Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profileForm" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" 
                                           value="<?= htmlspecialchars($currentUser['first_name']) ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor ingresa tu nombre.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="lastName" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" 
                                           value="<?= htmlspecialchars($currentUser['last_name']) ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor ingresa tus apellidos.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($currentUser['username']) ?>" required>
                            <div class="invalid-feedback">
                                Por favor ingresa un nombre de usuario válido.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                            <div class="invalid-feedback">
                                Por favor ingresa un correo electrónico válido.
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cambiar Contraseña -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>
                        Seguridad
                    </h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <div class="invalid-feedback">
                                Por favor ingresa tu contraseña actual.
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                    <div class="invalid-feedback">
                                        La contraseña debe tener al menos 8 caracteres.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirmNewPassword" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirmNewPassword" name="confirm_password" required>
                                    <div class="invalid-feedback">
                                        Las contraseñas no coinciden.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-shield-check me-2"></i>
                                Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preferencias -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Preferencias
                    </h5>
                </div>
                <div class="card-body">
                    <form id="preferencesForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="defaultQuestions" class="form-label">Preguntas por defecto en tests</label>
                                    <select class="form-select" id="defaultQuestions" name="default_questions">
                                        <option value="10">10 preguntas</option>
                                        <option value="20" selected>20 preguntas</option>
                                        <option value="30">30 preguntas</option>
                                        <option value="50">50 preguntas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="defaultDifficulty" class="form-label">Dificultad preferida</label>
                                    <select class="form-select" id="defaultDifficulty" name="default_difficulty">
                                        <option value="">Todas</option>
                                        <option value="easy">Fácil</option>
                                        <option value="medium" selected>Intermedio</option>
                                        <option value="hard">Difícil</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    Recibir notificaciones por email
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="showHints" checked>
                                <label class="form-check-label" for="showHints">
                                    Mostrar pistas durante los tests
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Guardar Preferencias
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Eliminar Cuenta -->
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-header bg-danger bg-opacity-10 border-0">
                    <h5 class="mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Zona Peligrosa
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Una vez que elimines tu cuenta, no hay vuelta atrás. Por favor, ten cuidado.
                    </p>
                    <button class="btn btn-outline-danger" onclick="confirmDeleteAccount()">
                        <i class="bi bi-trash me-2"></i>
                        Eliminar Cuenta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar avatar -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>
                    Cambiar Foto de Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="avatarForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="avatarFile" class="form-label">Seleccionar imagen</label>
                        <input type="file" class="form-control" id="avatarFile" name="avatar" accept="image/*" required>
                        <div class="form-text">
                            Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.
                        </div>
                    </div>
                    <div id="avatarPreview" class="text-center mb-3" style="display: none;">
                        <img id="previewImg" src="" alt="Vista previa" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="uploadAvatar()">
                    <i class="bi bi-upload me-2"></i>
                    Subir Foto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeProfilePage();
});

function initializeProfilePage() {
    // Configurar formularios
    document.getElementById('profileForm').addEventListener('submit', updateProfile);
    document.getElementById('passwordForm').addEventListener('submit', changePassword);
    document.getElementById('preferencesForm').addEventListener('submit', updatePreferences);
    
    // Configurar validación de contraseñas
    setupPasswordValidation();
    
    // Configurar preview de avatar
    document.getElementById('avatarFile').addEventListener('change', previewAvatar);
    
    // Cargar estadísticas del perfil
    loadProfileStats();
    
    // Cargar logros
    loadAchievements();
}

function setupPasswordValidation() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmNewPassword');
    
    confirmPassword.addEventListener('input', function() {
        if (this.value !== newPassword.value) {
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            this.setCustomValidity('');
        }
    });
    
    newPassword.addEventListener('input', function() {
        if (confirmPassword.value && confirmPassword.value !== this.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    });
}

async function updateProfile(e) {
    e.preventDefault();
    
    const form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        showLoading(true);
        
        const response = await apiRequest('/api/v1/auth/profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken
            },
            body: JSON.stringify(data)
        });
        
        if (response.success) {
            showAlert('Perfil actualizado correctamente', 'success');
            // Actualizar datos del usuario en la interfaz
            updateUserInterface(response.data);
        } else {
            throw new Error(response.message || 'Error actualizando el perfil');
        }
    } catch (error) {
        console.error('Error actualizando perfil:', error);
        showAlert(error.message || 'Error al actualizar el perfil', 'error');
    } finally {
        showLoading(false);
    }
}

async function changePassword(e) {
    e.preventDefault();
    
    const form = e.target;
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    if (data.new_password !== data.confirm_password) {
        showAlert('Las contraseñas no coinciden', 'error');
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await apiRequest('/api/v1/auth/change-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken
            },
            body: JSON.stringify({
                current_password: data.current_password,
                new_password: data.new_password
            })
        });
        
        if (response.success) {
            showAlert('Contraseña cambiada correctamente', 'success');
            form.reset();
            form.classList.remove('was-validated');
        } else {
            throw new Error(response.message || 'Error cambiando la contraseña');
        }
    } catch (error) {
        console.error('Error cambiando contraseña:', error);
        showAlert(error.message || 'Error al cambiar la contraseña', 'error');
    } finally {
        showLoading(false);
    }
}

function updatePreferences(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const preferences = Object.fromEntries(formData.entries());
    
    // Guardar en localStorage por ahora
    localStorage.setItem('userPreferences', JSON.stringify(preferences));
    
    showAlert('Preferencias guardadas correctamente', 'success');
}

function changeAvatar() {
    const modal = new bootstrap.Modal(document.getElementById('avatarModal'));
    modal.show();
}

function previewAvatar(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('avatarPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

async function uploadAvatar() {
    const fileInput = document.getElementById('avatarFile');
    const file = fileInput.files[0];
    
    if (!file) {
        showAlert('Por favor selecciona una imagen', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    try {
        showLoading(true);
        
        const response = await fetch(window.App.apiUrl + '/auth/avatar', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': window.App.csrfToken
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Foto de perfil actualizada', 'success');
            // Actualizar imagen en la interfaz
            location.reload(); // Recargar para mostrar la nueva imagen
        } else {
            throw new Error(result.message || 'Error subiendo la imagen');
        }
    } catch (error) {
        console.error('Error subiendo avatar:', error);
        showAlert(error.message || 'Error al subir la imagen', 'error');
    } finally {
        showLoading(false);
        bootstrap.Modal.getInstance(document.getElementById('avatarModal')).hide();
    }
}

async function loadProfileStats() {
    try {
        const response = await apiRequest('/api/v1/tests/stats');
        
        if (response.success && response.data) {
            const stats = response.data;
            
            document.getElementById('profileTotalTests').textContent = stats.total_tests || 0;
            document.getElementById('profileBestScore').textContent = (stats.best_score || 0) + '%';
            document.getElementById('profileAverage').textContent = (stats.average_score || 0) + '%';
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Mostrar valores por defecto
        document.getElementById('profileTotalTests').textContent = '0';
        document.getElementById('profileBestScore').textContent = '0%';
        document.getElementById('profileAverage').textContent = '0%';
    }
}

function loadAchievements() {
    // Simular logros por ahora
    const achievements = [
        { icon: 'bi-play-circle', title: 'Primer Test', description: 'Completaste tu primer test', earned: true },
        { icon: 'bi-trophy', title: 'Puntuación Perfecta', description: 'Obtuviste 100% en un test', earned: false },
        { icon: 'bi-lightning', title: 'Velocista', description: 'Completaste un test en menos de 5 minutos', earned: false },
        { icon: 'bi-calendar-check', title: 'Constante', description: 'Realizaste tests 7 días seguidos', earned: false }
    ];
    
    const container = document.getElementById('achievementsContainer');
    
    const html = achievements.map(achievement => `
        <div class="d-flex align-items-center mb-3 ${achievement.earned ? '' : 'opacity-50'}">
            <div class="me-3">
                <i class="bi ${achievement.icon} fs-4 ${achievement.earned ? 'text-warning' : 'text-muted'}"></i>
            </div>
            <div>
                <h6 class="mb-0">${achievement.title}</h6>
                <small class="text-muted">${achievement.description}</small>
            </div>
            ${achievement.earned ? '<div class="ms-auto"><i class="bi bi-check-circle text-success"></i></div>' : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function updateUserInterface(userData) {
    // Actualizar nombre en la interfaz
    document.querySelector('h4').textContent = userData.first_name + ' ' + userData.last_name;
    // Actualizar otros elementos según sea necesario
}

function confirmDeleteAccount() {
    Swal.fire({
        title: '¿Eliminar cuenta?',
        text: 'Esta acción no se puede deshacer. Se eliminarán todos tus datos permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        input: 'text',
        inputPlaceholder: 'Escribe "ELIMINAR" para confirmar',
        inputValidator: (value) => {
            if (value !== 'ELIMINAR') {
                return 'Debes escribir "ELIMINAR" para confirmar';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            deleteAccount();
        }
    });
}

async function deleteAccount() {
    try {
        showLoading(true);
        
        const response = await apiRequest('/api/v1/auth/delete-account', {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': window.App.csrfToken
            }
        });
        
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Cuenta eliminada',
                text: 'Tu cuenta ha sido eliminada correctamente.',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = window.App.baseUrl;
            });
        } else {
            throw new Error(response.message || 'Error eliminando la cuenta');
        }
    } catch (error) {
        console.error('Error eliminando cuenta:', error);
        showAlert(error.message || 'Error al eliminar la cuenta', 'error');
    } finally {
        showLoading(false);
    }
}
</script>

<?php
$content = ob_get_clean();
include '../templates/base.php';
?>