<?php
/**
 * API Endpoints de Tests
 */

require_once '../../config/config.php';

$middleware = new AuthMiddleware();
$middleware->handleCors();
$middleware->validateOrigin();

$testModel = new TestSession();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleTestGet();
            break;
        case 'POST':
            handleTestPost();
            break;
        case 'PUT':
            handleTestPut();
            break;
        case 'DELETE':
            handleTestDelete();
            break;
        default:
            $middleware->requireMethod(['GET', 'POST', 'PUT', 'DELETE']);
    }
} catch (Exception $e) {
    logMessage("Error en API tests: " . $e->getMessage(), 'ERROR');
    ApiResponse::serverError('Error interno del servidor');
}

function handleTestGet() {
    global $testModel, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'history':
            getUserHistory();
            break;
        case 'stats':
            getUserStats();
            break;
        case 'all':
            getAllTests();
            break;
        case 'general-stats':
            getGeneralStats();
            break;
        case 'category-stats':
            getCategoryStats();
            break;
        case 'top-scores':
            getTopScores();
            break;
        default:
            // Si hay ID, obtener test específico
            if (isset($_GET['id'])) {
                getTestById();
            } else {
                ApiResponse::notFound('Endpoint no encontrado');
            }
    }
}

function handleTestPost() {
    global $testModel, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'start':
            startTest();
            break;
        case 'submit':
            submitTest();
            break;
        case 'save-answer':
            saveAnswer();
            break;
        default:
            ApiResponse::notFound('Endpoint no encontrado');
    }
}

function handleTestPut() {
    global $testModel, $middleware;
    
    if (isset($_GET['id'])) {
        updateTest();
    } else {
        ApiResponse::notFound('ID de test requerido');
    }
}

function handleTestDelete() {
    global $testModel, $middleware;
    
    if (isset($_GET['id'])) {
        deleteTest();
    } else {
        ApiResponse::notFound('ID de test requerido');
    }
}

function startTest() {
    global $testModel, $middleware;
    
    $user = $middleware->requireAuth();
    
    ApiResponse::validateJsonRequest();
    $data = ApiResponse::getJsonInput();
    
    ApiResponse::validateRequired($data, ['total_questions']);
    
    $totalQuestions = intval($data['total_questions']);
    
    // Validar cantidad de preguntas
    if ($totalQuestions < MIN_QUESTIONS_PER_TEST || $totalQuestions > MAX_QUESTIONS_PER_TEST) {
        ApiResponse::validationError([
            'total_questions' => "La cantidad debe estar entre " . MIN_QUESTIONS_PER_TEST . " y " . MAX_QUESTIONS_PER_TEST
        ]);
    }
    
    // Crear sesión de test
    $sessionId = $testModel->create($user['id'], $totalQuestions);
    
    if ($sessionId) {
        // Obtener preguntas aleatorias
        $questionModel = new Question();
        $questions = $questionModel->getRandomQuestions(
            $totalQuestions,
            $data['category'] ?? null,
            $data['difficulty'] ?? null
        );
        
        if (empty($questions)) {
            ApiResponse::notFound('No se encontraron suficientes preguntas');
        }
        
        if (count($questions) < $totalQuestions) {
            ApiResponse::error("Solo se encontraron " . count($questions) . " preguntas de las {$totalQuestions} solicitadas");
        }
        
        $response = [
            'session_id' => $sessionId,
            'questions' => $questions,
            'total_questions' => count($questions),
            'passing_score' => DEFAULT_PASSING_SCORE
        ];
        
        ApiResponse::success($response, 'Test iniciado exitosamente', 201);
    } else {
        ApiResponse::serverError('Error al crear sesión de test');
    }
}

function saveAnswer() {
    global $testModel, $middleware;
    
    $user = $middleware->requireAuth();
    
    ApiResponse::validateJsonRequest();
    $data = ApiResponse::getJsonInput();
    
    ApiResponse::validateRequired($data, ['session_id', 'question_id', 'selected_answer']);
    
    $sessionId = intval($data['session_id']);
    $questionId = intval($data['question_id']);
    $selectedAnswer = intval($data['selected_answer']);
    $answerTime = isset($data['answer_time']) ? intval($data['answer_time']) : null;
    
    // Verificar que la sesión pertenece al usuario
    $session = $testModel->getById($sessionId);
    if (!$session || $session['user_id'] != $user['id']) {
        ApiResponse::forbidden('No tienes acceso a esta sesión de test');
    }
    
    // Obtener la pregunta para verificar respuesta correcta
    $questionModel = new Question();
    $question = $questionModel->getById($questionId);
    
    if (!$question) {
        ApiResponse::notFound('Pregunta no encontrada');
    }
    
    $isCorrect = ($selectedAnswer == $question['correct_answer']);
    
    $result = $testModel->saveAnswer($sessionId, $questionId, $selectedAnswer, $isCorrect, $answerTime);
    
    if ($result) {
        ApiResponse::success([
            'is_correct' => $isCorrect,
            'correct_answer' => $question['correct_answer']
        ], 'Respuesta guardada exitosamente');
    } else {
        ApiResponse::serverError('Error al guardar respuesta');
    }
}

