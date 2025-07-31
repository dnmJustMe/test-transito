<?php
/**
 * Modelo de Sesiones de Test
 */

require_once 'api/config/database.php';

class TestSession {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($data) {
        $sql = "INSERT INTO test_sessions (user_id, difficulty, question_count, correct_answers, score, passed) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['user_id'],
            $data['difficulty'],
            $data['question_count'],
            $data['correct_answers'],
            $data['score'],
            $data['passed']
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM test_sessions WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function getByUser($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM test_sessions WHERE user_id = ? ORDER BY completed_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->query($sql, [$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getStatsByUser($userId) {
        $sql = "SELECT 
                COUNT(*) as total_tests,
                COUNT(CASE WHEN passed = 1 THEN 1 END) as passed_tests,
                COUNT(CASE WHEN passed = 0 THEN 1 END) as failed_tests,
                AVG(score) as average_score,
                MAX(score) as best_score,
                COUNT(CASE WHEN difficulty = 'easy' THEN 1 END) as easy_tests,
                COUNT(CASE WHEN difficulty = 'medium' THEN 1 END) as medium_tests,
                COUNT(CASE WHEN difficulty = 'hard' THEN 1 END) as hard_tests
                FROM test_sessions WHERE user_id = ?";
        
        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->fetch();
    }
    
    public function getGlobalStats() {
        $sql = "SELECT 
                COUNT(*) as total_tests,
                COUNT(CASE WHEN passed = 1 THEN 1 END) as passed_tests,
                COUNT(CASE WHEN passed = 0 THEN 1 END) as failed_tests,
                AVG(score) as average_score,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN difficulty = 'easy' THEN 1 END) as easy_tests,
                COUNT(CASE WHEN difficulty = 'medium' THEN 1 END) as medium_tests,
                COUNT(CASE WHEN difficulty = 'hard' THEN 1 END) as hard_tests
                FROM test_sessions";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    public function saveUserAnswers($sessionId, $answers) {
        $sql = "INSERT INTO user_answers (session_id, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)";
        
        foreach ($answers as $answer) {
            $this->db->query($sql, [
                $sessionId,
                $answer['question_id'],
                $answer['user_answer'],
                $answer['is_correct']
            ]);
        }
    }
    
    public function getUserAnswers($sessionId) {
        $sql = "SELECT ua.*, q.question_text, q.answer1, q.answer2, q.answer3, q.correct_answer, q.image_path
                FROM user_answers ua
                JOIN questions q ON ua.question_id = q.id
                WHERE ua.session_id = ?
                ORDER BY ua.answered_at";
        
        $stmt = $this->db->query($sql, [$sessionId]);
        return $stmt->fetchAll();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM test_sessions WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getRecentTests($limit = 10) {
        $sql = "SELECT ts.*, u.username, u.first_name, u.last_name
                FROM test_sessions ts
                JOIN users u ON ts.user_id = u.id
                ORDER BY ts.completed_at DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    public function getTopScores($limit = 10) {
        $sql = "SELECT ts.*, u.username, u.first_name, u.last_name
                FROM test_sessions ts
                JOIN users u ON ts.user_id = u.id
                WHERE ts.passed = 1
                ORDER BY ts.score DESC, ts.completed_at ASC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
}
?>