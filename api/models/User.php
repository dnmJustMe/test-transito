<?php
/**
 * Modelo de Usuario
 */

require_once 'api/config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, lives) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $params = [
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['role'] ?? 'user',
            $data['lives'] ?? 3
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        return $stmt->fetch();
    }
    
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function usernameExists($username) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $stmt = $this->db->query($sql, [$username]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                first_name = ?, 
                last_name = ?, 
                lives = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $params = [
            $data['username'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['lives'],
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function updateLives($id, $lives, $lostLife = false) {
        $sql = "UPDATE users SET 
                lives = ?, 
                last_life_lost = " . ($lostLife ? "CURRENT_TIMESTAMP" : "NULL") . ",
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        return $this->db->query($sql, [$lives, $id]);
    }
    
    public function getAll() {
        $sql = "SELECT id, username, email, first_name, last_name, role, lives, last_life_lost, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getLivesWithRegeneration($userId) {
        $user = $this->findById($userId);
        if (!$user) return null;
        
        // Verificar si hay vidas perdidas que se pueden regenerar
        if ($user['last_life_lost'] && $user['lives'] < 3) {
            $lastLost = new DateTime($user['last_life_lost']);
            $now = new DateTime();
            $diff = $now->diff($lastLost);
            $minutesPassed = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            // Obtener configuración de regeneración
            $configSql = "SELECT value FROM system_config WHERE key_name = 'life_regeneration_minutes'";
            $configStmt = $this->db->query($configSql);
            $regenerationMinutes = $configStmt->fetch()['value'] ?? 5;
            
            if ($minutesPassed >= $regenerationMinutes) {
                // Regenerar una vida
                $newLives = min(3, $user['lives'] + 1);
                $this->updateLives($userId, $newLives);
                $user['lives'] = $newLives;
            }
        }
        
        return $user;
    }
    
    public function canTakeTest($userId) {
        $user = $this->getLivesWithRegeneration($userId);
        return $user && $user['lives'] > 0;
    }
    
    public function loseLife($userId) {
        $user = $this->findById($userId);
        if (!$user || $user['lives'] <= 0) return false;
        
        $newLives = $user['lives'] - 1;
        return $this->updateLives($userId, $newLives, true);
    }
    
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
                COUNT(CASE WHEN role = 'user' THEN 1 END) as total_normal_users,
                AVG(lives) as avg_lives
                FROM users";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
?>