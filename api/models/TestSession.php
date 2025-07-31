<?php
require_once __DIR__ . '/../includes/Database.php';

class TestSession {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        try {
            $sql = "INSERT INTO test_sessions (user_id, test_id, total_questions) 
                    VALUES (:user_id, :test_id, :total_questions)";
            
            $params = [
                ':user_id' => $data['user_id'],
                ':test_id' => $data['test_id'],
                ':total_questions' => $data['total_questions']
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear sesión de test: " . $e->getMessage());
        }
    }
    
    public function findById($id) {
        try {
            $sql = "SELECT ts.*, t.name as test_name, u.first_name, u.last_name 
                    FROM test_sessions ts 
                    LEFT JOIN tests t ON ts.test_id = t.id 
                    LEFT JOIN users u ON ts.user_id = u.id 
                    WHERE ts.id = :id";
            return $this->db->fetch($sql, [':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al buscar sesión: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $sql = "UPDATE test_sessions SET 
                    end_time = :end_time,
                    score = :score,
                    correct_answers = :correct_answers,
                    status = :status
                    WHERE id = :id";
            
            $params = [
                ':id' => $id,
                ':end_time' => $data['end_time'],
                ':score' => $data['score'],
                ':correct_answers' => $data['correct_answers'],
                ':status' => $data['status']
            ];
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar sesión: " . $e->getMessage());
        }
    }
    
    public function getByUser($userId, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT ts.*, t.name as test_name 
                    FROM test_sessions ts 
                    LEFT JOIN tests t ON ts.test_id = t.id 
                    WHERE ts.user_id = :user_id 
                    ORDER BY ts.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                ':user_id' => $userId,
                ':limit' => $limit,
                ':offset' => $offset
            ];
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al obtener sesiones del usuario: " . $e->getMessage());
        }
    }
    
    public function getInProgress($userId) {
        try {
            $sql = "SELECT ts.*, t.name as test_name 
                    FROM test_sessions ts 
                    LEFT JOIN tests t ON ts.test_id = t.id 
                    WHERE ts.user_id = :user_id AND ts.status = 'in_progress'";
            
            return $this->db->fetch($sql, [':user_id' => $userId]);
        } catch (Exception $e) {
            throw new Exception("Error al obtener sesión en progreso: " . $e->getMessage());
        }
    }
    
    public function countByUser($userId) {
        try {
            $sql = "SELECT COUNT(*) as total FROM test_sessions WHERE user_id = :user_id";
            $result = $this->db->fetch($sql, [':user_id' => $userId]);
            return $result['total'];
        } catch (Exception $e) {
            throw new Exception("Error al contar sesiones: " . $e->getMessage());
        }
    }
    
    public function getStats($userId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_tests,
                        AVG(score) as average_score,
                        MAX(score) as best_score,
                        COUNT(CASE WHEN score >= 70 THEN 1 END) as passed_tests
                    FROM test_sessions 
                    WHERE user_id = :user_id AND status = 'completed'";
            
            return $this->db->fetch($sql, [':user_id' => $userId]);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas: " . $e->getMessage());
        }
    }
    
    public function getAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT ts.*, t.name as test_name, u.first_name, u.last_name 
                    FROM test_sessions ts 
                    LEFT JOIN tests t ON ts.test_id = t.id 
                    LEFT JOIN users u ON ts.user_id = u.id 
                    ORDER BY ts.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [':limit' => $limit, ':offset' => $offset];
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al obtener todas las sesiones: " . $e->getMessage());
        }
    }
    
    public function count() {
        try {
            $sql = "SELECT COUNT(*) as total FROM test_sessions";
            $result = $this->db->fetch($sql);
            return $result['total'];
        } catch (Exception $e) {
            throw new Exception("Error al contar sesiones: " . $e->getMessage());
        }
    }
}
?>