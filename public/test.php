<?php
/**
 * Página de Test - Simulador de Examen
 */

require_once '../config/config.php';

$auth = new Auth();

// Verificar que el usuario esté logueado
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$currentUser = $auth->getCurrentUser();
$currentPage = 'test';

$title = 'Test de Práctica - Test Licencia Cuba';
$description = 'Realiza tu test de práctica para el examen de conducir';

ob_start();
?>

<div class="container-fluid mt-4">
    <!-- Estado del Test -->
    <div id="testContainer" style="display: none;">
        <!-- Header del Test -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="mb-0">
                                    <i class="bi bi-clipboard-check me-2"></i>
                                    Test de Práctica
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" id="progressBar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Pregunta <span id="currentQuestion">1</span> de <span id="totalQuestions">20</span></small>
                                    <small class="text-muted"><span id="correctAnswers">0</span> correctas</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="d-flex align-items-center justify-content-end">
                                    <i class="bi bi-clock me-2 text-primary"></i>
                                    <span class="fw-bold" id="timer">00:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pregunta Actual -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <!-- Imagen de la pregunta -->
                        <div id="questionImage" class="text-center mb-4" style="display: none;">
                            <img id="questionImg" src="" alt="Imagen de la pregunta" class="img-fluid rounded" style="max-height: 300px;">
                        </div>

                        <!-- Texto de la pregunta -->
                        <div class="mb-4">
                            <h4 id="questionText" class="mb-3">Cargando pregunta...</h4>
                        </div>

                        <!-- Opciones de respuesta -->
                        <div id="answersContainer">
                            <!-- Se llenarán dinámicamente -->
                        </div>

                        <!-- Botones de navegación -->
                        <div class="d-flex justify-content-between mt-4">
                            <button id="prevBtn" class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-arrow-left me-2"></i>
                                Anterior
                            </button>
                            <button id="nextBtn" class="btn btn-primary" disabled>
                                Siguiente
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <button id="finishBtn" class="btn btn-success" style="display: none;">
                                <i class="bi bi-check-circle me-2"></i>
                                Finalizar Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración Inicial del Test -->
    <div id="testSetup">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-play-circle display-1 text-primary mb-3"></i>
                            <h2>Configurar Test</h2>
                            <p class="text-muted">Personaliza tu test de práctica</p>
                        </div>

                        <form id="testConfigForm">
                            <div class="mb-3">
                                <label for="questionCount" class="form-label">Número de preguntas</label>
                                <select class="form-select" id="questionCount" name="questionCount">
                                    <option value="10">10 preguntas (Rápido)</option>
                                    <option value="20" selected>20 preguntas (Estándar)</option>
                                    <option value="30">30 preguntas (Completo)</option>
                                    <option value="50">50 preguntas (Intensivo)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Categoría</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Todas las categorías</option>
                                    <option value="velocidad">Velocidad y Límites</option>
                                    <option value="señales">Señales de Tránsito</option>
                                    <option value="distancia">Distancia y Seguimiento</option>
                                    <option value="luces">Luces y Visibilidad</option>
                                    <option value="peatones">Peatones y Cruces</option>
                                    <option value="emergencia">Situaciones de Emergencia</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="difficulty" class="form-label">Dificultad</label>
                                <select class="form-select" id="difficulty" name="difficulty">
                                    <option value="">Todas las dificultades</option>
                                    <option value="easy">Fácil</option>
                                    <option value="medium" selected>Intermedio</option>
                                    <option value="hard">Difícil</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showResults" checked>
                                    <label class="form-check-label" for="showResults">
                                        Mostrar resultado después de cada pregunta
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-play-circle me-2"></i>
                                    Iniciar Test
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <a href="<?= BASE_URL ?>/dashboard" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-2"></i>
                                Volver al Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados del Test -->
    <div id="testResults" style="display: none;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div id="resultIcon" class="display-1 mb-3"></div>
                        <h2 id="resultTitle">Resultados del Test</h2>
                        <p class="text-muted mb-4">Has completado el test</p>

                        <div class="row mb-4">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="h3 text-primary mb-1" id="finalScore">0%</div>
                                    <small class="text-muted">Puntuación</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="h3 text-success mb-1" id="finalCorrect">0</div>
                                    <small class="text-muted">Correctas</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="h3 text-danger mb-1" id="finalIncorrect">0</div>
                                    <small class="text-muted">Incorrectas</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="h3 text-info mb-1" id="finalTime">0:00</div>
                                    <small class="text-muted">Tiempo</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button id="reviewBtn" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-2"></i>
                                Revisar Respuestas
                            </button>
                            <button id="newTestBtn" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-2"></i>
                                Nuevo Test
                            </button>
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-2"></i>
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales del test
let currentTest = {
    sessionId: null,
    questions: [],
    currentQuestionIndex: 0,
    answers: {},
    startTime: null,
    timer: null,
    config: {}
};

