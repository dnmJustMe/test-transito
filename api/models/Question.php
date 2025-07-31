<?php
require_once __DIR__ . '/../includes/Database.php';

class Question {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        try {
            $sql = "INSERT INTO questions (category_id, question_text, option_a, option_b, option_c, 
                    correct_answer, image_path, explanation, difficulty) 
                    VALUES (:category_id, :question_text, :option_a, :option_b, :option_c, 
                    :correct_answer, :image_path, :explanation, :difficulty)";
            
            $params = [
                ':category_id' => $data['category_id'],
                ':question_text' => $data['question_text'],
                ':option_a' => $data['option_a'],
                ':option_b' => $data['option_b'],
                ':option_c' => $data['option_c'],
                ':correct_answer' => $data['correct_answer'],
                ':image_path' => $data['image_path'] ?? null,
                ':explanation' => $data['explanation'] ?? null,
                ':difficulty' => $data['difficulty'] ?? 'medium'
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear pregunta: " . $e->getMessage());
        }
    }
    
    public function findById($id) {
        try {
            $sql = "SELECT q.*, c.name as category_name 
                    FROM questions q 
                    LEFT JOIN categories c ON q.category_id = c.id 
                    WHERE q.id = :id AND q.is_active = 1";
            return $this->db->fetch($sql, [':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al buscar pregunta: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $sql = "UPDATE questions SET 
                    category_id = :category_id,
                    question_text = :question_text,
                    option_a = :option_a,
                    option_b = :option_b,
                    option_c = :option_c,
                    correct_answer = :correct_answer,
                    explanation = :explanation,
                    difficulty = :difficulty,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $params = [
                ':id' => $id,
                ':category_id' => $data['category_id'],
                ':question_text' => $data['question_text'],
                ':option_a' => $data['option_a'],
                ':option_b' => $data['option_b'],
                ':option_c' => $data['option_c'],
                ':correct_answer' => $data['correct_answer'],
                ':explanation' => $data['explanation'] ?? null,
                ':difficulty' => $data['difficulty'] ?? 'medium'
            ];
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar pregunta: " . $e->getMessage());
        }
    }
    
    public function updateImage($id, $imagePath) {
        try {
            $sql = "UPDATE questions SET image_path = :image_path WHERE id = :id";
            $this->db->query($sql, [':id' => $id, ':image_path' => $imagePath]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar imagen: " . $e->getMessage());
        }
    }
    
    public function getAll($page = 1, $limit = 10, $categoryId = null) {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT q.*, c.name as category_name 
                    FROM questions q 
                    LEFT JOIN categories c ON q.category_id = c.id 
                    WHERE q.is_active = 1";
            
            $params = [];
            
            if ($categoryId) {
                $sql .= " AND q.category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }
            
            $sql .= " ORDER BY q.created_at DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al obtener preguntas: " . $e->getMessage());
        }
    }
    
    public function getRandomQuestions($limit = 20, $categoryId = null) {
        try {
            $sql = "SELECT q.*, c.name as category_name 
                    FROM questions q 
                    LEFT JOIN categories c ON q.category_id = c.id 
                    WHERE q.is_active = 1";
            
            $params = [];
            
            if ($categoryId) {
                $sql .= " AND q.category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }
            
            $sql .= " ORDER BY RAND() LIMIT :limit";
            $params[':limit'] = $limit;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al obtener preguntas aleatorias: " . $e->getMessage());
        }
    }
    
    public function count($categoryId = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM questions WHERE is_active = 1";
            $params = [];
            
            if ($categoryId) {
                $sql .= " AND category_id = :category_id";
                $params[':category_id'] = $categoryId;
            }
            
            $result = $this->db->fetch($sql, $params);
            return $result['total'];
        } catch (Exception $e) {
            throw new Exception("Error al contar preguntas: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $sql = "UPDATE questions SET is_active = 0 WHERE id = :id";
            $this->db->query($sql, [':id' => $id]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar pregunta: " . $e->getMessage());
        }
    }
    
    public function getByCategory($categoryId) {
        try {
            $sql = "SELECT * FROM questions WHERE category_id = :category_id AND is_active = 1";
            return $this->db->fetchAll($sql, [':category_id' => $categoryId]);
        } catch (Exception $e) {
            throw new Exception("Error al obtener preguntas por categoría: " . $e->getMessage());
        }
    }
    
    public function search($term) {
        try {
            $sql = "SELECT q.*, c.name as category_name 
                    FROM questions q 
                    LEFT JOIN categories c ON q.category_id = c.id 
                    WHERE q.is_active = 1 
                    AND (q.question_text LIKE :term OR q.option_a LIKE :term 
                    OR q.option_b LIKE :term OR q.option_c LIKE :term)";
            
            $params = [':term' => '%' . $term . '%'];
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al buscar preguntas: " . $e->getMessage());
        }
    }
}
?>