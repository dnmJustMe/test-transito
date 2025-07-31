<?php
/**
 * API Endpoints de Preguntas
 */

require_once '../../config/config.php';

$middleware = new AuthMiddleware();
$middleware->handleCors();
$middleware->validateOrigin();

$questionModel = new Question();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleQuestionGet();
            break;
        case 'POST':
            handleQuestionPost();
            break;
        case 'PUT':
            handleQuestionPut();
            break;
        case 'DELETE':
            handleQuestionDelete();
            break;
        default:
            $middleware->requireMethod(['GET', 'POST', 'PUT', 'DELETE']);
    }
} catch (Exception $e) {
    logMessage("Error en API questions: " . $e->getMessage(), 'ERROR');
    ApiResponse::serverError('Error interno del servidor');
}

function handleQuestionGet() {
    global $questionModel, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'list':
            getQuestions();
            break;
        case 'random':
            getRandomQuestions();
            break;
        case 'categories':
            getCategories();
            break;
        case 'stats':
            getQuestionStats();
            break;
        case 'failed':
            getMostFailedQuestions();
            break;
        default:
            // Si hay ID, obtener pregunta específica
            if (isset($_GET['id'])) {
                getQuestionById();
            } else {
                ApiResponse::notFound('Endpoint no encontrado');
            }
    }
}

function handleQuestionPost() {
    global $questionModel, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'create':
            createQuestion();
            break;
        default:
            ApiResponse::notFound('Endpoint no encontrado');
    }
}

function handleQuestionPut() {
    global $questionModel, $middleware;
    
    if (isset($_GET['id'])) {
        updateQuestion();
    } else {
        ApiResponse::notFound('ID de pregunta requerido');
    }
}

function handleQuestionDelete() {
    global $questionModel, $middleware;
    
    if (isset($_GET['id'])) {
        deleteQuestion();
    } else {
        ApiResponse::notFound('ID de pregunta requerido');
    }
}

function getQuestions() {
    global $questionModel, $middleware;
    
    $middleware->requireAdmin();
    
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 20;
    list($page, $limit) = ApiResponse::validatePagination($page, $limit);
    
    $filters = [
        'search' => $_GET['search'] ?? '',
        'category' => $_GET['category'] ?? '',
        'difficulty' => $_GET['difficulty'] ?? ''
    ];
    
    $result = $questionModel->getAll($page, $limit, $filters);
    
    if ($result === false) {
        ApiResponse::serverError('Error al obtener preguntas');
    }
    
    ApiResponse::paginated(
        $result['questions'],
        $result['total'],
        $result['page'],
        $result['limit'],
        'Preguntas obtenidas exitosamente'
    );
}

function getRandomQuestions() {
    global $questionModel, $middleware;
    
    $middleware->requireAuth();
    
    $count = intval($_GET['count'] ?? 20);
    $category = $_GET['category'] ?? null;
    $difficulty = $_GET['difficulty'] ?? null;
    
    // Validar cantidad
    if ($count < MIN_QUESTIONS_PER_TEST || $count > MAX_QUESTIONS_PER_TEST) {
        ApiResponse::validationError([
            'count' => "La cantidad debe estar entre " . MIN_QUESTIONS_PER_TEST . " y " . MAX_QUESTIONS_PER_TEST
        ]);
    }
    
    $questions = $questionModel->getRandomQuestions($count, $category, $difficulty);
    
    if ($questions === false) {
        ApiResponse::serverError('Error al obtener preguntas aleatorias');
    }
    
    if (empty($questions)) {
        ApiResponse::notFound('No se encontraron preguntas con los criterios especificados');
    }
    
    if (count($questions) < $count) {
        ApiResponse::error("Solo se encontraron " . count($questions) . " preguntas de las {$count} solicitadas");
    }
    
    ApiResponse::success($questions, 'Preguntas aleatorias obtenidas exitosamente');
}

function getQuestionById() {
    global $questionModel, $middleware;
    
    $middleware->requireAdmin();
    
    $id = intval($_GET['id']);
    $question = $questionModel->getById($id);
    
    if (!$question) {
        ApiResponse::notFound('Pregunta no encontrada');
    }
    
    ApiResponse::success($question, 'Pregunta obtenida exitosamente');
}

