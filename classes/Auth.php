<?php
/**
 * Clase para manejo de autenticación y sesiones
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->startSession();
    }
    
    /**
     * Iniciar sesión segura
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerar ID de sesión para prevenir session fixation
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
        
        // Verificar expiración de sesión
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->logout();
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($data) {
        try {
            // Validar datos
            $validation = $this->validateRegistrationData($data);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            // Verificar si el usuario ya existe
            if ($this->userExists($data['email'], $data['username'])) {
                return ['success' => false, 'message' => 'El email o nombre de usuario ya están registrados'];
            }
            
            // Encriptar contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
            
            // Insertar usuario
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['first_name'],
                $data['last_name']
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                logMessage("Usuario registrado: {$data['email']} (ID: {$userId})");
                return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $userId];
            }
            
            return ['success' => false, 'message' => 'Error al registrar usuario'];
            
        } catch (Exception $e) {
            logMessage("Error en registro: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Iniciar sesión
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Buscar usuario
            $stmt = $this->db->prepare("
                SELECT id, username, email, password, first_name, last_name, role, is_active 
                FROM users 
                WHERE email = ? AND is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                logMessage("Intento de login fallido para: {$email}", 'WARNING');
                return ['success' => false, 'message' => 'Credenciales incorrectas'];
            }
            
            // Crear sesión
            $sessionId = $this->createUserSession($user['id']);
            
            // Establecer variables de sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['session_id'] = $sessionId;
            $_SESSION['logged_in'] = true;
            
            logMessage("Usuario logueado: {$user['email']} (ID: {$user['id']})");
            
            return [
                'success' => true, 
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role']
                ]
            ];
            
        } catch (Exception $e) {
            logMessage("Error en login: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        if (isset($_SESSION['session_id'])) {
            $this->deleteUserSession($_SESSION['session_id']);
        }
        
        session_unset();
        session_destroy();
        
        // Iniciar nueva sesión limpia
        session_start();
        session_regenerate_id(true);
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, first_name, last_name, role, avatar, created_at 
                FROM users 
                WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo usuario actual: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Crear sesión de usuario en la base de datos
     */
    private function createUserSession($userId) {
        try {
            $sessionId = bin2hex(random_bytes(64));
            $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = $this->db->prepare("
                INSERT INTO user_sessions (id, user_id, ip_address, user_agent, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$sessionId, $userId, $ipAddress, $userAgent, $expiresAt]);
            
            return $sessionId;
        } catch (Exception $e) {
            logMessage("Error creando sesión: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }
    
    /**
     * Eliminar sesión de usuario
     */
    private function deleteUserSession($sessionId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
        } catch (Exception $e) {
            logMessage("Error eliminando sesión: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Limpiar sesiones expiradas
     */
    public function cleanExpiredSessions() {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $stmt->execute();
        } catch (Exception $e) {
            logMessage("Error limpiando sesiones expiradas: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Verificar si el usuario existe
     */
    private function userExists($email, $username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Validar datos de registro
     */
    private function validateRegistrationData($data) {
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'message' => "El campo {$field} es requerido"];
            }
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email no válido'];
        }
        
        // Validar username
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
            return ['valid' => false, 'message' => 'El nombre de usuario debe tener entre 3-20 caracteres alfanuméricos'];
        }
        
        // Validar contraseña
        if (strlen($data['password']) < 6) {
            return ['valid' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        // Validar nombres
        if (strlen($data['first_name']) < 2 || strlen($data['last_name']) < 2) {
            return ['valid' => false, 'message' => 'Los nombres deben tener al menos 2 caracteres'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verificar contraseña actual
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
            }
            
            // Validar nueva contraseña
            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres'];
            }
            
            // Actualizar contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if ($result) {
                logMessage("Contraseña cambiada para usuario ID: {$userId}");
                return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
            }
            
            return ['success' => false, 'message' => 'Error al actualizar contraseña'];
            
        } catch (Exception $e) {
            logMessage("Error cambiando contraseña: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['first_name', 'last_name', 'username'];
            $updateFields = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'message' => 'No hay datos para actualizar'];
            }
            
            // Verificar username único si se está actualizando
            if (isset($data['username'])) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$data['username'], $userId]);
                if ($stmt->fetchColumn() > 0) {
                    return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
                }
            }
            
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Actualizar sesión si se cambió el username
                if (isset($data['username'])) {
                    $_SESSION['username'] = $data['username'];
                }
                
                logMessage("Perfil actualizado para usuario ID: {$userId}");
                return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
            }
            
            return ['success' => false, 'message' => 'Error al actualizar perfil'];
            
        } catch (Exception $e) {
            logMessage("Error actualizando perfil: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
}