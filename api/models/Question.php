<?php
/**
 * Modelo de Preguntas
 */

require_once 'config/database.php';

class Question {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($data) {
        $sql = "INSERT INTO questions (question_text, answer1, answer2, answer3, correct_answer, image_path) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['question_text'],
            $data['answer1'],
            $data['answer2'],
            $data['answer3'],
            $data['correct_answer'],
            $data['image_path'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM questions WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE questions SET 
                question_text = ?, 
                answer1 = ?, 
                answer2 = ?, 
                answer3 = ?, 
                correct_answer = ?, 
                image_path = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $params = [
            $data['question_text'],
            $data['answer1'],
            $data['answer2'],
            $data['answer3'],
            $data['correct_answer'],
            $data['image_path'] ?? null,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM questions WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getAll($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM questions ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getRandomQuestions($count) {
        $sql = "SELECT * FROM questions ORDER BY RAND() LIMIT ?";
        $stmt = $this->db->query($sql, [$count]);
        return $stmt->fetchAll();
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM questions";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_questions,
                COUNT(CASE WHEN image_path IS NOT NULL THEN 1 END) as questions_with_images,
                COUNT(CASE WHEN image_path IS NULL THEN 1 END) as questions_without_images
                FROM questions";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    public function search($term) {
        $sql = "SELECT * FROM questions 
                WHERE question_text LIKE ? OR answer1 LIKE ? OR answer2 LIKE ? OR answer3 LIKE ? 
                ORDER BY created_at DESC";
        
        $searchTerm = "%$term%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    public function uploadImage($questionId, $imageFile) {
        $uploadDir = 'assets/img/questions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = 'q' . $questionId . '_' . time() . '.png';
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($imageFile['tmp_name'], $filePath)) {
            $sql = "UPDATE questions SET image_path = ? WHERE id = ?";
            $this->db->query($sql, [$filePath, $questionId]);
            return $filePath;
        }
        
        return false;
    }
    
    public function deleteImage($questionId) {
        $question = $this->findById($questionId);
        if ($question && $question['image_path']) {
            if (file_exists($question['image_path'])) {
                unlink($question['image_path']);
            }
            
            $sql = "UPDATE questions SET image_path = NULL WHERE id = ?";
            return $this->db->query($sql, [$questionId]);
        }
        
        return false;
    }
}
?>