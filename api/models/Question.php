<?php
require_once __DIR__ . '/../includes/Database.php';

class Question {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO questions (category_id, nro, question_text, answer1, answer2, answer3, correct_answer, article_reference, image_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $imagePath = null;
        if (isset($data['nro'])) {
            $imagePath = "assets/img/questions/i{$data['nro']}.png";
        }
        
        $params = [
            $data['category_id'],
            $data['nro'],
            $data['question_text'],
            $data['answer1'],
            $data['answer2'],
            $data['answer3'],
            $data['correct_answer'],
            $data['article_reference'] ?? null,
            $imagePath
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function findById($id) {
        $sql = "SELECT q.*, c.name as category_name 
                FROM questions q 
                LEFT JOIN categories c ON q.category_id = c.id 
                WHERE q.id = ? AND q.deleted_at IS NULL";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE questions SET 
                category_id = ?, 
                nro = ?, 
                question_text = ?, 
                answer1 = ?, 
                answer2 = ?, 
                answer3 = ?, 
                correct_answer = ?, 
                article_reference = ?, 
                image_path = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $imagePath = null;
        if (isset($data['nro'])) {
            $imagePath = "assets/img/questions/i{$data['nro']}.png";
        }
        
        $params = [
            $data['category_id'],
            $data['nro'],
            $data['question_text'],
            $data['answer1'],
            $data['answer2'],
            $data['answer3'],
            $data['correct_answer'],
            $data['article_reference'] ?? null,
            $imagePath,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function getAll($page = 1, $limit = 20, $categoryId = null) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE q.deleted_at IS NULL";
        $params = [];
        
        if ($categoryId) {
            $whereClause .= " AND q.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql = "SELECT q.*, c.name as category_name 
                FROM questions q 
                LEFT JOIN categories c ON q.category_id = c.id 
                $whereClause 
                ORDER BY q.id DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getRandomQuestions($categoryId = null, $limit = 20) {
        $whereClause = "WHERE q.deleted_at IS NULL";
        $params = [];
        
        if ($categoryId) {
            $whereClause .= " AND q.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql = "SELECT q.*, c.name as category_name 
                FROM questions q 
                LEFT JOIN categories c ON q.category_id = c.id 
                $whereClause 
                ORDER BY RAND() 
                LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function count($categoryId = null) {
        $whereClause = "WHERE deleted_at IS NULL";
        $params = [];
        
        if ($categoryId) {
            $whereClause .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql = "SELECT COUNT(*) as total FROM questions $whereClause";
        $result = $this->db->fetch($sql, $params);
        return $result['total'];
    }
    
    public function delete($id) {
        $sql = "UPDATE questions SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getByCategory($categoryId) {
        $sql = "SELECT * FROM questions WHERE category_id = ? AND deleted_at IS NULL ORDER BY id";
        return $this->db->fetchAll($sql, [$categoryId]);
    }
    
    public function search($term) {
        $sql = "SELECT q.*, c.name as category_name 
                FROM questions q 
                LEFT JOIN categories c ON q.category_id = c.id 
                WHERE (q.question_text LIKE ? OR q.answer1 LIKE ? OR q.answer2 LIKE ? OR q.answer3 LIKE ?) 
                AND q.deleted_at IS NULL 
                ORDER BY q.id DESC";
        
        $searchTerm = "%$term%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getImagePath($nro) {
        $imagePath = "assets/img/questions/i{$nro}.png";
        $fullPath = __DIR__ . "/../../{$imagePath}";
        
        if (file_exists($fullPath)) {
            return $imagePath;
        }
        
        return null;
    }
    
    public function updateImagePath($id, $nro) {
        $imagePath = "assets/img/questions/i{$nro}.png";
        $sql = "UPDATE questions SET image_path = ? WHERE id = ?";
        return $this->db->query($sql, [$imagePath, $id]);
    }
}
?>