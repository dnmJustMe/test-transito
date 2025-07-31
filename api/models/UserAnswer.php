<?php
require_once __DIR__ . '/../includes/Database.php';

class UserAnswer {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        try {
            $sql = "INSERT INTO user_answers (session_id, question_id, user_answer, is_correct, time_spent) 
                    VALUES (:session_id, :question_id, :user_answer, :is_correct, :time_spent)";
            
            $params = [
                ':session_id' => $data['session_id'],
                ':question_id' => $data['question_id'],
                ':user_answer' => $data['user_answer'],
                ':is_correct' => $data['is_correct'],
                ':time_spent' => $data['time_spent'] ?? 0
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear respuesta: " . $e->getMessage());
        }
    }
    
    public function getBySession($sessionId) {
        try {
            $sql = "SELECT ua.*, q.question_text, q.option_a, q.option_b, q.option_c, 
                    q.correct_answer, q.explanation, q.image_path 
                    FROM user_answers ua 
                    LEFT JOIN questions q ON ua.question_id = q.id 
                    WHERE ua.session_id = :session_id 
                    ORDER BY ua.created_at ASC";
            
            return $this->db->fetchAll($sql, [':session_id' => $sessionId]);
        } catch (Exception $e) {
            throw new Exception("Error al obtener respuestas: " . $e->getMessage());
        }
    }
    
    public function getCorrectAnswersBySession($sessionId) {
        try {
            $sql = "SELECT COUNT(*) as correct_count 
                    FROM user_answers 
                    WHERE session_id = :session_id AND is_correct = 1";
            
            $result = $this->db->fetch($sql, [':session_id' => $sessionId]);
            return $result['correct_count'];
        } catch (Exception $e) {
            throw new Exception("Error al contar respuestas correctas: " . $e->getMessage());
        }
    }
    
    public function getTotalTimeBySession($sessionId) {
        try {
            $sql = "SELECT SUM(time_spent) as total_time 
                    FROM user_answers 
                    WHERE session_id = :session_id";
            
            $result = $this->db->fetch($sql, [':session_id' => $sessionId]);
            return $result['total_time'] ?? 0;
        } catch (Exception $e) {
            throw new Exception("Error al obtener tiempo total: " . $e->getMessage());
        }
    }
    
    public function exists($sessionId, $questionId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM user_answers 
                    WHERE session_id = :session_id AND question_id = :question_id";
            
            $params = [
                ':session_id' => $sessionId,
                ':question_id' => $questionId
            ];
            
            $result = $this->db->fetch($sql, $params);
            return $result['count'] > 0;
        } catch (Exception $e) {
            throw new Exception("Error al verificar respuesta: " . $e->getMessage());
        }
    }
    
    public function update($sessionId, $questionId, $data) {
        try {
            $sql = "UPDATE user_answers SET 
                    user_answer = :user_answer,
                    is_correct = :is_correct,
                    time_spent = :time_spent
                    WHERE session_id = :session_id AND question_id = :question_id";
            
            $params = [
                ':session_id' => $sessionId,
                ':question_id' => $questionId,
                ':user_answer' => $data['user_answer'],
                ':is_correct' => $data['is_correct'],
                ':time_spent' => $data['time_spent'] ?? 0
            ];
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar respuesta: " . $e->getMessage());
        }
    }
    
    public function getStatsByUser($userId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_answers,
                        COUNT(CASE WHEN ua.is_correct = 1 THEN 1 END) as correct_answers,
                        AVG(ua.time_spent) as avg_time_per_question
                    FROM user_answers ua 
                    LEFT JOIN test_sessions ts ON ua.session_id = ts.id 
                    WHERE ts.user_id = :user_id";
            
            return $this->db->fetch($sql, [':user_id' => $userId]);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas de respuestas: " . $e->getMessage());
        }
    }
    
    public function getByCategory($userId, $categoryId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_answers,
                        COUNT(CASE WHEN ua.is_correct = 1 THEN 1 END) as correct_answers
                    FROM user_answers ua 
                    LEFT JOIN test_sessions ts ON ua.session_id = ts.id 
                    LEFT JOIN questions q ON ua.question_id = q.id 
                    WHERE ts.user_id = :user_id AND q.category_id = :category_id";
            
            $params = [
                ':user_id' => $userId,
                ':category_id' => $categoryId
            ];
            
            return $this->db->fetch($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas por categoría: " . $e->getMessage());
        }
    }
}
?>