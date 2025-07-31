<?php
/**
 * Modelo de Usuario
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, first_name, last_name, role, avatar, 
                       is_active, email_verified, created_at, updated_at 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo usuario por ID: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, first_name, last_name, role, avatar, 
                       is_active, email_verified, created_at, updated_at 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo usuario por email: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener todos los usuarios (para admin)
     */
    public function getAll($page = 1, $limit = 20, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $whereClause = "";
            $params = [];
            
            if (!empty($search)) {
                $whereClause = " WHERE username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            }
            
            // Contar total
            $countSql = "SELECT COUNT(*) FROM users" . $whereClause;
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Obtener usuarios
            $sql = "
                SELECT id, username, email, first_name, last_name, role, avatar, 
                       is_active, email_verified, created_at, updated_at 
                FROM users" . $whereClause . "
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            logMessage("Error obteniendo usuarios: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Crear usuario
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, role) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $data['password'], // Ya debe venir hasheada
                $data['first_name'],
                $data['last_name'],
                $data['role'] ?? 'user'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error creando usuario: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function update($id, $data) {
        try {
            $allowedFields = ['username', 'email', 'first_name', 'last_name', 'role', 'is_active'];
            $updateFields = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            logMessage("Error actualizando usuario: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            logMessage("Error eliminando usuario: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de usuario
     */
    public function getUserStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_tests,
                    AVG(score_percentage) as avg_score,
                    MAX(score_percentage) as best_score,
                    COUNT(CASE WHEN score_percentage >= ? THEN 1 END) as passed_tests
                FROM test_sessions 
                WHERE user_id = ? AND status = 'completed'
            ");
            $stmt->execute([DEFAULT_PASSING_SCORE, $userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo estadísticas de usuario: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Verificar si el email existe
     */
    public function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
            $params = [$email];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            logMessage("Error verificando email: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Verificar si el username existe
     */
    public function usernameExists($username, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
            $params = [$username];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            logMessage("Error verificando username: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}