<?php
// Configuración principal del sistema
define('BASE_URL', 'http://localhost/test-transito/');
define('API_URL', BASE_URL . 'api/');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'test_transito');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de sesiones
define('SESSION_NAME', 'test_transito_session');
define('SESSION_LIFETIME', 3600); // 1 hora

// Configuración de seguridad
define('JWT_SECRET', 'tu_clave_secreta_muy_segura_aqui_2024');
define('PASSWORD_COST', 12);

// Configuración de archivos
define('UPLOAD_DIR', '../assets/img/questions/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

// Función para obtener la URL base
function getBaseUrl() {
    return BASE_URL;
}

// Función para obtener la URL de la API
function getApiUrl() {
    return API_URL;
}
?>