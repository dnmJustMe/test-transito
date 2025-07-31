<?php
/**
 * Historial de Tests - Página de usuario
 */

require_once '../config/config.php';

$auth = new Auth();

// Verificar que el usuario esté logueado
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$currentUser = $auth->getCurrentUser();
$currentPage = 'history';

$title = 'Historial de Tests - Test Licencia Cuba';
$description = 'Revisa tu historial de tests y progreso';

ob_start();
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="bi bi-clock-history me-2"></i>
                        Historial de Tests
                    </h2>
                    <p class="text-muted mb-0">Revisa tu progreso y resultados anteriores</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/test" class="btn btn-primary">
                        <i class="bi bi-play-circle me-2"></i>
                        Nuevo Test
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Resumidas -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-primary mb-2">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h5 class="card-title">Total Tests</h5>
                    <h3 class="text-primary mb-0" id="totalTestsCount">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-success mb-2">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h5 class="card-title">Mejor Resultado</h5>
                    <h3 class="text-success mb-0" id="bestScoreDisplay">-</h3>
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
                    <h3 class="text-info mb-0" id="averageScoreDisplay">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-4 text-warning mb-2">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5 class="card-title">Este Mes</h5>
                    <h3 class="text-warning mb-0" id="thisMonthCount">-</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Filtrar por resultado</label>
                            <select class="form-select" id="scoreFilter">
                                <option value="">Todos los resultados</option>
                                <option value="excellent">Excelente (80-100%)</option>
                                <option value="good">Bueno (60-79%)</option>
                                <option value="needs-improvement">Necesita mejorar (0-59%)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="periodFilter">
                                <option value="">Todo el tiempo</option>
                                <option value="today">Hoy</option>
                                <option value="week">Esta semana</option>
                                <option value="month">Este mes</option>
                                <option value="year">Este año</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ordenar por</label>
                            <select class="form-select" id="sortFilter">
                                <option value="date_desc">Más reciente</option>
                                <option value="date_asc">Más antiguo</option>
                                <option value="score_desc">Mejor puntuación</option>
                                <option value="score_asc">Peor puntuación</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="bi bi-funnel me-2"></i>
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Tests -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Historial Detallado
                    </h5>
                </div>
                <div class="card-body">
                    <div id="testsContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Cargando historial...</p>
                        </div>
                    </div>
                    
                    <!-- Paginación -->
                    <div id="paginationContainer" class="mt-4" style="display: none;">
                        <nav aria-label="Navegación del historial">
                            <ul class="pagination justify-content-center" id="pagination">
                                <!-- Se llenará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles del Test -->
<div class="modal fade" id="testDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-eye me-2"></i>
                    Detalles del Test
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="testDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Cargando detalles...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeHistoryPage();
});

async function initializeHistoryPage() {
    try {
        // Cargar estadísticas
        await loadUserStats();
        
        // Cargar historial inicial
        await loadTestHistory();
        
    } catch (error) {
        console.error('Error inicializando historial:', error);
        showAlert('Error cargando el historial', 'error');
    }
}

async function loadUserStats() {
    try {
        const response = await apiRequest('/api/v1/tests/stats');
        
        if (response.success && response.data) {
            const stats = response.data;
            
            document.getElementById('totalTestsCount').textContent = stats.total_tests || 0;
            document.getElementById('bestScoreDisplay').textContent = (stats.best_score || 0) + '%';
            document.getElementById('averageScoreDisplay').textContent = (stats.average_score || 0) + '%';
            document.getElementById('thisMonthCount').textContent = stats.this_month_tests || 0;
        }
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Mostrar valores por defecto
        document.getElementById('totalTestsCount').textContent = '0';
        document.getElementById('bestScoreDisplay').textContent = '0%';
        document.getElementById('averageScoreDisplay').textContent = '0%';
        document.getElementById('thisMonthCount').textContent = '0';
    }
}

async function loadTestHistory(page = 1) {
    try {
        currentPage = page;
        
        // Construir parámetros de consulta
        const params = new URLSearchParams({
            page: page,
            limit: 10,
            ...currentFilters
        });
        
        const response = await apiRequest(`/api/v1/tests/history?${params}`);
        
        if (response.success && response.data) {
            displayTestHistory(response.data.tests);
            displayPagination(response.data.pagination);
        } else {
            displayNoTests();
        }
    } catch (error) {
        console.error('Error cargando historial:', error);
        displayNoTests();
    }
}

