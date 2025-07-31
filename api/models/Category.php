<?php
require_once __DIR__ . '/../includes/Database.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $params = [$data['name'], $data['description']];
        return $this->db->query($sql, $params);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM categories WHERE id = ? AND deleted_at IS NULL";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE categories SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $params = [$data['name'], $data['description'], $id];
        return $this->db->query($sql, $params);
    }
    
    public function getAll() {
        $sql = "SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    public function delete($id) {
        $sql = "UPDATE categories SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getWithQuestionCount() {
        $sql = "SELECT c.*, COUNT(q.id) as question_count 
                FROM categories c 
                LEFT JOIN questions q ON c.id = q.category_id AND q.deleted_at IS NULL 
                WHERE c.deleted_at IS NULL 
                GROUP BY c.id 
                ORDER BY c.name";
        return $this->db->fetchAll($sql);
    }
    
    public function updateQuestionCount($categoryId) {
        $sql = "UPDATE categories SET question_count = (
            SELECT COUNT(*) FROM questions WHERE category_id = ? AND deleted_at IS NULL
        ) WHERE id = ?";
        return $this->db->query($sql, [$categoryId, $categoryId]);
    }
}
?>