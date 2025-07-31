<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/JWT.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function register() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!$this->validateRegistrationData($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos de registro inválidos']);
                return;
            }
            
            // Verificar si el email ya existe
            if ($this->user->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['error' => 'El email ya está registrado']);
                return;
            }
            
            // Verificar si el username ya existe
            if ($this->user->usernameExists($input['username'])) {
                http_response_code(409);
                echo json_encode(['error' => 'El nombre de usuario ya está en uso']);
                return;
            }
            
            // Crear usuario
            $userId = $this->user->create($input);
            $user = $this->user->findById($userId);
            
            // Generar token
            $token = JWT::generate([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'exp' => time() + (SESSION_LIFETIME)
            ]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'token' => $token,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function login() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['email']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email y contraseña son requeridos']);
                return;
            }
            
            // Autenticar usuario
            $user = $this->user->authenticate($input['email'], $input['password']);
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                return;
            }
            
            // Generar token
            $token = JWT::generate([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'exp' => time() + (SESSION_LIFETIME)
            ]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => $user
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function profile() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $userData = $this->user->findById($user['user_id']);
            
            if (!$userData) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'user' => $userData
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function updateProfile() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!$this->validateProfileData($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos de perfil inválidos']);
                return;
            }
            
            // Verificar si el email ya existe (excluyendo el usuario actual)
            if ($this->user->emailExists($input['email'], $user['user_id'])) {
                http_response_code(409);
                echo json_encode(['error' => 'El email ya está registrado']);
                return;
            }
            
            // Actualizar perfil
            $this->user->update($user['user_id'], $input);
            $updatedUser = $this->user->findById($user['user_id']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'user' => $updatedUser
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function changePassword() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['current_password']) || !isset($input['new_password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Contraseña actual y nueva contraseña son requeridas']);
                return;
            }
            
            if (strlen($input['new_password']) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'La nueva contraseña debe tener al menos 6 caracteres']);
                return;
            }
            
            // Verificar contraseña actual
            $currentUser = $this->user->findById($user['user_id']);
            if (!password_verify($input['current_password'], $currentUser['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'La contraseña actual es incorrecta']);
                return;
            }
            
            // Actualizar contraseña
            $this->user->updatePassword($user['user_id'], $input['new_password']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    private function validateRegistrationData($data) {
        return isset($data['username']) && 
               isset($data['email']) && 
               isset($data['password']) && 
               isset($data['first_name']) && 
               isset($data['last_name']) &&
               strlen($data['username']) >= 3 &&
               strlen($data['password']) >= 6 &&
               filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    }
    
    private function validateProfileData($data) {
        return isset($data['first_name']) && 
               isset($data['last_name']) && 
               isset($data['email']) &&
               filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    }
}
?>