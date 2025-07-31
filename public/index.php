<?php
/**
 * Página Principal - Redirección según estado de autenticación
 */

require_once '../config/config.php';

$auth = new Auth();

// Si el usuario está logueado, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

// Si no está logueado, mostrar página de bienvenida
$currentUser = null;
$currentPage = 'home';

$title = 'Test Licencia Cuba - Simulador Oficial';
$description = 'Simulador oficial del examen teórico para licencia de conducir en Cuba. Practica con preguntas reales y evalúa tus conocimientos.';

ob_start();
?>

<div class="app-container">
    <div class="app-box animate__animated animate__fadeIn text-center">
        <!-- Logo -->
        <div class="mb-4">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo Test Licencia Cuba" class="mb-3" style="max-height: 120px;">
            <h1 class="text-gradient mb-2">Test de Licencia en Cuba</h1>
            <p class="text-muted mb-4">Simulador oficial de preguntas del examen teórico</p>
        </div>

        <!-- Características principales -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="p-3">
                    <i class="bi bi-question-circle-fill text-primary fs-1 mb-2"></i>
                    <h6 class="fw-bold">Preguntas Oficiales</h6>
                    <small class="text-muted">Banco actualizado de preguntas reales del examen</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3">
                    <i class="bi bi-graph-up text-success fs-1 mb-2"></i>
                    <h6 class="fw-bold">Seguimiento</h6>
                    <small class="text-muted">Monitorea tu progreso y mejora continua</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3">
                    <i class="bi bi-award-fill text-warning fs-1 mb-2"></i>
                    <h6 class="fw-bold">Certificación</h6>
                    <small class="text-muted">Prepárate para aprobar el examen oficial</small>
                </div>
            </div>
        </div>

        <!-- Estadísticas del sistema -->
        <div class="row mb-4">
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <i class="bi bi-people text-primary fs-4"></i>
                    <div class="stats-number" id="totalUsers">-</div>
                    <div class="stats-label">Usuarios</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <i class="bi bi-clipboard-check text-success fs-4"></i>
                    <div class="stats-number" id="totalTests">-</div>
                    <div class="stats-label">Tests</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <i class="bi bi-question-circle text-info fs-4"></i>
                    <div class="stats-number" id="totalQuestions">-</div>
                    <div class="stats-label">Preguntas</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <i class="bi bi-trophy text-warning fs-4"></i>
                    <div class="stats-number" id="avgScore">-</div>
                    <div class="stats-label">Promedio</div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-grid gap-3">
            <a href="<?= BASE_URL ?>/register" class="btn btn-main btn-lg">
                <i class="bi bi-person-plus me-2"></i>Comenzar Ahora - ¡Es Gratis!
            </a>
            <a href="<?= BASE_URL ?>/login" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ya tengo cuenta
            </a>
        </div>

        <!-- Información adicional -->
        <div class="mt-4 pt-4 border-top">
            <div class="row">
                <div class="col-md-6 text-start">
                    <h6 class="fw-bold mb-2">¿Por qué elegirnos?</h6>
                    <ul class="list-unstyled small text-muted">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Interfaz moderna y fácil de usar</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Preguntas actualizadas constantemente</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Estadísticas detalladas de progreso</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Acceso desde cualquier dispositivo</li>
                    </ul>
                </div>
                <div class="col-md-6 text-start">
                    <h6 class="fw-bold mb-2">Modalidades de Test</h6>
                    <ul class="list-unstyled small text-muted">
                        <li><i class="bi bi-lightning text-primary me-2"></i>Test rápido (20 preguntas)</li>
                        <li><i class="bi bi-speedometer text-info me-2"></i>Test estándar (40 preguntas)</li>
                        <li><i class="bi bi-trophy text-warning me-2"></i>Test completo (100 preguntas)</li>
                        <li><i class="bi bi-funnel text-secondary me-2"></i>Filtros por categoría</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar estadísticas del sistema
    loadPublicStats();
});

async function loadPublicStats() {
    try {
        // Simular carga de estadísticas públicas
        // En producción, esto sería una llamada a la API
        setTimeout(() => {
            document.getElementById('totalUsers').textContent = '1,234';
            document.getElementById('totalTests').textContent = '5,678';
            document.getElementById('totalQuestions').textContent = '150';
            document.getElementById('avgScore').textContent = '78%';
        }, 1000);
        
        // Animar números
        animateNumbers();
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function animateNumbers() {
    const numbers = document.querySelectorAll('.stats-number');
    numbers.forEach((number, index) => {
        setTimeout(() => {
            number.classList.add('animate__animated', 'animate__bounceIn');
        }, index * 200);
    });
}
</script>

<?php
$content = ob_get_clean();
include '../templates/base.php';
?>