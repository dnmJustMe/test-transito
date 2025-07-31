<?php
/**
 * API Endpoints de Autenticación
 */

require_once '../../config/config.php';

$middleware = new AuthMiddleware();
$middleware->handleCors();
$middleware->validateOrigin();

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handleAuthPost();
            break;
        case 'GET':
            handleAuthGet();
            break;
        case 'DELETE':
            handleAuthDelete();
            break;
        default:
            $middleware->requireMethod(['POST', 'GET', 'DELETE']);
    }
} catch (Exception $e) {
    logMessage("Error en API auth: " . $e->getMessage(), 'ERROR');
    ApiResponse::serverError('Error interno del servidor');
}

function handleAuthPost() {
    global $auth, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'login':
            login();
            break;
        case 'register':
            register();
            break;
        case 'change-password':
            changePassword();
            break;
        default:
            ApiResponse::notFound('Endpoint no encontrado');
    }
}

function handleAuthGet() {
    global $auth, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'profile':
            getProfile();
            break;
        case 'csrf-token':
            getCsrfToken();
            break;
        default:
            ApiResponse::notFound('Endpoint no encontrado');
    }
}

function handleAuthDelete() {
    global $auth, $middleware;
    
    $path = $_GET['action'] ?? '';
    
    switch ($path) {
        case 'logout':
            logout();
            break;
        default:
            ApiResponse::notFound('Endpoint no encontrado');
    }
}

function login() {
    global $auth, $middleware;
    
    $middleware->rateLimit(10, 900); // 10 intentos por 15 minutos
    
    ApiResponse::validateJsonRequest();
    $data = ApiResponse::getJsonInput();
    
    ApiResponse::validateRequired($data, ['email', 'password']);
    $data = ApiResponse::sanitizeInput($data);
    
    // Validar formato de email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        ApiResponse::validationError(['email' => 'Formato de email inválido']);
    }
    
    $result = $auth->login($data['email'], $data['password'], $data['remember_me'] ?? false);
    
    if ($result['success']) {
        // Incluir token CSRF en la respuesta
        $result['csrf_token'] = generateCSRFToken();
        ApiResponse::success($result, 'Inicio de sesión exitoso');
    } else {
        ApiResponse::unauthorized($result['message']);
    }
}

function register() {
    global $auth, $middleware;
    
    $middleware->rateLimit(5, 3600); // 5 registros por hora
    
    // Verificar si el registro está habilitado
    if (!getSystemConfig('allow_registration', 1)) {
        ApiResponse::forbidden('El registro de nuevos usuarios está deshabilitado');
    }
    
    ApiResponse::validateJsonRequest();
    $data = ApiResponse::getJsonInput();
    
    ApiResponse::validateRequired($data, ['username', 'email', 'password', 'first_name', 'last_name']);
    $data = ApiResponse::sanitizeInput($data);
    
    // Validaciones adicionales
    $errors = [];
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Formato de email inválido';
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
        $errors['username'] = 'El nombre de usuario debe tener entre 3-20 caracteres alfanuméricos';
    }
    
    if (strlen($data['password']) < 6) {
        $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'Las contraseñas no coinciden';
    }
    
    if (!empty($errors)) {
        ApiResponse::validationError($errors);
    }
    
    $result = $auth->register($data);
    
    if ($result['success']) {
        ApiResponse::success([
            'user_id' => $result['user_id'],
            'message' => $result['message']
        ], 'Usuario registrado exitosamente', 201);
    } else {
        if (strpos($result['message'], 'ya están registrados') !== false) {
            ApiResponse::conflict($result['message']);
        } else {
            ApiResponse::error($result['message']);
        }
    }
}

function getProfile() {
    global $auth, $middleware;
    
    $user = $middleware->requireAuth();
    
    // Obtener estadísticas del usuario
    $userModel = new User();
    $testModel = new TestSession();
    
    $stats = $testModel->getUserStats($user['id']);
    
    $profile = [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
            'created_at' => $user['created_at']
        ],
        'stats' => $stats['stats'] ?? null,
        'recent_progress' => $stats['progress'] ?? []
    ];
    
    ApiResponse::success($profile, 'Perfil obtenido exitosamente');
}

function changePassword() {
    global $auth, $middleware;
    
    $user = $middleware->requireAuth();
    $middleware->requireCSRF();
    
    ApiResponse::validateJsonRequest();
    $data = ApiResponse::getJsonInput();
    
    ApiResponse::validateRequired($data, ['current_password', 'new_password']);
    
    if (isset($data['confirm_password']) && $data['new_password'] !== $data['confirm_password']) {
        ApiResponse::validationError(['confirm_password' => 'Las contraseñas no coinciden']);
    }
    
    $result = $auth->changePassword($user['id'], $data['current_password'], $data['new_password']);
    
    if ($result['success']) {
        ApiResponse::success(null, $result['message']);
    } else {
        ApiResponse::error($result['message']);
    }
}

function logout() {
    global $auth, $middleware;
    
    $middleware->requireAuth();
    
    $auth->logout();
    ApiResponse::success(null, 'Sesión cerrada exitosamente');
}

function getCsrfToken() {
    $token = generateCSRFToken();
    ApiResponse::success(['csrf_token' => $token], 'Token CSRF generado');
}