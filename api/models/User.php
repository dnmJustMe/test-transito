<?php
require_once __DIR__ . '/../includes/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        try {
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, role) 
                    VALUES (:username, :email, :password, :first_name, :last_name, :role)";
            
            $params = [
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':role' => $data['role'] ?? 'user'
            ];
            
            $this->db->query($sql, $params);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Error al crear usuario: " . $e->getMessage());
        }
    }
    
    public function findByEmail($email) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1";
            return $this->db->fetch($sql, [':email' => $email]);
        } catch (Exception $e) {
            throw new Exception("Error al buscar usuario: " . $e->getMessage());
        }
    }
    
    public function findByUsername($username) {
        try {
            $sql = "SELECT * FROM users WHERE username = :username AND is_active = 1";
            return $this->db->fetch($sql, [':username' => $username]);
        } catch (Exception $e) {
            throw new Exception("Error al buscar usuario: " . $e->getMessage());
        }
    }
    
    public function findById($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id AND is_active = 1";
            return $this->db->fetch($sql, [':id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Error al buscar usuario: " . $e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $sql = "UPDATE users SET 
                    first_name = :first_name, 
                    last_name = :last_name, 
                    email = :email,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $params = [
                ':id' => $id,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email']
            ];
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar usuario: " . $e->getMessage());
        }
    }
    
    public function updatePassword($id, $newPassword) {
        try {
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $params = [
                ':id' => $id,
                ':password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ];
            
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar contraseña: " . $e->getMessage());
        }
    }
    
    public function authenticate($email, $password) {
        try {
            $user = $this->findByEmail($email);
            if (!$user) {
                return false;
            }
            
            if (password_verify($password, $user['password'])) {
                unset($user['password']); // No devolver la contraseña
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            throw new Exception("Error en autenticación: " . $e->getMessage());
        }
    }
    
    public function getAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT id, username, email, first_name, last_name, role, created_at 
                    FROM users WHERE is_active = 1 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [':limit' => $limit, ':offset' => $offset];
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }
    
    public function count() {
        try {
            $sql = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
            $result = $this->db->fetch($sql);
            return $result['total'];
        } catch (Exception $e) {
            throw new Exception("Error al contar usuarios: " . $e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $sql = "UPDATE users SET is_active = 0 WHERE id = :id";
            $this->db->query($sql, [':id' => $id]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al eliminar usuario: " . $e->getMessage());
        }
    }
    
    public function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email AND is_active = 1";
            $params = [':email' => $email];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $result = $this->db->fetch($sql, $params);
            return $result['count'] > 0;
        } catch (Exception $e) {
            throw new Exception("Error al verificar email: " . $e->getMessage());
        }
    }
    
    public function usernameExists($username, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username AND is_active = 1";
            $params = [':username' => $username];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $result = $this->db->fetch($sql, $params);
            return $result['count'] > 0;
        } catch (Exception $e) {
            throw new Exception("Error al verificar username: " . $e->getMessage());
        }
    }
}
?>