function submitTest() {
    global $testModel, $middleware;
    
    $user = $middleware->requireAuth();
    
    ApiResponse::validateJsonRequest();
    $data = ApiResponse::getJsonInput();
    
    ApiResponse::validateRequired($data, ['session_id']);
    
    $sessionId = intval($data['session_id']);
    $timeTaken = isset($data['time_taken']) ? intval($data['time_taken']) : null;
    
    // Verificar que la sesión pertenece al usuario
    $session = $testModel->getById($sessionId);
    if (!$session || $session['user_id'] != $user['id']) {
        ApiResponse::forbidden('No tienes acceso a esta sesión de test');
    }
    
    // Obtener respuestas de la sesión
    $answers = $testModel->getSessionAnswers($sessionId);
    
    if (empty($answers)) {
        ApiResponse::error('No se encontraron respuestas para esta sesión');
    }
    
    // Calcular puntuación
    $correctAnswers = 0;
    foreach ($answers as $answer) {
        if ($answer['is_correct']) {
            $correctAnswers++;
        }
    }
    
    $totalQuestions = $session['total_questions'];
    $scorePercentage = round(($correctAnswers / $totalQuestions) * 100, 2);
    
    // Finalizar test
    $result = $testModel->finish($sessionId, $correctAnswers, $scorePercentage, $timeTaken);
    
    if ($result) {
        $response = [
            'session_id' => $sessionId,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percentage' => $scorePercentage,
            'passed' => $scorePercentage >= DEFAULT_PASSING_SCORE,
            'passing_score' => DEFAULT_PASSING_SCORE,
            'time_taken' => $timeTaken,
            'answers' => $answers
        ];
        
        ApiResponse::success($response, 'Test finalizado exitosamente');
    } else {
        ApiResponse::serverError('Error al finalizar test');
    }
}

function getUserHistory() {
    global $testModel, $middleware;
    
    $user = $middleware->requireAuth();
    
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    list($page, $limit) = ApiResponse::validatePagination($page, $limit);
    
    $result = $testModel->getUserHistory($user['id'], $page, $limit);
    
    if ($result === false) {
        ApiResponse::serverError('Error al obtener historial');
    }
    
    ApiResponse::paginated(
        $result['history'],
        $result['total'],
        $result['page'],
        $result['limit'],
        'Historial obtenido exitosamente'
    );
}

function getUserStats() {
    global $testModel, $middleware;
    
    $user = $middleware->requireAuth();
    
    $stats = $testModel->getUserStats($user['id']);
    
    if ($stats === false) {
        ApiResponse::serverError('Error al obtener estadísticas');
    }
    
    ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente');
}

function getTestById() {
    global $testModel, $middleware;
    
    $user = $middleware->requireAuth();
    
    $id = intval($_GET['id']);
    $test = $testModel->getById($id);
    
    if (!$test) {
        ApiResponse::notFound('Test no encontrado');
    }
    
    // Verificar permisos
    if ($test['user_id'] != $user['id'] && $user['role'] !== 'admin') {
        ApiResponse::forbidden('No tienes acceso a este test');
    }
    
    // Obtener respuestas si se solicitan
    if (isset($_GET['include_answers']) && $_GET['include_answers']) {
        $test['answers'] = $testModel->getSessionAnswers($id);
    }
    
    ApiResponse::success($test, 'Test obtenido exitosamente');
}

function getAllTests() {
    global $testModel, $middleware;
    
    $middleware->requireAdmin();
    
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    list($page, $limit) = ApiResponse::validatePagination($page, $limit);
    
    $filters = [
        'user_id' => $_GET['user_id'] ?? '',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'passed' => isset($_GET['passed']) ? (bool)$_GET['passed'] : ''
    ];
    
    $result = $testModel->getAll($page, $limit, $filters);
    
    if ($result === false) {
        ApiResponse::serverError('Error al obtener tests');
    }
    
    ApiResponse::paginated(
        $result['sessions'],
        $result['total'],
        $result['page'],
        $result['limit'],
        'Tests obtenidos exitosamente'
    );
}

function getGeneralStats() {
    global $testModel, $middleware;
    
    $middleware->requireAdmin();
    
    $stats = $testModel->getGeneralStats();
    
    if ($stats === false) {
        ApiResponse::serverError('Error al obtener estadísticas generales');
    }
    
    ApiResponse::success($stats, 'Estadísticas generales obtenidas exitosamente');
}

function getCategoryStats() {
    global $testModel, $middleware;
    
    $middleware->requireAdmin();
    
    $stats = $testModel->getStatsByCategory();
    
    if ($stats === false) {
        ApiResponse::serverError('Error al obtener estadísticas por categoría');
    }
    
    ApiResponse::success($stats, 'Estadísticas por categoría obtenidas exitosamente');
}

function getTopScores() {
    global $testModel, $middleware;
    
    $limit = intval($_GET['limit'] ?? 10);
    $scores = $testModel->getTopScores($limit);
    
    if ($scores === false) {
        ApiResponse::serverError('Error al obtener mejores puntuaciones');
    }
    
    ApiResponse::success($scores, 'Mejores puntuaciones obtenidas exitosamente');
}

function deleteTest() {
    global $testModel, $middleware;
    
    $middleware->requireAdmin();
    $middleware->requireCSRF();
    
    $id = intval($_GET['id']);
    $test = $testModel->getById($id);
    
    if (!$test) {
        ApiResponse::notFound('Test no encontrado');
    }
    
    $result = $testModel->delete($id);
    
    if ($result) {
        ApiResponse::success(null, 'Test eliminado exitosamente');
    } else {
        ApiResponse::serverError('Error al eliminar test');
    }
}