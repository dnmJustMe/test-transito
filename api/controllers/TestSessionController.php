<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/JWT.php';
require_once __DIR__ . '/../models/TestSession.php';
require_once __DIR__ . '/../models/UserAnswer.php';

class TestSessionController {
    private $testSession;
    private $userAnswer;
    
    public function __construct() {
        $this->testSession = new TestSession();
        $this->userAnswer = new UserAnswer();
    }
    
    public function getByUser() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $sessions = $this->testSession->getByUser($user['user_id'], $page, $limit);
            $total = $this->testSession->countByUser($user['user_id']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $sessions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getById($id) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $session = $this->testSession->findById($id);
            
            if (!$session) {
                http_response_code(404);
                echo json_encode(['error' => 'Sesión no encontrada']);
                return;
            }
            
            // Verificar que el usuario sea el propietario o admin
            if ($session['user_id'] != $user['user_id'] && $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            // Obtener respuestas detalladas
            $answers = $this->userAnswer->getBySession($id);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'session' => $session,
                    'answers' => $answers
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getStats() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $stats = $this->testSession->getStats($user['user_id']);
            $answerStats = $this->userAnswer->getStatsByUser($user['user_id']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'test_stats' => $stats,
                    'answer_stats' => $answerStats
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getInProgress() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $session = $this->testSession->getInProgress($user['user_id']);
            
            if (!$session) {
                http_response_code(404);
                echo json_encode(['error' => 'No hay sesión en progreso']);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $session
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getAll() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $sessions = $this->testSession->getAll($page, $limit);
            $total = $this->testSession->count();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $sessions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getStatsByCategory($categoryId) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $stats = $this->userAnswer->getByCategory($user['user_id'], $categoryId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
}
?>