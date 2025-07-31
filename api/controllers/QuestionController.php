<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/JWT.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/TestSession.php';
require_once __DIR__ . '/../models/UserAnswer.php';

class QuestionController {
    private $questionModel;
    private $categoryModel;
    
    public function __construct() {
        $this->questionModel = new Question();
        $this->categoryModel = new Category();
    }
    
    public function getAll() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            
            $questions = $this->questionModel->getAll($page, $limit, $categoryId);
            
            // Procesar imágenes para cada pregunta
            foreach ($questions as &$question) {
                if ($question['nro']) {
                    $imagePath = $this->questionModel->getImagePath($question['nro']);
                    $question['image_url'] = $imagePath ? BASE_URL . $imagePath : null;
                }
            }
            
            $total = $this->questionModel->count($categoryId);
            
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
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getById($id) {
        try {
            $question = $this->questionModel->findById($id);
            
            if (!$question) {
                http_response_code(404);
                echo json_encode(['error' => 'Pregunta no encontrada']);
                return;
            }
            
            // Procesar imagen
            if ($question['nro']) {
                $imagePath = $this->questionModel->getImagePath($question['nro']);
                $question['image_url'] = $imagePath ? BASE_URL . $imagePath : null;
            }
            
            echo json_encode(['success' => true, 'data' => $question]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function create() {
        try {
            // Verificar permisos de administrador
            $currentUser = JWT::getCurrentUser();
            if (!$currentUser || $currentUser['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos requeridos
            $required = ['category_id', 'nro', 'question_text', 'answer1', 'answer2', 'answer3', 'correct_answer'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Campo requerido: $field"]);
                    return;
                }
            }
            
            // Validar que la categoría existe
            $category = $this->categoryModel->findById($input['category_id']);
            if (!$category) {
                http_response_code(400);
                echo json_encode(['error' => 'Categoría no válida']);
                return;
            }
            
            // Validar respuesta correcta
            if (!in_array($input['correct_answer'], [1, 2, 3])) {
                http_response_code(400);
                echo json_encode(['error' => 'Respuesta correcta debe ser 1, 2 o 3']);
                return;
            }
            
            $questionId = $this->questionModel->create($input);
            
            // Actualizar contador de preguntas en la categoría
            $this->categoryModel->updateQuestionCount($input['category_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Pregunta creada exitosamente',
                'data' => ['id' => $questionId]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function update($id) {
        try {
            // Verificar permisos de administrador
            $currentUser = JWT::getCurrentUser();
            if (!$currentUser || $currentUser['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar que la pregunta existe
            $existingQuestion = $this->questionModel->findById($id);
            if (!$existingQuestion) {
                http_response_code(404);
                echo json_encode(['error' => 'Pregunta no encontrada']);
                return;
            }
            
            // Validar datos requeridos
            $required = ['category_id', 'nro', 'question_text', 'answer1', 'answer2', 'answer3', 'correct_answer'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Campo requerido: $field"]);
                    return;
                }
            }
            
            // Validar que la categoría existe
            $category = $this->categoryModel->findById($input['category_id']);
            if (!$category) {
                http_response_code(400);
                echo json_encode(['error' => 'Categoría no válida']);
                return;
            }
            
            // Validar respuesta correcta
            if (!in_array($input['correct_answer'], [1, 2, 3])) {
                http_response_code(400);
                echo json_encode(['error' => 'Respuesta correcta debe ser 1, 2 o 3']);
                return;
            }
            
            $this->questionModel->update($id, $input);
            
            // Actualizar contadores de categorías
            $this->categoryModel->updateQuestionCount($input['category_id']);
            if ($existingQuestion['category_id'] != $input['category_id']) {
                $this->categoryModel->updateQuestionCount($existingQuestion['category_id']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Pregunta actualizada exitosamente'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function delete($id) {
        try {
            // Verificar permisos de administrador
            $currentUser = JWT::getCurrentUser();
            if (!$currentUser || $currentUser['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $question = $this->questionModel->findById($id);
            if (!$question) {
                http_response_code(404);
                echo json_encode(['error' => 'Pregunta no encontrada']);
                return;
            }
            
            $this->questionModel->delete($id);
            
            // Actualizar contador de preguntas en la categoría
            $this->categoryModel->updateQuestionCount($question['category_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Pregunta eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getRandomQuestions() {
        try {
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            $questions = $this->questionModel->getRandomQuestions($categoryId, $limit);
            
            // Procesar imágenes para cada pregunta
            foreach ($questions as &$question) {
                if ($question['nro']) {
                    $imagePath = $this->questionModel->getImagePath($question['nro']);
                    $question['image_url'] = $imagePath ? BASE_URL . $imagePath : null;
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $questions
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function startTest() {
        try {
            $currentUser = JWT::getCurrentUser();
            if (!$currentUser) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : null;
            $questionCount = isset($input['question_count']) ? (int)$input['question_count'] : 20;
            
            // Obtener preguntas aleatorias
            $questions = $this->questionModel->getRandomQuestions($categoryId, $questionCount);
            
            if (empty($questions)) {
                http_response_code(404);
                echo json_encode(['error' => 'No hay preguntas disponibles']);
                return;
            }
            
            // Procesar imágenes para cada pregunta
            foreach ($questions as &$question) {
                if ($question['nro']) {
                    $imagePath = $this->questionModel->getImagePath($question['nro']);
                    $question['image_url'] = $imagePath ? BASE_URL . $imagePath : null;
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'questions' => $questions,
                    'total_questions' => count($questions)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function uploadImage() {
        try {
            // Verificar permisos de administrador
            $currentUser = JWT::getCurrentUser();
            if (!$currentUser || $currentUser['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'Error al subir imagen']);
                return;
            }
            
            $nro = isset($_POST['nro']) ? (int)$_POST['nro'] : null;
            if (!$nro) {
                http_response_code(400);
                echo json_encode(['error' => 'Número de pregunta requerido']);
                return;
            }
            
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de archivo no permitido']);
                return;
            }
            
            $uploadDir = UPLOAD_DIR;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filename = "i{$nro}.png";
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Imagen subida exitosamente',
                    'data' => [
                        'filename' => $filename,
                        'path' => "assets/img/questions/{$filename}"
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar imagen']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
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
            $question = $this->questionModel->findById($input['question_id']);
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