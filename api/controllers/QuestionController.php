<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/JWT.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/TestSession.php';
require_once __DIR__ . '/../models/UserAnswer.php';

class QuestionController {
    private $question;
    private $category;
    private $testSession;
    private $userAnswer;
    
    public function __construct() {
        $this->question = new Question();
        $this->category = new Category();
        $this->testSession = new TestSession();
        $this->userAnswer = new UserAnswer();
    }
    
    public function getAll() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            
            $questions = $this->question->getAll($page, $limit, $categoryId);
            $total = $this->question->count($categoryId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $questions,
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
            $question = $this->question->findById($id);
            
            if (!$question) {
                http_response_code(404);
                echo json_encode(['error' => 'Pregunta no encontrada']);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $question
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function create() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!$this->validateQuestionData($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos de pregunta inválidos']);
                return;
            }
            
            // Crear pregunta
            $questionId = $this->question->create($input);
            $question = $this->question->findById($questionId);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Pregunta creada exitosamente',
                'data' => $question
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function update($id) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!$this->validateQuestionData($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos de pregunta inválidos']);
                return;
            }
            
            // Actualizar pregunta
            $this->question->update($id, $input);
            $question = $this->question->findById($id);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Pregunta actualizada exitosamente',
                'data' => $question
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function delete($id) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $this->question->delete($id);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Pregunta eliminada exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function uploadImage($id) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            if (!isset($_FILES['image'])) {
                http_response_code(400);
                echo json_encode(['error' => 'No se proporcionó imagen']);
                return;
            }
            
            $file = $_FILES['image'];
            
            // Validaciones de archivo
            if ($file['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al subir archivo']);
                return;
            }
            
            if ($file['size'] > MAX_FILE_SIZE) {
                http_response_code(400);
                echo json_encode(['error' => 'Archivo demasiado grande']);
                return;
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ALLOWED_EXTENSIONS)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de archivo no permitido']);
                return;
            }
            
            // Crear directorio si no existe
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            // Generar nombre único
            $filename = uniqid() . '.' . $extension;
            $filepath = UPLOAD_DIR . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->question->updateImage($id, $filename);
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Imagen subida exitosamente',
                    'filename' => $filename
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar archivo']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getRandomQuestions() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            
            $questions = $this->question->getRandomQuestions($limit, $categoryId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $questions
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function search() {
        try {
            $term = isset($_GET['q']) ? $_GET['q'] : '';
            
            if (empty($term)) {
                http_response_code(400);
                echo json_encode(['error' => 'Término de búsqueda requerido']);
                return;
            }
            
            $questions = $this->question->search($term);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $questions
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function startTest() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $limit = $input['limit'] ?? 20;
            $categoryId = $input['category_id'] ?? null;
            
            // Obtener preguntas aleatorias
            $questions = $this->question->getRandomQuestions($limit, $categoryId);
            
            if (empty($questions)) {
                http_response_code(404);
                echo json_encode(['error' => 'No hay preguntas disponibles']);
                return;
            }
            
            // Crear sesión de test
            $sessionData = [
                'user_id' => $user['user_id'],
                'test_id' => 1, // Test por defecto
                'total_questions' => count($questions)
            ];
            
            $sessionId = $this->testSession->create($sessionData);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'session_id' => $sessionId,
                'questions' => $questions,
                'total_questions' => count($questions)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function submitAnswer() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['session_id']) || !isset($input['question_id']) || !isset($input['answer'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }
            
            // Obtener pregunta
            $question = $this->question->findById($input['question_id']);
            if (!$question) {
                http_response_code(404);
                echo json_encode(['error' => 'Pregunta no encontrada']);
                return;
            }
            
            // Verificar si ya existe respuesta
            if ($this->userAnswer->exists($input['session_id'], $input['question_id'])) {
                // Actualizar respuesta existente
                $this->userAnswer->update($input['session_id'], $input['question_id'], [
                    'user_answer' => $input['answer'],
                    'is_correct' => ($input['answer'] === $question['correct_answer']),
                    'time_spent' => $input['time_spent'] ?? 0
                ]);
            } else {
                // Crear nueva respuesta
                $this->userAnswer->create([
                    'session_id' => $input['session_id'],
                    'question_id' => $input['question_id'],
                    'user_answer' => $input['answer'],
                    'is_correct' => ($input['answer'] === $question['correct_answer']),
                    'time_spent' => $input['time_spent'] ?? 0
                ]);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'is_correct' => ($input['answer'] === $question['correct_answer']),
                'correct_answer' => $question['correct_answer'],
                'explanation' => $question['explanation']
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function finishTest($sessionId) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            // Obtener respuestas correctas
            $correctAnswers = $this->userAnswer->getCorrectAnswersBySession($sessionId);
            $totalTime = $this->userAnswer->getTotalTimeBySession($sessionId);
            
            // Obtener sesión
            $session = $this->testSession->findById($sessionId);
            if (!$session || $session['user_id'] != $user['user_id']) {
                http_response_code(404);
                echo json_encode(['error' => 'Sesión no encontrada']);
                return;
            }
            
            // Calcular puntuación
            $score = ($correctAnswers / $session['total_questions']) * 100;
            
            // Actualizar sesión
            $this->testSession->update($sessionId, [
                'end_time' => date('Y-m-d H:i:s'),
                'score' => round($score, 2),
                'correct_answers' => $correctAnswers,
                'status' => 'completed'
            ]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'score' => round($score, 2),
                'correct_answers' => $correctAnswers,
                'total_questions' => $session['total_questions'],
                'total_time' => $totalTime,
                'passed' => $score >= 70
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    private function validateQuestionData($data) {
        return isset($data['category_id']) && 
               isset($data['question_text']) && 
               isset($data['option_a']) && 
               isset($data['option_b']) && 
               isset($data['option_c']) && 
               isset($data['correct_answer']) &&
               in_array($data['correct_answer'], ['A', 'B', 'C']);
    }
}
?>