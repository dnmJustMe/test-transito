<?php
require_once __DIR__ . '/../includes/Database.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        try {
            $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
            $params = [
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear categoría: " . $e->getMessage());
        }
    }
    
    public function findById($id) {
        try {
            $sql = "SELECT * FROM categories WHERE id = :id AND is_active = 1";
            return $this->db->fetch($sql, [':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al buscar categoría: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $sql = "UPDATE categories SET 
                    name = :name, 
                    description = :description,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $params = [
                ':id' => $id,
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null
            ];
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar categoría: " . $e->getMessage());
        }
    }
    
    public function getAll() {
        try {
            $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $sql = "UPDATE categories SET is_active = 0 WHERE id = :id";
            $this->db->query($sql, [':id' => $id]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar categoría: " . $e->getMessage());
        }
    }
    
    public function getWithQuestionCount() {
        try {
            $sql = "SELECT c.*, COUNT(q.id) as question_count 
                    FROM categories c 
                    LEFT JOIN questions q ON c.id = q.category_id AND q.is_active = 1
                    WHERE c.is_active = 1 
                    GROUP BY c.id 
                    ORDER BY c.name ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            throw new Exception("Error al obtener categorías con conteo: " . $e->getMessage());
        }
    }
}
?>