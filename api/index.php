<?php
/**
 * API Principal - Enrutador
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Obtener la URL y método
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remover la base path
$basePath = '/test-transito/api/';
$path = str_replace($basePath, '', $requestUri);
$path = strtok($path, '?'); // Remover query string

// Dividir la ruta en partes
$pathParts = explode('/', trim($path, '/'));
$controller = $pathParts[0] ?? '';
$action = $pathParts[1] ?? '';
$id = $pathParts[2] ?? null;

try {
    switch ($controller) {
        case 'auth':
            require_once 'controllers/AuthController.php';
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
                    } elseif ($requestMethod === 'PUT') {
                        $authController->updateProfile();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'lives':
                    if ($requestMethod === 'GET') {
                        $authController->getLives();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'add-lives':
                    if ($requestMethod === 'POST') {
                        $authController->addLives();
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
            require_once 'controllers/QuestionController.php';
            $questionController = new QuestionController();
            
            switch ($action) {
                case '':
                    if ($requestMethod === 'GET') {
                        $questionController->getAll();
                    } elseif ($requestMethod === 'POST') {
                        $questionController->create();
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
                    
                case 'finish-test':
                    if ($requestMethod === 'POST') {
                        $questionController->finishTest();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'stats':
                    if ($requestMethod === 'GET') {
                        $questionController->getStats();
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
                    
                case 'upload-image':
                    if ($requestMethod === 'POST' && $id) {
                        $questionController->uploadImage($id);
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'delete-image':
                    if ($requestMethod === 'DELETE' && $id) {
                        $questionController->deleteImage($id);
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                default:
                    if (is_numeric($action)) {
                        $id = $action;
                        if ($requestMethod === 'GET') {
                            $questionController->getById($id);
                        } elseif ($requestMethod === 'PUT') {
                            $questionController->update($id);
                        } elseif ($requestMethod === 'DELETE') {
                            $questionController->delete($id);
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
            
        case 'sessions':
            require_once 'controllers/SessionController.php';
            $sessionController = new SessionController();
            
            switch ($action) {
                case 'by-user':
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
                    
                case 'global-stats':
                    if ($requestMethod === 'GET') {
                        $sessionController->getGlobalStats();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'recent':
                    if ($requestMethod === 'GET') {
                        $sessionController->getRecentTests();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'top-scores':
                    if ($requestMethod === 'GET') {
                        $sessionController->getTopScores();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'public-stats':
                    if ($requestMethod === 'GET') {
                        $sessionController->getPublicStats();
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                default:
                    if (is_numeric($action)) {
                        $sessionId = $action;
                        if ($requestMethod === 'GET') {
                            $sessionController->getSessionDetails($sessionId);
                        } elseif ($requestMethod === 'DELETE') {
                            $sessionController->deleteSession($sessionId);
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
            
        case 'admin':
            require_once 'controllers/AuthController.php';
            require_once 'controllers/QuestionController.php';
            require_once 'controllers/SessionController.php';
            
            $authController = new AuthController();
            $questionController = new QuestionController();
            $sessionController = new SessionController();
            
            switch ($action) {
                case 'users':
                    if ($requestMethod === 'GET') {
                        // Obtener todos los usuarios (solo admin)
                        $user = $authController->getCurrentUser();
                        if (!$user || $user['role'] !== 'admin') {
                            http_response_code(401);
                            echo json_encode(['error' => 'No autorizado']);
                            exit;
                        }
                        
                        require_once 'models/User.php';
                        $userModel = new User();
                        $users = $userModel->getAll();
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $users
                        ]);
                    } else {
                        http_response_code(405);
                        echo json_encode(['error' => 'Método no permitido']);
                    }
                    break;
                    
                case 'stats':
                    if ($requestMethod === 'GET') {
                        $sessionController->getGlobalStats();
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