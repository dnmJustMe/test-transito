<?php
/**
 * Dashboard Principal - Panel de Usuario
 */

require_once '../config/config.php';

$auth = new Auth();

// Verificar que el usuario esté logueado
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$currentUser = $auth->getCurrentUser();
$currentPage = 'dashboard';

$title = 'Dashboard - Test Licencia Cuba';
$description = 'Panel principal de usuario - Test Licencia Cuba';

ob_start();
?>

<div class="container-fluid mt-4">
    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="bi bi-emoji-smile me-2"></i>
                                ¡Bienvenido, <?= htmlspecialchars($currentUser['first_name']) ?>!
                            </h2>
                            <p class="mb-0 opacity-75">
                                Continúa practicando para tu examen de conducir
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>/test" class="btn btn-light">
                                    <i class="bi bi-play-circle me-2"></i>
                                    Iniciar Test
                                </a>
                                <a href="<?= BASE_URL ?>/history" class="btn btn-outline-light">
                                    <i class="bi bi-clock-history me-2"></i>
                                    Historial
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h5 class="card-title">Tests Realizados</h5>
                    <h3 class="text-primary mb-0" id="totalTests">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h5 class="card-title">Mejor Puntuación</h5>
                    <h3 class="text-success mb-0" id="bestScore">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-info mb-2">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5 class="card-title">Promedio</h5>
                    <h3 class="text-info mb-0" id="averageScore">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-2">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <h5 class="card-title">Racha Actual</h5>
                    <h3 class="text-warning mb-0" id="currentStreak">-</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="row">
        <!-- Tests Recientes -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Tests Recientes
                        </h5>
                        <a href="<?= BASE_URL ?>/history" class="btn btn-sm btn-outline-primary">
                            Ver todos
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="recentTests">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Cargando tests recientes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Progreso -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        Tu Progreso
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Conocimiento General</span>
                            <span class="small" id="progressGeneral">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" id="progressBarGeneral" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Señales de Tránsito</span>
                            <span class="small" id="progressSignals">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" id="progressBarSignals" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Velocidad y Distancia</span>
                            <span class="small" id="progressSpeed">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" id="progressBarSpeed" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/test" class="btn btn-primary">
                            <i class="bi bi-play-circle me-2"></i>
                            Test Completo
                        </a>
                        <a href="<?= BASE_URL ?>/test?mode=quick" class="btn btn-outline-primary">
                            <i class="bi bi-lightning me-2"></i>
                            Test Rápido (10 preguntas)
                        </a>
                        <a href="<?= BASE_URL ?>/test?mode=practice" class="btn btn-outline-success">
                            <i class="bi bi-book me-2"></i>
                            Modo Práctica
                        </a>
                        <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i>
                            Mi Perfil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tip del Día -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Tip del Día
                    </h5>
                </div>
                <div class="card-body">
                    <div id="dailyTip">
                        <p class="small text-muted">Cargando tip...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

async function initializeDashboard() {
    try {
        // Cargar estadísticas del usuario
        await loadUserStats();
        
        // Cargar tests recientes
        await loadRecentTests();
        
        // Cargar tip del día
        loadDailyTip();
        
    } catch (error) {
        console.error('Error inicializando dashboard:', error);
        showAlert('Error cargando datos del dashboard', 'error');
    }
}

async function loadUserStats() {
    try {
        const response = await apiRequest('/api/v1/tests/stats');
        
        if (response.success && response.data) {
            const stats = response.data;
            
            document.getElementById('totalTests').textContent = stats.total_tests || 0;
            document.getElementById('bestScore').textContent = (stats.best_score || 0) + '%';
            document.getElementById('averageScore').textContent = (stats.average_score || 0) + '%';
            document.getElementById('currentStreak').textContent = stats.current_streak || 0;
            
            // Actualizar barras de progreso por categoría
            if (stats.category_progress) {
                updateCategoryProgress(stats.category_progress);
            }
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Mostrar valores por defecto
        document.getElementById('totalTests').textContent = '0';
        document.getElementById('bestScore').textContent = '0%';
        document.getElementById('averageScore').textContent = '0%';
        document.getElementById('currentStreak').textContent = '0';
    }
}

async function loadRecentTests() {
    try {
        const response = await apiRequest('/api/v1/tests/history?limit=5');
        
        if (response.success && response.data && response.data.tests) {
            displayRecentTests(response.data.tests);
        } else {
            displayNoTests();
        }
    } catch (error) {
        console.error('Error cargando tests recientes:', error);
        displayNoTests();
    }
}

function displayRecentTests(tests) {
    const container = document.getElementById('recentTests');
    
    if (tests.length === 0) {
        displayNoTests();
        return;
    }
    
    const html = tests.map(test => `
        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
            <div>
                <h6 class="mb-1">${formatDate(test.started_at)}</h6>
                <small class="text-muted">
                    ${test.correct_answers}/${test.total_questions} preguntas correctas
                </small>
            </div>
            <div class="text-end">
                <span class="badge ${getScoreBadgeClass(test.score_percentage)} fs-6">
                    ${test.score_percentage}%
                </span>
                <br>
                <small class="text-muted">${test.time_taken ? formatTime(test.time_taken) : 'N/A'}</small>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function displayNoTests() {
    const container = document.getElementById('recentTests');
    container.innerHTML = `
        <div class="text-center py-4">
            <i class="bi bi-clipboard-x display-4 text-muted mb-3"></i>
            <h6 class="text-muted">No has realizado tests aún</h6>
            <p class="small text-muted mb-3">¡Comienza tu primer test ahora!</p>
            <a href="${window.App.baseUrl}/test" class="btn btn-primary">
                <i class="bi bi-play-circle me-2"></i>
                Iniciar Test
            </a>
        </div>
    `;
}

function updateCategoryProgress(categoryProgress) {
    // Simular progreso por categorías (en una implementación real vendría del backend)
    const categories = {
        'general': { element: 'progressGeneral', bar: 'progressBarGeneral' },
        'señales': { element: 'progressSignals', bar: 'progressBarSignals' },
        'velocidad': { element: 'progressSpeed', bar: 'progressBarSpeed' }
    };
    
    // Valores simulados para demostración
    const progress = {
        'general': Math.floor(Math.random() * 100),
        'señales': Math.floor(Math.random() * 100),
        'velocidad': Math.floor(Math.random() * 100)
    };
    
    Object.keys(categories).forEach(key => {
        const value = progress[key] || 0;
        document.getElementById(categories[key].element).textContent = value + '%';
        document.getElementById(categories[key].bar).style.width = value + '%';
    });
}

function loadDailyTip() {
    const tips = [
        "Recuerda siempre usar el cinturón de seguridad, es tu mejor protección.",
        "La velocidad máxima en zonas urbanas es de 50 km/h.",
        "Mantén una distancia segura de al menos 3 metros con el vehículo de adelante.",
        "Las luces altas solo deben usarse en carreteras oscuras sin tráfico.",
        "Siempre cede el paso a los peatones en los cruces.",
        "Revisa tus espejos cada 5-8 segundos mientras conduces.",
        "En caso de lluvia, reduce la velocidad y aumenta la distancia de seguimiento."
    ];
    
    const randomTip = tips[Math.floor(Math.random() * tips.length)];
    document.getElementById('dailyTip').innerHTML = `
        <p class="small mb-0">${randomTip}</p>
    `;
}

function getScoreBadgeClass(score) {
    if (score >= 80) return 'bg-success';
    if (score >= 60) return 'bg-warning';
    return 'bg-danger';
}

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}
</script>

<?php
$content = ob_get_clean();
include '../templates/base.php';
?>