<?php
/**
 * Controlador de Sesiones de Test
 */

require_once 'models/TestSession.php';
require_once 'models/User.php';

class SessionController {
    private $sessionModel;
    private $userModel;
    
    public function __construct() {
        $this->sessionModel = new TestSession();
        $this->userModel = new User();
    }
    
    public function getByUser() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            
            $sessions = $this->sessionModel->getByUser($user['id'], $page, $limit);
            
            return $this->sendResponse(true, 'Sesiones obtenidas', $sessions);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getStats() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $stats = $this->sessionModel->getStatsByUser($user['id']);
            
            return $this->sendResponse(true, 'Estadísticas obtenidas', $stats);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getGlobalStats() {
        try {
            $stats = $this->sessionModel->getGlobalStats();
            
            return $this->sendResponse(true, 'Estadísticas globales obtenidas', $stats);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getRecentTests() {
        try {
            $limit = $_GET['limit'] ?? 10;
            $recentTests = $this->sessionModel->getRecentTests($limit);
            
            return $this->sendResponse(true, 'Tests recientes obtenidos', $recentTests);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getTopScores() {
        try {
            $limit = $_GET['limit'] ?? 10;
            $topScores = $this->sessionModel->getTopScores($limit);
            
            return $this->sendResponse(true, 'Mejores puntuaciones obtenidas', $topScores);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getSessionDetails($sessionId) {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $session = $this->sessionModel->findById($sessionId);
            if (!$session) {
                return $this->sendResponse(false, 'Sesión no encontrada', null, 404);
            }
            
            // Verificar que el usuario es dueño de la sesión o es admin
            if ($session['user_id'] != $user['id'] && $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 403);
            }
            
            $userAnswers = $this->sessionModel->getUserAnswers($sessionId);
            
            return $this->sendResponse(true, 'Detalles de sesión obtenidos', [
                'session' => $session,
                'answers' => $userAnswers
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function deleteSession($sessionId) {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $session = $this->sessionModel->findById($sessionId);
            if (!$session) {
                return $this->sendResponse(false, 'Sesión no encontrada', null, 404);
            }
            
            $this->sessionModel->delete($sessionId);
            
            return $this->sendResponse(true, 'Sesión eliminada exitosamente');
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getPublicStats() {
        try {
            $stats = $this->sessionModel->getGlobalStats();
            
            // Obtener estadísticas de usuarios
            $userStats = $this->userModel->getStats();
            
            // Obtener configuración del sistema
            $db = new Database();
            $configSql = "SELECT key_name, value FROM system_config WHERE key_name IN ('total_questions', 'passing_score')";
            $configStmt = $db->query($configSql);
            $configs = $configStmt->fetchAll();
            
            $config = [];
            foreach ($configs as $configItem) {
                $config[$configItem['key_name']] = $configItem['value'];
            }
            
            return $this->sendResponse(true, 'Estadísticas públicas obtenidas', [
                'total_tests' => $stats['total_tests'] ?? 0,
                'total_users' => $userStats['total_users'] ?? 0,
                'total_questions' => $config['total_questions'] ?? 0,
                'passing_score' => $config['passing_score'] ?? 80,
                'average_score' => round($stats['average_score'] ?? 0, 2),
                'passed_tests' => $stats['passed_tests'] ?? 0,
                'failed_tests' => $stats['failed_tests'] ?? 0
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    private function getCurrentUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }
        
        $token = $matches[1];
        $payload = $this->verifyJWT($token);
        
        if (!$payload) {
            return null;
        }
        
        return $this->userModel->findById($payload['user_id']);
    }
    
    private function verifyJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = $parts[2];
        
        $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], 'your-secret-key', true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));
        
        if ($signature !== $expectedSignature) {
            return false;
        }
        
        $payloadData = json_decode($payload, true);
        if ($payloadData['exp'] < time()) {
            return false;
        }
        
        return $payloadData;
    }
    
    private function sendResponse($success, $message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
}
?>