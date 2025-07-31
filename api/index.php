<?php
require_once __DIR__ . '/config/config.php';

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener la URL y método
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remover la base path de la URL
$basePath = '/test-transito/api/';
$path = str_replace($basePath, '', $requestUri);
$path = parse_url($path, PHP_URL_PATH);

// Dividir la ruta en segmentos
$segments = array_filter(explode('/', $path));

// Determinar el controlador y acción
$controller = isset($segments[0]) ? $segments[0] : '';
$action = isset($segments[1]) ? $segments[1] : '';
$id = isset($segments[2]) ? $segments[2] : null;

try {
    switch ($controller) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $authController = new AuthController();
            
            switch ($action) {
                case 'register':
                    if ($requestMethod === 'POST') {
                        $authController->register();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'login':
                    if ($requestMethod === 'POST') {
                        $authController->login();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'profile':
                    if ($requestMethod === 'GET') {
                        $authController->profile();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'update-profile':
                    if ($requestMethod === 'PUT') {
                        $authController->updateProfile();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'change-password':
                    if ($requestMethod === 'PUT') {
                        $authController->changePassword();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint no encontrado']);
                    break;
            }
            break;
            
        case 'questions':
            require_once __DIR__ . '/controllers/QuestionController.php';
            $questionController = new QuestionController();
            
            switch ($action) {
                case '':
                    if ($requestMethod === 'GET') {
                        $questionController->getAll();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'random':
                    if ($requestMethod === 'GET') {
                        $questionController->getRandomQuestions();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'search':
                    if ($requestMethod === 'GET') {
                        $questionController->search();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'start-test':
                    if ($requestMethod === 'POST') {
                        $questionController->startTest();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'submit-answer':
                    if ($requestMethod === 'POST') {
                        $questionController->submitAnswer();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'finish-test':
                    if ($requestMethod === 'POST' && $id) {
                        $questionController->finishTest($id);
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'upload-image':
                    if ($requestMethod === 'POST' && $id) {
                        $questionController->uploadImage($id);
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                default:
                    if (is_numeric($action)) {
                        $id = $action;
                        switch ($requestMethod) {
                            case 'GET':
                                $questionController->getById($id);
                                break;
                            case 'PUT':
                                $questionController->update($id);
                                break;
                            case 'DELETE':
                                $questionController->delete($id);
                                break;
                            default:
                                http_response_code(405);
                                echo json_encode(['error' => 'Método no permitido']);
                                break;
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Endpoint no encontrado']);
                    }
                    break;
            }
            break;
            
        case 'categories':
            require_once __DIR__ . '/controllers/CategoryController.php';
            $categoryController = new CategoryController();
            
            switch ($action) {
                case '':
                    if ($requestMethod === 'GET') {
                        $categoryController->getAll();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'with-count':
                    if ($requestMethod === 'GET') {
                        $categoryController->getWithQuestionCount();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                default:
                    if (is_numeric($action)) {
                        $id = $action;
                        switch ($requestMethod) {
                            case 'GET':
                                $categoryController->getById($id);
                                break;
                            case 'PUT':
                                $categoryController->update($id);
                                break;
                            case 'DELETE':
                                $categoryController->delete($id);
                                break;
                            default:
                                http_response_code(405);
                                echo json_encode(['error' => 'Método no permitido']);
                                break;
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Endpoint no encontrado']);
                    }
                    break;
            }
            break;
            
        case 'sessions':
            require_once __DIR__ . '/controllers/TestSessionController.php';
            $sessionController = new TestSessionController();
            
            switch ($action) {
                case '':
                    if ($requestMethod === 'GET') {
                        $sessionController->getByUser();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'stats':
                    if ($requestMethod === 'GET') {
                        $sessionController->getStats();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'in-progress':
                    if ($requestMethod === 'GET') {
                        $sessionController->getInProgress();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'all':
                    if ($requestMethod === 'GET') {
                        $sessionController->getAll();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'stats-by-category':
                    if ($requestMethod === 'GET' && $id) {
                        $sessionController->getStatsByCategory($id);
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                default:
                    if (is_numeric($action)) {
                        $id = $action;
                        if ($requestMethod === 'GET') {
                            $sessionController->getById($id);
                        } else {
                            http_response_code(405);
                            echo json_encode(['error' => 'Método no permitido']);
                        }
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Endpoint no encontrado']);
                    }
                    break;
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Controlador no encontrado']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>