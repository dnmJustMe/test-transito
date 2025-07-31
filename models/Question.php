<?php
/**
 * Modelo de Pregunta
 */

class Question {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener pregunta por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT q.*, u.username as created_by_username 
                FROM questions q 
                LEFT JOIN users u ON q.created_by = u.id 
                WHERE q.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo pregunta por ID: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener todas las preguntas (para admin)
     */
    public function getAll($page = 1, $limit = 20, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            $whereConditions = ["q.is_active = 1"];
            $params = [];
            
            // Filtros
            if (!empty($filters['search'])) {
                $whereConditions[] = "(q.question_text LIKE ? OR q.category LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($filters['category'])) {
                $whereConditions[] = "q.category = ?";
                $params[] = $filters['category'];
            }
            
            if (!empty($filters['difficulty'])) {
                $whereConditions[] = "q.difficulty = ?";
                $params[] = $filters['difficulty'];
            }
            
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
            
            // Contar total
            $countSql = "SELECT COUNT(*) FROM questions q" . $whereClause;
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Obtener preguntas
            $sql = "
                SELECT q.*, u.username as created_by_username 
                FROM questions q 
                LEFT JOIN users u ON q.created_by = u.id" . $whereClause . "
                ORDER BY q.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $questions = $stmt->fetchAll();
            
            return [
                'questions' => $questions,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            logMessage("Error obteniendo preguntas: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener preguntas aleatorias para test
     */
    public function getRandomQuestions($count, $category = null, $difficulty = null) {
        try {
            $whereConditions = ["is_active = 1"];
            $params = [];
            
            if ($category) {
                $whereConditions[] = "category = ?";
                $params[] = $category;
            }
            
            if ($difficulty) {
                $whereConditions[] = "difficulty = ?";
                $params[] = $difficulty;
            }
            
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
            
            $sql = "
                SELECT id, question_text, image_path, option_1, option_2, option_3, correct_answer, category 
                FROM questions" . $whereClause . "
                ORDER BY RAND() 
                LIMIT ?
            ";
            
            $params[] = $count;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            logMessage("Error obteniendo preguntas aleatorias: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Crear pregunta
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO questions (question_text, image_path, option_1, option_2, option_3, 
                                     correct_answer, category, difficulty, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['question_text'],
                $data['image_path'] ?? null,
                $data['option_1'],
                $data['option_2'],
                $data['option_3'],
                $data['correct_answer'],
                $data['category'] ?? 'general',
                $data['difficulty'] ?? 'medium',
                $data['created_by']
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error creando pregunta: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Actualizar pregunta
     */
    public function update($id, $data) {
        try {
            $allowedFields = ['question_text', 'image_path', 'option_1', 'option_2', 'option_3', 
                            'correct_answer', 'category', 'difficulty'];
            $updateFields = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $params[] = $id;
            $sql = "UPDATE questions SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            logMessage("Error actualizando pregunta: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Eliminar pregunta (soft delete)
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("UPDATE questions SET is_active = 0, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            logMessage("Error eliminando pregunta: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener categorías disponibles
     */
    public function getCategories() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT category, COUNT(*) as count 
                FROM questions 
                WHERE is_active = 1 
                GROUP BY category 
                ORDER BY category
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logMessage("Error obteniendo categorías: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de preguntas
     */
    public function getStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_questions,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_questions,
                    COUNT(CASE WHEN difficulty = 'easy' THEN 1 END) as easy_questions,
                    COUNT(CASE WHEN difficulty = 'medium' THEN 1 END) as medium_questions,
                    COUNT(CASE WHEN difficulty = 'hard' THEN 1 END) as hard_questions,
                    COUNT(DISTINCT category) as total_categories
                FROM questions
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo estadísticas de preguntas: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Validar datos de pregunta
     */
    public function validateQuestionData($data) {
        $errors = [];
        
        // Validar campos requeridos
        $required = ['question_text', 'option_1', 'option_2', 'option_3', 'correct_answer'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "El campo {$field} es requerido";
            }
        }
        
        // Validar respuesta correcta
        if (isset($data['correct_answer']) && !in_array($data['correct_answer'], [1, 2, 3])) {
            $errors[] = "La respuesta correcta debe ser 1, 2 o 3";
        }
        
        // Validar dificultad
        if (isset($data['difficulty']) && !in_array($data['difficulty'], ['easy', 'medium', 'hard'])) {
            $errors[] = "La dificultad debe ser easy, medium o hard";
        }
        
        // Validar longitud de textos
        if (isset($data['question_text']) && strlen($data['question_text']) < 10) {
            $errors[] = "El texto de la pregunta debe tener al menos 10 caracteres";
        }
        
        foreach (['option_1', 'option_2', 'option_3'] as $option) {
            if (isset($data[$option]) && strlen($data[$option]) < 2) {
                $errors[] = "La opción {$option} debe tener al menos 2 caracteres";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Obtener preguntas más falladas
     */
    public function getMostFailedQuestions($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT q.id, q.question_text, q.category,
                       COUNT(ta.id) as total_attempts,
                       COUNT(CASE WHEN ta.is_correct = 0 THEN 1 END) as wrong_answers,
                       ROUND((COUNT(CASE WHEN ta.is_correct = 0 THEN 1 END) / COUNT(ta.id)) * 100, 2) as failure_rate
                FROM questions q
                INNER JOIN test_answers ta ON q.id = ta.question_id
                WHERE q.is_active = 1
                GROUP BY q.id
                HAVING total_attempts >= 10
                ORDER BY failure_rate DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logMessage("Error obteniendo preguntas más falladas: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}