function displayTestHistory(tests) {
    const container = document.getElementById('testsContainer');
    
    if (!tests || tests.length === 0) {
        displayNoTests();
        return;
    }
    
    const html = tests.map(test => `
        <div class="card mb-3 border-start border-4 ${getScoreBorderClass(test.score_percentage)}">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="h2 mb-1 ${getScoreTextClass(test.score_percentage)}">
                                ${test.score_percentage}%
                            </div>
                            <small class="text-muted">Puntuación</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-1">${formatDate(test.started_at)}</h6>
                        <p class="mb-1 text-muted">
                            <i class="bi bi-clock me-1"></i>
                            ${test.time_taken ? formatTime(test.time_taken) : 'N/A'}
                        </p>
                        <small class="text-muted">
                            ${test.correct_answers}/${test.total_questions} preguntas correctas
                        </small>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <span class="badge ${getScoreBadgeClass(test.score_percentage)} fs-6">
                                ${getScoreLabel(test.score_percentage)}
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar ${getScoreProgressClass(test.score_percentage)}" 
                                 style="width: ${test.score_percentage}%"></div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewTestDetails(${test.id})">
                            <i class="bi bi-eye me-1"></i>
                            Ver Detalles
                        </button>
                        <button class="btn btn-outline-success btn-sm ms-1" onclick="retakeTest(${test.id})">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Repetir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function displayNoTests() {
    const container = document.getElementById('testsContainer');
    container.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-clipboard-x display-1 text-muted mb-3"></i>
            <h5 class="text-muted">No se encontraron tests</h5>
            <p class="text-muted mb-4">
                ${Object.keys(currentFilters).length > 0 
                    ? 'No hay tests que coincidan con los filtros seleccionados.' 
                    : 'Aún no has realizado ningún test.'}
            </p>
            <a href="${window.App.baseUrl}/test" class="btn btn-primary">
                <i class="bi bi-play-circle me-2"></i>
                Realizar Primer Test
            </a>
        </div>
    `;
}

function displayPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    const paginationList = document.getElementById('pagination');
    
    if (!pagination || pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    let html = '';
    
    // Botón anterior
    if (pagination.current_page > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadTestHistory(${pagination.current_page - 1})">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
    }
    
    // Páginas
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTestHistory(${i})">${i}</a></li>`;
        }
    }
    
    // Botón siguiente
    if (pagination.current_page < pagination.total_pages) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadTestHistory(${pagination.current_page + 1})">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
    }
    
    paginationList.innerHTML = html;
}

function applyFilters() {
    currentFilters = {
        score_filter: document.getElementById('scoreFilter').value,
        period_filter: document.getElementById('periodFilter').value,
        sort: document.getElementById('sortFilter').value
    };
    
    // Remover filtros vacíos
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) {
            delete currentFilters[key];
        }
    });
    
    loadTestHistory(1);
}

async function viewTestDetails(testId) {
    try {
        const modal = new bootstrap.Modal(document.getElementById('testDetailsModal'));
        modal.show();
        
        const response = await apiRequest(`/api/v1/tests/${testId}`);
        
        if (response.success && response.data) {
            displayTestDetails(response.data);
        } else {
            throw new Error('No se pudieron cargar los detalles');
        }
    } catch (error) {
        console.error('Error cargando detalles:', error);
        document.getElementById('testDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Error cargando los detalles del test
            </div>
        `;
    }
}

function displayTestDetails(testData) {
    const content = document.getElementById('testDetailsContent');
    
    const html = `
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Información General</h6>
                <ul class="list-unstyled">
                    <li><strong>Fecha:</strong> ${formatDate(testData.started_at)}</li>
                    <li><strong>Duración:</strong> ${testData.time_taken ? formatTime(testData.time_taken) : 'N/A'}</li>
                    <li><strong>Preguntas:</strong> ${testData.total_questions}</li>
                    <li><strong>Correctas:</strong> ${testData.correct_answers}</li>
                    <li><strong>Puntuación:</strong> ${testData.score_percentage}%</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Resultado</h6>
                <div class="text-center">
                    <div class="display-4 ${getScoreTextClass(testData.score_percentage)} mb-2">
                        ${testData.score_percentage}%
                    </div>
                    <span class="badge ${getScoreBadgeClass(testData.score_percentage)} fs-6">
                        ${getScoreLabel(testData.score_percentage)}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Nota:</strong> Los detalles de respuestas específicas estarán disponibles en una futura actualización.
        </div>
    `;
    
    content.innerHTML = html;
}

function retakeTest(testId) {
    Swal.fire({
        title: '¿Repetir Test?',
        text: 'Se creará un nuevo test con preguntas similares',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, repetir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = window.App.baseUrl + '/test';
        }
    });
}

// Funciones de utilidad para estilos
function getScoreBorderClass(score) {
    if (score >= 80) return 'border-success';
    if (score >= 60) return 'border-warning';
    return 'border-danger';
}

function getScoreTextClass(score) {
    if (score >= 80) return 'text-success';
    if (score >= 60) return 'text-warning';
    return 'text-danger';
}

function getScoreBadgeClass(score) {
    if (score >= 80) return 'bg-success';
    if (score >= 60) return 'bg-warning';
    return 'bg-danger';
}

function getScoreProgressClass(score) {
    if (score >= 80) return 'bg-success';
    if (score >= 60) return 'bg-warning';
    return 'bg-danger';
}

function getScoreLabel(score) {
    if (score >= 80) return 'Excelente';
    if (score >= 60) return 'Bueno';
    return 'Necesita mejorar';
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