<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo" height="40" class="me-2">
            <span class="fw-bold">Test Licencia Cuba</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isset($currentUser)): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'test' ? 'active' : '' ?>" href="<?= BASE_URL ?>/test">
                            <i class="bi bi-play-circle me-1"></i>Realizar Test
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'history' ? 'active' : '' ?>" href="<?= BASE_URL ?>/history">
                            <i class="bi bi-clock-history me-1"></i>Historial
                        </a>
                    </li>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i>Administración
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard">
                                    <i class="bi bi-speedometer2 me-2"></i>Panel Admin
                                </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/questions">
                                    <i class="bi bi-question-circle me-2"></i>Preguntas
                                </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/users">
                                    <i class="bi bi-people me-2"></i>Usuarios
                                </a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/tests">
                                    <i class="bi bi-clipboard-data me-2"></i>Tests
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings">
                                    <i class="bi bi-sliders me-2"></i>Configuración
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>" href="<?= BASE_URL ?>">
                            <i class="bi bi-house-door me-1"></i>Inicio
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isset($currentUser)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php if ($currentUser['avatar']): ?>
                                <img src="<?= UPLOADS_URL ?>/avatars/<?= $currentUser['avatar'] ?>" alt="Avatar" class="rounded-circle me-2" width="30" height="30">
                            <?php else: ?>
                                <i class="bi bi-person-circle me-2 fs-5"></i>
                            <?php endif; ?>
                            <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['first_name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">
                                <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                                <small class="text-muted d-block">@<?= htmlspecialchars($currentUser['username']) ?></small>
                            </h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/history">
                                <i class="bi bi-clock-history me-2"></i>Mi Historial
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="logout()">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'login' ? 'active' : '' ?>" href="<?= BASE_URL ?>/login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage ?? '') === 'register' ? 'active' : '' ?>" href="<?= BASE_URL ?>/register">
                            <i class="bi bi-person-plus me-1"></i>Registrarse
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Notifications (for logged users) -->
                <?php if (isset($currentUser)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notificationBadge">
                                <span class="visually-hidden">notificaciones no leídas</span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li><h6 class="dropdown-header">Notificaciones</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notificationsList">
                                <div class="text-center text-muted p-3">
                                    <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                    No hay notificaciones
                                </div>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
function logout() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que quieres cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Cerrando sesión...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Realizar logout via API
            fetch(`${window.App.apiUrl}/auth/logout`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.App.csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `${window.App.baseUrl}/login`;
                } else {
                    Swal.fire('Error', data.message || 'Error al cerrar sesión', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        }
    });
}

// Cargar notificaciones (simulado por ahora)
document.addEventListener('DOMContentLoaded', function() {
    if (window.App.user) {
        // Aquí se cargarían las notificaciones reales
        // loadNotifications();
    }
});
</script>