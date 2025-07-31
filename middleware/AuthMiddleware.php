<?php
/**
 * Middleware de Autenticación para API
 */

class AuthMiddleware {
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Verificar autenticación requerida
     */
    public function requireAuth() {
        if (!$this->auth->isLoggedIn()) {
            ApiResponse::unauthorized('Debe iniciar sesión para acceder a este recurso');
        }
        
        return $this->auth->getCurrentUser();
    }
    
    /**
     * Verificar permisos de administrador
     */
    public function requireAdmin() {
        $user = $this->requireAuth();
        
        if (!$this->auth->isAdmin()) {
            ApiResponse::forbidden('Se requieren permisos de administrador');
        }
        
        return $user;
    }
    
    /**
     * Verificar que el usuario puede acceder a sus propios datos
     */
    public function requireOwnerOrAdmin($userId) {
        $user = $this->requireAuth();
        
        if ($user['id'] != $userId && !$this->auth->isAdmin()) {
            ApiResponse::forbidden('No tienes permisos para acceder a este recurso');
        }
        
        return $user;
    }
    
    /**
     * Autenticación opcional (para endpoints públicos con funcionalidad extra para usuarios logueados)
     */
    public function optionalAuth() {
        if ($this->auth->isLoggedIn()) {
            return $this->auth->getCurrentUser();
        }
        
        return null;
    }
    
    /**
     * Verificar token CSRF para operaciones críticas
     */
    public function requireCSRF() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
        
        if (!$token || !verifyCSRFToken($token)) {
            ApiResponse::forbidden('Token CSRF inválido o faltante');
        }
    }
    
    /**
     * Rate limiting básico por IP
     */
    public function rateLimit($maxRequests = 60, $timeWindow = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$ip}";
        
        // Usar archivo temporal para almacenar contadores (en producción usar Redis/Memcached)
        $rateLimitFile = LOGS_PATH . '/rate_limits.json';
        $rateLimits = [];
        
        if (file_exists($rateLimitFile)) {
            $rateLimits = json_decode(file_get_contents($rateLimitFile), true) ?: [];
        }
        
        $now = time();
        $windowStart = $now - $timeWindow;
        
        // Limpiar entradas expiradas
        foreach ($rateLimits as $k => $data) {
            if ($data['timestamp'] < $windowStart) {
                unset($rateLimits[$k]);
            }
        }
        
        // Contar requests en la ventana actual
        $currentRequests = 0;
        foreach ($rateLimits as $k => $data) {
            if (strpos($k, $ip) === 10 && $data['timestamp'] >= $windowStart) { // 10 = strlen("rate_limit_")
                $currentRequests++;
            }
        }
        
        if ($currentRequests >= $maxRequests) {
            header('X-RateLimit-Limit: ' . $maxRequests);
            header('X-RateLimit-Remaining: 0');
            header('X-RateLimit-Reset: ' . ($now + $timeWindow));
            ApiResponse::error('Demasiadas solicitudes. Intente más tarde.', 429);
        }
        
        // Registrar request actual
        $rateLimits[$key . '_' . $now] = ['timestamp' => $now];
        file_put_contents($rateLimitFile, json_encode($rateLimits), LOCK_EX);
        
        // Headers informativos
        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: ' . ($maxRequests - $currentRequests - 1));
        header('X-RateLimit-Reset: ' . ($now + $timeWindow));
    }
    
    /**
     * Verificar método HTTP permitido
     */
    public function requireMethod($allowedMethods) {
        $method = $_SERVER['REQUEST_METHOD'];
        $allowedMethods = is_array($allowedMethods) ? $allowedMethods : [$allowedMethods];
        
        if (!in_array($method, $allowedMethods)) {
            header('Allow: ' . implode(', ', $allowedMethods));
            ApiResponse::error('Método no permitido', 405);
        }
    }
    
    /**
     * Validar origen de la solicitud (CORS básico)
     */
    public function validateOrigin($allowedOrigins = []) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Si no se especifican orígenes, permitir todos (desarrollo)
        if (empty($allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
            return;
        }
        
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        } else {
            ApiResponse::forbidden('Origen no permitido');
        }
        
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
    }
    
    /**
     * Manejar requests OPTIONS para CORS
     */
    public function handleCors() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->validateOrigin();
            http_response_code(200);
            exit;
        }
    }
}