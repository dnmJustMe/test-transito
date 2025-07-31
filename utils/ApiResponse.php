<?php
/**
 * Clase para respuestas estandarizadas de API
 */

class ApiResponse {
    
    /**
     * Enviar respuesta exitosa
     */
    public static function success($data = null, $message = 'Success', $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c'),
            'code' => $code
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Enviar respuesta de error
     */
    public static function error($message = 'Error', $code = 400, $errors = null) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('c'),
            'code' => $code
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Respuesta de validación fallida
     */
    public static function validationError($errors, $message = 'Datos de entrada no válidos') {
        self::error($message, 422, $errors);
    }
    
    /**
     * Respuesta de no autorizado
     */
    public static function unauthorized($message = 'No autorizado') {
        self::error($message, 401);
    }
    
    /**
     * Respuesta de prohibido
     */
    public static function forbidden($message = 'Acceso prohibido') {
        self::error($message, 403);
    }
    
    /**
     * Respuesta de no encontrado
     */
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }
    
    /**
     * Respuesta de conflicto
     */
    public static function conflict($message = 'Conflicto en la solicitud') {
        self::error($message, 409);
    }
    
    /**
     * Respuesta de error interno del servidor
     */
    public static function serverError($message = 'Error interno del servidor') {
        self::error($message, 500);
    }
    
    /**
     * Respuesta paginada
     */
    public static function paginated($data, $total, $page, $limit, $message = 'Success') {
        $response = [
            'items' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ]
        ];
        
        self::success($response, $message);
    }
    
    /**
     * Validar Content-Type JSON
     */
    public static function validateJsonRequest() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') === false) {
            self::error('Content-Type debe ser application/json', 400);
        }
    }
    
    /**
     * Obtener datos JSON del request
     */
    public static function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::error('JSON no válido: ' . json_last_error_msg(), 400);
        }
        
        return $data ?: [];
    }
    
    /**
     * Validar parámetros requeridos
     */
    public static function validateRequired($data, $required) {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            self::validationError($missing, 'Campos requeridos faltantes: ' . implode(', ', $missing));
        }
    }
    
    /**
     * Sanitizar entrada
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * Validar paginación
     */
    public static function validatePagination($page = 1, $limit = 20) {
        $page = max(1, intval($page));
        $limit = max(1, min(MAX_PAGE_SIZE, intval($limit)));
        
        return [$page, $limit];
    }
}