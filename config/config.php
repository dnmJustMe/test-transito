<?php
/**
 * Configuración General del Sistema
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiar a 1 en desarrollo
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Zona horaria
date_default_timezone_set('America/Havana');

// Configuraciones del sitio
define('SITE_NAME', 'Test Licencia Cuba');
define('SITE_URL', 'http://localhost/test-licencia-cuba'); // Cambiar por tu dominio
define('API_VERSION', 'v1');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('IMAGES_PATH', UPLOAD_PATH . '/images');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('LOGS_PATH', ROOT_PATH . '/logs');

// URLs del sistema
define('BASE_URL', SITE_URL);
define('API_URL', BASE_URL . '/api/' . API_VERSION);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuración de seguridad
define('JWT_SECRET', 'tu_clave_secreta_muy_segura_aqui'); // Cambiar en producción
define('BCRYPT_COST', 12);
define('SESSION_LIFETIME', 7200); // 2 horas en segundos

// Configuración de paginación
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Configuración del test
define('MIN_QUESTIONS_PER_TEST', 5);
define('MAX_QUESTIONS_PER_TEST', 100);
define('DEFAULT_PASSING_SCORE', 80);

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Crear directorios necesarios si no existen
$directories = [LOGS_PATH, UPLOAD_PATH, IMAGES_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/classes/',
        ROOT_PATH . '/models/',
        ROOT_PATH . '/controllers/',
        ROOT_PATH . '/middleware/',
        ROOT_PATH . '/utils/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Incluir archivos de configuración
require_once __DIR__ . '/database.php';

// Función para obtener configuración del sistema
function getSystemConfig($key, $default = null) {
    static $config = null;
    
    if ($config === null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
            $config = [];
            while ($row = $stmt->fetch()) {
                $config[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error loading system config: " . $e->getMessage());
            $config = [];
        }
    }
    
    return isset($config[$key]) ? $config[$key] : $default;
}

// Función para logging
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents(LOGS_PATH . '/app.log', $logMessage, FILE_APPEND | LOCK_EX);
}

// Función para generar CSRF token
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Función para verificar CSRF token
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}