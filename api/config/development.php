<?php
// Configuración específica para desarrollo
// Este archivo se puede incluir en config.php para desarrollo

// Habilitar modo debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de logs más detallada
ini_set('log_errors', 1);
ini_set('error_log', '../logs/development.log');

// Configuración de sesiones para desarrollo
ini_set('session.cookie_httponly', 0);
ini_set('session.cookie_secure', 0);

// Configuración de CORS más permisiva para desarrollo
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Max-Age: 86400');

// Configuración de caché para desarrollo
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Función para logging en desarrollo
function devLog($message, $type = 'INFO') {
    $logFile = '../logs/development.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Función para debug
function debug($data) {
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

// Configuración de base de datos para desarrollo
define('DB_HOST_DEV', 'localhost');
define('DB_NAME_DEV', 'test_transito_dev');
define('DB_USER_DEV', 'root');
define('DB_PASS_DEV', '');

// Configuración de JWT para desarrollo (clave menos segura)
define('JWT_SECRET_DEV', 'dev_secret_key_2024');

// Configuración de archivos para desarrollo
define('UPLOAD_DIR_DEV', '../assets/img/questions/');
define('MAX_FILE_SIZE_DEV', 10 * 1024 * 1024); // 10MB para desarrollo
define('ALLOWED_EXTENSIONS_DEV', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuración de paginación para desarrollo
define('ITEMS_PER_PAGE_DEV', 5);

// Configuración de sesiones para desarrollo
define('SESSION_LIFETIME_DEV', 7200); // 2 horas para desarrollo

// Función para cambiar a configuración de desarrollo
function useDevelopmentConfig() {
    define('DEVELOPMENT_MODE', true);
    
    // Usar configuraciones de desarrollo
    define('DB_HOST', DB_HOST_DEV);
    define('DB_NAME', DB_NAME_DEV);
    define('DB_USER', DB_USER_DEV);
    define('DB_PASS', DB_PASS_DEV);
    define('JWT_SECRET', JWT_SECRET_DEV);
    define('UPLOAD_DIR', UPLOAD_DIR_DEV);
    define('MAX_FILE_SIZE', MAX_FILE_SIZE_DEV);
    define('ALLOWED_EXTENSIONS', ALLOWED_EXTENSIONS_DEV);
    define('ITEMS_PER_PAGE', ITEMS_PER_PAGE_DEV);
    define('SESSION_LIFETIME', SESSION_LIFETIME_DEV);
    
    devLog('Configuración de desarrollo cargada');
}

// Función para crear base de datos de desarrollo
function createDevelopmentDatabase() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST_DEV, DB_USER_DEV, DB_PASS_DEV);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear base de datos si no existe
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME_DEV . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        devLog('Base de datos de desarrollo creada/verificada');
        return true;
    } catch (PDOException $e) {
        devLog('Error creando base de datos de desarrollo: ' . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Función para limpiar logs
function clearDevelopmentLogs() {
    $logFile = '../logs/development.log';
    if (file_exists($logFile)) {
        unlink($logFile);
        devLog('Logs de desarrollo limpiados');
    }
}

// Función para generar datos de prueba
function generateTestData() {
    if (!defined('DEVELOPMENT_MODE') || !DEVELOPMENT_MODE) {
        return false;
    }
    
    try {
        $db = Database::getInstance();
        
        // Insertar categorías de prueba
        $categories = [
            ['name' => 'Señales de Tránsito', 'description' => 'Preguntas sobre señales de tránsito'],
            ['name' => 'Leyes de Tránsito', 'description' => 'Preguntas sobre leyes y regulaciones'],
            ['name' => 'Mecánica Básica', 'description' => 'Preguntas sobre mecánica del vehículo'],
            ['name' => 'Primeros Auxilios', 'description' => 'Preguntas sobre primeros auxilios'],
            ['name' => 'Conducción Defensiva', 'description' => 'Preguntas sobre técnicas de conducción']
        ];
        
        foreach ($categories as $category) {
            $sql = "INSERT IGNORE INTO categories (name, description) VALUES (:name, :description)";
            $db->query($sql, $category);
        }
        
        // Insertar preguntas de prueba
        $questions = [
            [
                'category_id' => 1,
                'question_text' => '¿Qué significa una señal de STOP?',
                'option_a' => 'Reducir velocidad',
                'option_b' => 'Detenerse completamente',
                'option_c' => 'Acelerar',
                'correct_answer' => 'B',
                'explanation' => 'La señal de STOP requiere detenerse completamente antes de continuar.'
            ],
            [
                'category_id' => 2,
                'question_text' => '¿Cuál es la velocidad máxima en zona urbana?',
                'option_a' => '30 km/h',
                'option_b' => '50 km/h',
                'option_c' => '70 km/h',
                'correct_answer' => 'B',
                'explanation' => 'En zona urbana la velocidad máxima es 50 km/h.'
            ]
        ];
        
        foreach ($questions as $question) {
            $sql = "INSERT IGNORE INTO questions (category_id, question_text, option_a, option_b, option_c, correct_answer, explanation) 
                    VALUES (:category_id, :question_text, :option_a, :option_b, :option_c, :correct_answer, :explanation)";
            $db->query($sql, $question);
        }
        
        devLog('Datos de prueba generados');
        return true;
    } catch (Exception $e) {
        devLog('Error generando datos de prueba: ' . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Función para verificar estado del sistema
function checkSystemStatus() {
    $status = [
        'database' => false,
        'directories' => false,
        'permissions' => false
    ];
    
    // Verificar conexión a base de datos
    try {
        $db = Database::getInstance();
        $status['database'] = true;
    } catch (Exception $e) {
        devLog('Error de conexión a base de datos: ' . $e->getMessage(), 'ERROR');
    }
    
    // Verificar directorios
    $directories = [
        '../logs/',
        '../assets/img/questions/',
        '../templates/',
        '../api/config/'
    ];
    
    $status['directories'] = true;
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            $status['directories'] = false;
            devLog("Directorio no existe: $dir", 'ERROR');
        }
    }
    
    // Verificar permisos
    $status['permissions'] = is_writable('../logs/') && is_writable('../assets/img/questions/');
    
    return $status;
}

// Función para mostrar información de desarrollo
function showDevelopmentInfo() {
    if (!defined('DEVELOPMENT_MODE') || !DEVELOPMENT_MODE) {
        return;
    }
    
    $info = [
        'PHP Version' => PHP_VERSION,
        'Database Host' => DB_HOST,
        'Database Name' => DB_NAME,
        'Upload Directory' => UPLOAD_DIR,
        'Max File Size' => formatBytes(MAX_FILE_SIZE),
        'Session Lifetime' => SESSION_LIFETIME . ' seconds'
    ];
    
    echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">';
    echo '<h3>Development Information</h3>';
    foreach ($info as $key => $value) {
        echo "<strong>$key:</strong> $value<br>";
    }
    echo '</div>';
}

// Función auxiliar para formatear bytes
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Configuración automática para desarrollo
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    useDevelopmentConfig();
}
?>