document.addEventListener('DOMContentLoaded', function() {
    initializeTestPage();
});

function initializeTestPage() {
    // Configurar formulario de configuración
    document.getElementById('testConfigForm').addEventListener('submit', startTest);
    
    // Configurar botones de navegación
    document.getElementById('prevBtn').addEventListener('click', previousQuestion);
    document.getElementById('nextBtn').addEventListener('click', nextQuestion);
    document.getElementById('finishBtn').addEventListener('click', finishTest);
    
    // Configurar botones de resultados
    document.getElementById('newTestBtn').addEventListener('click', newTest);
    document.getElementById('reviewBtn').addEventListener('click', reviewAnswers);
}

async function startTest(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    currentTest.config = Object.fromEntries(formData.entries());
    
    try {
        showLoading(true);
        
        // Iniciar sesión de test
        const response = await apiRequest('/api/v1/tests/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken
            },
            body: JSON.stringify({
                question_count: parseInt(currentTest.config.questionCount),
                category: currentTest.config.category || null,
                difficulty: currentTest.config.difficulty || null
            })
        });

        if (response.success) {
            currentTest.sessionId = response.data.session_id;
            currentTest.questions = response.data.questions;
            currentTest.startTime = Date.now();
            
            showTestInterface();
            startTimer();
            displayQuestion(0);
        } else {
            throw new Error(response.message || 'Error iniciando el test');
        }
    } catch (error) {
        console.error('Error iniciando test:', error);
        showAlert('Error al iniciar el test. Inténtalo de nuevo.', 'error');
    } finally {
        showLoading(false);
    }
}

function showTestInterface() {
    document.getElementById('testSetup').style.display = 'none';
    document.getElementById('testContainer').style.display = 'block';
    document.getElementById('totalQuestions').textContent = currentTest.questions.length;
}

function displayQuestion(index) {
    const question = currentTest.questions[index];
    if (!question) return;
    
    currentTest.currentQuestionIndex = index;
    
    // Actualizar UI
    document.getElementById('currentQuestion').textContent = index + 1;
    document.getElementById('questionText').textContent = question.question_text;
    
    // Mostrar imagen si existe
    const imageContainer = document.getElementById('questionImage');
    const img = document.getElementById('questionImg');
    if (question.image_path) {
        img.src = window.App.baseUrl + '/uploads/images/' + question.image_path;
        imageContainer.style.display = 'block';
    } else {
        imageContainer.style.display = 'none';
    }
    
    // Mostrar opciones
    displayAnswerOptions(question, index);
    
    // Actualizar progreso
    updateProgress();
    
    // Actualizar botones
    updateNavigationButtons();
}