function createQuestion() {
    global $questionModel, $middleware;
    
    $user = $middleware->requireAdmin();
    $middleware->requireCSRF();
    
    $data = ApiResponse::getJsonInput();
    
    // Validar datos
    $validation = $questionModel->validateQuestionData($data);
    if (!$validation['valid']) {
        ApiResponse::validationError($validation['errors']);
    }
    
    $data = ApiResponse::sanitizeInput($data);
    $data['created_by'] = $user['id'];
    
    // Manejar subida de imagen si está presente
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = handleImageUpload($_FILES['image']);
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }
    }
    
    $questionId = $questionModel->create($data);
    
    if ($questionId) {
        $question = $questionModel->getById($questionId);
        ApiResponse::success($question, 'Pregunta creada exitosamente', 201);
    } else {
        ApiResponse::serverError('Error al crear pregunta');
    }
}

function updateQuestion() {
    global $questionModel, $middleware;
    
    $middleware->requireAdmin();
    $middleware->requireCSRF();
    
    $id = intval($_GET['id']);
    $question = $questionModel->getById($id);
    
    if (!$question) {
        ApiResponse::notFound('Pregunta no encontrada');
    }
    
    $data = ApiResponse::getJsonInput();
    
    // Validar datos si se proporcionan
    if (!empty($data)) {
        $validation = $questionModel->validateQuestionData($data);
        if (!$validation['valid']) {
            ApiResponse::validationError($validation['errors']);
        }
    }
    
    $data = ApiResponse::sanitizeInput($data);
    
    // Manejar subida de imagen si está presente
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Eliminar imagen anterior si existe
        if ($question['image_path'] && file_exists(IMAGES_PATH . '/' . $question['image_path'])) {
            unlink(IMAGES_PATH . '/' . $question['image_path']);
        }
        
        $imagePath = handleImageUpload($_FILES['image']);
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }
    }
    
    $result = $questionModel->update($id, $data);
    
    if ($result) {
        $updatedQuestion = $questionModel->getById($id);
        ApiResponse::success($updatedQuestion, 'Pregunta actualizada exitosamente');
    } else {
        ApiResponse::serverError('Error al actualizar pregunta');
    }
}

function deleteQuestion() {
    global $questionModel, $middleware;
    
    $middleware->requireAdmin();
    $middleware->requireCSRF();
    
    $id = intval($_GET['id']);
    $question = $questionModel->getById($id);
    
    if (!$question) {
        ApiResponse::notFound('Pregunta no encontrada');
    }
    
    $result = $questionModel->delete($id);
    
    if ($result) {
        ApiResponse::success(null, 'Pregunta eliminada exitosamente');
    } else {
        ApiResponse::serverError('Error al eliminar pregunta');
    }
}

function getCategories() {
    global $questionModel, $middleware;
    
    $categories = $questionModel->getCategories();
    
    if ($categories === false) {
        ApiResponse::serverError('Error al obtener categorías');
    }
    
    ApiResponse::success($categories, 'Categorías obtenidas exitosamente');
}

function getQuestionStats() {
    global $questionModel, $middleware;
    
    $middleware->requireAdmin();
    
    $stats = $questionModel->getStats();
    
    if ($stats === false) {
        ApiResponse::serverError('Error al obtener estadísticas');
    }
    
    ApiResponse::success($stats, 'Estadísticas obtenidas exitosamente');
}

function getMostFailedQuestions() {
    global $questionModel, $middleware;
    
    $middleware->requireAdmin();
    
    $limit = intval($_GET['limit'] ?? 10);
    $questions = $questionModel->getMostFailedQuestions($limit);
    
    if ($questions === false) {
        ApiResponse::serverError('Error al obtener preguntas más falladas');
    }
    
    ApiResponse::success($questions, 'Preguntas más falladas obtenidas exitosamente');
}

function handleImageUpload($file) {
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        ApiResponse::validationError(['image' => 'Tipo de archivo no permitido']);
    }
    
    // Validar tamaño
    if ($file['size'] > MAX_FILE_SIZE) {
        ApiResponse::validationError(['image' => 'El archivo es demasiado grande']);
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('question_') . '.' . $extension;
    $filepath = IMAGES_PATH . '/' . $filename;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}