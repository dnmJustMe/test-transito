<?php
/**
 * Controlador de Preguntas
 */

require_once 'api/models/Question.php';
require_once 'api/models/User.php';
require_once 'api/models/TestSession.php';

class QuestionController {
    private $questionModel;
    private $userModel;
    private $sessionModel;
    
    public function __construct() {
        $this->questionModel = new Question();
        $this->userModel = new User();
        $this->sessionModel = new TestSession();
    }
    
    public function getAll() {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            
            $questions = $this->questionModel->getAll($page, $limit);
            $total = $this->questionModel->count();
            
            return $this->sendResponse(true, 'Preguntas obtenidas', [
                'questions' => $questions,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $question = $this->questionModel->findById($id);
            
            if (!$question) {
                return $this->sendResponse(false, 'Pregunta no encontrada', null, 404);
            }
            
            return $this->sendResponse(true, 'Pregunta obtenida', $question);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function create() {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['question_text']) || !isset($input['answer1']) || 
                !isset($input['answer2']) || !isset($input['answer3']) || 
                !isset($input['correct_answer'])) {
                return $this->sendResponse(false, 'Todos los campos son requeridos');
            }
            
            if (!in_array($input['correct_answer'], [1, 2, 3])) {
                return $this->sendResponse(false, 'La respuesta correcta debe ser 1, 2 o 3');
            }
            
            $questionId = $this->questionModel->create($input);
            
            if ($questionId) {
                $question = $this->questionModel->findById($questionId);
                return $this->sendResponse(true, 'Pregunta creada exitosamente', $question);
            } else {
                return $this->sendResponse(false, 'Error al crear pregunta');
            }
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['question_text']) || !isset($input['answer1']) || 
                !isset($input['answer2']) || !isset($input['answer3']) || 
                !isset($input['correct_answer'])) {
                return $this->sendResponse(false, 'Todos los campos son requeridos');
            }
            
            if (!in_array($input['correct_answer'], [1, 2, 3])) {
                return $this->sendResponse(false, 'La respuesta correcta debe ser 1, 2 o 3');
            }
            
            $this->questionModel->update($id, $input);
            $question = $this->questionModel->findById($id);
            
            return $this->sendResponse(true, 'Pregunta actualizada exitosamente', $question);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $this->questionModel->delete($id);
            return $this->sendResponse(true, 'Pregunta eliminada exitosamente');
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function startTest() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            // Verificar si puede hacer test
            if (!$this->userModel->canTakeTest($user['id'])) {
                return $this->sendResponse(false, 'No tienes vidas disponibles. Espera 5 minutos para regenerar.', null, 403);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $difficulty = $input['difficulty'] ?? 'easy';
            
            // Obtener configuración según dificultad
            $configKey = $difficulty . '_questions';
            $configSql = "SELECT value FROM system_config WHERE key_name = ?";
            $db = new Database();
            $configStmt = $db->query($configSql, [$configKey]);
            $questionCount = $configStmt->fetch()['value'] ?? 20;
            
            // Obtener preguntas aleatorias
            $questions = $this->questionModel->getRandomQuestions($questionCount);
            
            if (count($questions) < $questionCount) {
                return $this->sendResponse(false, 'No hay suficientes preguntas disponibles');
            }
            
            return $this->sendResponse(true, 'Test iniciado', [
                'questions' => $questions,
                'difficulty' => $difficulty,
                'question_count' => $questionCount
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function finishTest() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['difficulty']) || !isset($input['answers'])) {
                return $this->sendResponse(false, 'Datos incompletos');
            }
            
            // Calcular resultados
            $correctAnswers = 0;
            $totalQuestions = count($input['answers']);
            $userAnswers = [];
            
            foreach ($input['answers'] as $answer) {
                $question = $this->questionModel->findById($answer['question_id']);
                $isCorrect = ($answer['user_answer'] == $question['correct_answer']);
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                $userAnswers[] = [
                    'question_id' => $answer['question_id'],
                    'user_answer' => $answer['user_answer'],
                    'is_correct' => $isCorrect
                ];
            }
            
            $score = round(($correctAnswers / $totalQuestions) * 100, 2);
            
            // Obtener puntuación mínima para aprobar
            $configSql = "SELECT value FROM system_config WHERE key_name = 'passing_score'";
            $db = new Database();
            $configStmt = $db->query($configSql);
            $passingScore = $configStmt->fetch()['value'] ?? 80;
            
            $passed = $score >= $passingScore;
            
            // Crear sesión de test
            $sessionData = [
                'user_id' => $user['id'],
                'difficulty' => $input['difficulty'],
                'question_count' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'score' => $score,
                'passed' => $passed
            ];
            
            $sessionId = $this->sessionModel->create($sessionData);
            
            // Guardar respuestas del usuario
            $this->sessionModel->saveUserAnswers($sessionId, $userAnswers);
            
            // Si no aprobó, perder una vida
            if (!$passed) {
                $this->userModel->loseLife($user['id']);
            }
            
            return $this->sendResponse(true, 'Test completado', [
                'session_id' => $sessionId,
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'passed' => $passed,
                'lives_lost' => !$passed ? 1 : 0
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function uploadImage($id) {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            if (!isset($_FILES['image'])) {
                return $this->sendResponse(false, 'No se proporcionó imagen');
            }
            
            $imageFile = $_FILES['image'];
            
            // Validar archivo
            if ($imageFile['error'] !== UPLOAD_ERR_OK) {
                return $this->sendResponse(false, 'Error al subir imagen');
            }
            
            if ($imageFile['size'] > 2 * 1024 * 1024) { // 2MB
                return $this->sendResponse(false, 'La imagen es demasiado grande (máximo 2MB)');
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($imageFile['type'], $allowedTypes)) {
                return $this->sendResponse(false, 'Tipo de archivo no permitido');
            }
            
            $imagePath = $this->questionModel->uploadImage($id, $imageFile);
            
            if ($imagePath) {
                return $this->sendResponse(true, 'Imagen subida exitosamente', ['image_path' => $imagePath]);
            } else {
                return $this->sendResponse(false, 'Error al subir imagen');
            }
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function deleteImage($id) {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $this->questionModel->deleteImage($id);
            return $this->sendResponse(true, 'Imagen eliminada exitosamente');
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getStats() {
        try {
            $stats = $this->questionModel->getStats();
            return $this->sendResponse(true, 'Estadísticas obtenidas', $stats);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function search() {
        try {
            $term = $_GET['q'] ?? '';
            
            if (empty($term)) {
                return $this->sendResponse(false, 'Término de búsqueda requerido');
            }
            
            $questions = $this->questionModel->search($term);
            return $this->sendResponse(true, 'Búsqueda completada', $questions);
            
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