function displayAnswerOptions(question, questionIndex) {
    const container = document.getElementById('answersContainer');
    const selectedAnswer = currentTest.answers[question.id];
    
    const options = [
        { value: 1, text: question.option_1 },
        { value: 2, text: question.option_2 },
        { value: 3, text: question.option_3 }
    ];
    
    const html = options.map(option => `
        <div class="form-check mb-3">
            <input class="form-check-input" type="radio" 
                   name="question_${question.id}" 
                   id="option_${question.id}_${option.value}" 
                   value="${option.value}"
                   ${selectedAnswer == option.value ? 'checked' : ''}
                   onchange="selectAnswer(${question.id}, ${option.value})">
            <label class="form-check-label" for="option_${question.id}_${option.value}">
                ${option.text}
            </label>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function selectAnswer(questionId, answer) {
    currentTest.answers[questionId] = answer;
    
    // Habilitar botón siguiente
    document.getElementById('nextBtn').disabled = false;
    
    // Actualizar contador de respuestas correctas (simulado)
    updateCorrectAnswersCount();
}

function updateCorrectAnswersCount() {
    // En una implementación real, esto se calcularía al final
    // Por ahora mostramos el número de respuestas dadas
    const answeredCount = Object.keys(currentTest.answers).length;
    document.getElementById('correctAnswers').textContent = answeredCount;
}

function previousQuestion() {
    if (currentTest.currentQuestionIndex > 0) {
        displayQuestion(currentTest.currentQuestionIndex - 1);
    }
}

function nextQuestion() {
    if (currentTest.currentQuestionIndex < currentTest.questions.length - 1) {
        displayQuestion(currentTest.currentQuestionIndex + 1);
    }
}

function updateProgress() {
    const progress = ((currentTest.currentQuestionIndex + 1) / currentTest.questions.length) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const finishBtn = document.getElementById('finishBtn');
    
    prevBtn.disabled = currentTest.currentQuestionIndex === 0;
    
    const currentQuestion = currentTest.questions[currentTest.currentQuestionIndex];
    const hasAnswer = currentTest.answers[currentQuestion.id];
    
    if (currentTest.currentQuestionIndex === currentTest.questions.length - 1) {
        nextBtn.style.display = 'none';
        finishBtn.style.display = 'inline-block';
        finishBtn.disabled = !hasAnswer;
    } else {
        nextBtn.style.display = 'inline-block';
        finishBtn.style.display = 'none';
        nextBtn.disabled = !hasAnswer;
    }
}

function startTimer() {
    currentTest.timer = setInterval(() => {
        const elapsed = Math.floor((Date.now() - currentTest.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        
        document.getElementById('timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
}

async function finishTest() {
    try {
        showLoading(true);
        clearInterval(currentTest.timer);
        
        const totalTime = Math.floor((Date.now() - currentTest.startTime) / 1000);
        
        // Enviar respuestas al servidor
        const response = await apiRequest('/api/v1/tests/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.App.csrfToken
            },
            body: JSON.stringify({
                session_id: currentTest.sessionId,
                answers: currentTest.answers,
                time_taken: totalTime
            })
        });

        if (response.success) {
            showResults(response.data);
        } else {
            throw new Error(response.message || 'Error enviando respuestas');
        }
    } catch (error) {
        console.error('Error finalizando test:', error);
        showAlert('Error al finalizar el test. Inténtalo de nuevo.', 'error');
    } finally {
        showLoading(false);
    }
}

function showResults(results) {
    document.getElementById('testContainer').style.display = 'none';
    document.getElementById('testResults').style.display = 'block';
    
    // Mostrar estadísticas
    document.getElementById('finalScore').textContent = results.score_percentage + '%';
    document.getElementById('finalCorrect').textContent = results.correct_answers;
    document.getElementById('finalIncorrect').textContent = results.total_questions - results.correct_answers;
    document.getElementById('finalTime').textContent = formatTime(results.time_taken);
    
    // Mostrar icono según resultado
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    
    if (results.score_percentage >= 80) {
        icon.innerHTML = '<i class="bi bi-trophy text-success"></i>';
        title.textContent = '¡Excelente! Has aprobado';
        title.className = 'text-success';
    } else if (results.score_percentage >= 60) {
        icon.innerHTML = '<i class="bi bi-emoji-neutral text-warning"></i>';
        title.textContent = 'Bien, pero puedes mejorar';
        title.className = 'text-warning';
    } else {
        icon.innerHTML = '<i class="bi bi-emoji-frown text-danger"></i>';
        title.textContent = 'Necesitas más práctica';
        title.className = 'text-danger';
    }
}

function newTest() {
    // Resetear variables
    currentTest = {
        sessionId: null,
        questions: [],
        currentQuestionIndex: 0,
        answers: {},
        startTime: null,
        timer: null,
        config: {}
    };
    
    // Mostrar configuración inicial
    document.getElementById('testResults').style.display = 'none';
    document.getElementById('testSetup').style.display = 'block';
}

function reviewAnswers() {
    // Implementar revisión de respuestas
    showAlert('Función de revisión en desarrollo', 'info');
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