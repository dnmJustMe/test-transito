<?php
/**
 * Controlador de Autenticación
 */

require_once 'models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function register() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['username']) || !isset($input['email']) || !isset($input['password']) || 
                !isset($input['first_name']) || !isset($input['last_name'])) {
                return $this->sendResponse(false, 'Todos los campos son requeridos');
            }
            
            // Validar email
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->sendResponse(false, 'Email inválido');
            }
            
            // Validar contraseña
            if (strlen($input['password']) < 6) {
                return $this->sendResponse(false, 'La contraseña debe tener al menos 6 caracteres');
            }
            
            // Verificar si el email ya existe
            if ($this->userModel->emailExists($input['email'])) {
                return $this->sendResponse(false, 'El email ya está registrado');
            }
            
            // Verificar si el username ya existe
            if ($this->userModel->usernameExists($input['username'])) {
                return $this->sendResponse(false, 'El nombre de usuario ya está registrado');
            }
            
            // Crear usuario
            $userId = $this->userModel->create($input);
            
            if ($userId) {
                return $this->sendResponse(true, 'Usuario registrado exitosamente');
            } else {
                return $this->sendResponse(false, 'Error al registrar usuario');
            }
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function login() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['email']) || !isset($input['password'])) {
                return $this->sendResponse(false, 'Email y contraseña son requeridos');
            }
            
            // Buscar usuario
            $user = $this->userModel->findByEmail($input['email']);
            if (!$user) {
                return $this->sendResponse(false, 'Credenciales inválidas');
            }
            
            // Verificar contraseña
            if (!password_verify($input['password'], $user['password'])) {
                return $this->sendResponse(false, 'Credenciales inválidas');
            }
            
            // Generar token JWT
            $token = $this->generateJWT($user);
            
            // Obtener vidas actualizadas
            $userWithLives = $this->userModel->getLivesWithRegeneration($user['id']);
            
            // Remover contraseña de la respuesta
            unset($user['password']);
            unset($userWithLives['password']);
            
            return $this->sendResponse(true, 'Login exitoso', [
                'token' => $token,
                'user' => $userWithLives
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function profile() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            // Obtener vidas actualizadas
            $userWithLives = $this->userModel->getLivesWithRegeneration($user['id']);
            unset($userWithLives['password']);
            
            return $this->sendResponse(true, 'Perfil obtenido', $userWithLives);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function updateProfile() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['username']) || !isset($input['email']) || 
                !isset($input['first_name']) || !isset($input['last_name'])) {
                return $this->sendResponse(false, 'Todos los campos son requeridos');
            }
            
            // Validar email
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->sendResponse(false, 'Email inválido');
            }
            
            // Verificar si el email ya existe (excluyendo el usuario actual)
            $existingUser = $this->userModel->findByEmail($input['email']);
            if ($existingUser && $existingUser['id'] != $user['id']) {
                return $this->sendResponse(false, 'El email ya está registrado');
            }
            
            // Actualizar usuario
            $this->userModel->update($user['id'], $input);
            
            // Obtener usuario actualizado
            $updatedUser = $this->userModel->findById($user['id']);
            unset($updatedUser['password']);
            
            return $this->sendResponse(true, 'Perfil actualizado exitosamente', $updatedUser);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function getLives() {
        try {
            $user = $this->getCurrentUser();
            if (!$user) {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $userWithLives = $this->userModel->getLivesWithRegeneration($user['id']);
            
            return $this->sendResponse(true, 'Vidas obtenidas', [
                'lives' => $userWithLives['lives'],
                'can_take_test' => $this->userModel->canTakeTest($user['id']),
                'last_life_lost' => $userWithLives['last_life_lost']
            ]);
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    public function addLives() {
        try {
            $user = $this->getCurrentUser();
            if (!$user || $user['role'] !== 'admin') {
                return $this->sendResponse(false, 'No autorizado', null, 401);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['user_id']) || !isset($input['lives'])) {
                return $this->sendResponse(false, 'ID de usuario y número de vidas son requeridos');
            }
            
            $targetUser = $this->userModel->findById($input['user_id']);
            if (!$targetUser) {
                return $this->sendResponse(false, 'Usuario no encontrado');
            }
            
            $newLives = min(3, $targetUser['lives'] + $input['lives']);
            $this->userModel->updateLives($input['user_id'], $newLives);
            
            return $this->sendResponse(true, 'Vidas agregadas exitosamente');
            
        } catch (Exception $e) {
            return $this->sendResponse(false, 'Error: ' . $e->getMessage());
        }
    }
    
    private function generateJWT($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + (60 * 60 * 24) // 24 horas
        ]);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, 'your-secret-key', true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    private function verifyJWT($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = $parts[2];
        
        $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], 'your-secret-key', true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));
        
        if ($signature !== $expectedSignature) {
            return false;
        }
        
        $payloadData = json_decode($payload, true);
        if ($payloadData['exp'] < time()) {
            return false;
        }
        
        return $payloadData;
    }
    
    private function getCurrentUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }
        
        $token = $matches[1];
        $payload = $this->verifyJWT($token);
        
        if (!$payload) {
            return null;
        }
        
        return $this->userModel->findById($payload['user_id']);
    }
    
    private function sendResponse($success, $message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
}
?>