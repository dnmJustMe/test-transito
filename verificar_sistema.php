<?php
/**
 * Script de Verificación Completa del Sistema
 * Verifica todas las funcionalidades y reporta problemas
 */

echo "=== VERIFICACIÓN COMPLETA DEL SISTEMA ===\n\n";

// 1. Verificar configuración de PHP
echo "1. VERIFICANDO CONFIGURACIÓN DE PHP:\n";
echo "- Versión de PHP: " . phpversion() . "\n";
echo "- Extensión PDO: " . (extension_loaded('pdo') ? '✓ Instalada' : '✗ No instalada') . "\n";
echo "- Extensión PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✓ Instalada' : '✗ No instalada') . "\n";
echo "- Extensión JSON: " . (extension_loaded('json') ? '✓ Instalada' : '✗ No instalada') . "\n";
echo "- Extensión cURL: " . (extension_loaded('curl') ? '✓ Instalada' : '✗ No instalada') . "\n";
echo "- Extensión OpenSSL: " . (extension_loaded('openssl') ? '✓ Instalada' : '✗ No instalada') . "\n\n";

// 2. Verificar archivos críticos
echo "2. VERIFICANDO ARCHIVOS CRÍTICOS:\n";
$criticalFiles = [
    'index.html',
    'assets/js/app.js',
    'assets/css/style.css',
    'api/index.php',
    'api/config/database.php',
    'api/controllers/AuthController.php',
    'api/controllers/QuestionController.php',
    'api/models/User.php',
    'api/models/Question.php',
    'install.php',
    'insert_questions.php',
    '.htaccess'
];

foreach ($criticalFiles as $file) {
    echo "- $file: " . (file_exists($file) ? '✓ Existe' : '✗ No existe') . "\n";
}
echo "\n";

// 3. Verificar permisos de directorios
echo "3. VERIFICANDO PERMISOS DE DIRECTORIOS:\n";
$directories = [
    'assets/img/questions',
    'logs',
    'api'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "- $dir: " . ($writable ? '✓ Escritura permitida' : '✗ Sin permisos de escritura') . "\n";
    } else {
        echo "- $dir: ✗ No existe\n";
    }
}
echo "\n";

// 4. Verificar conexión a base de datos
echo "4. VERIFICANDO CONEXIÓN A BASE DE DATOS:\n";
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=test_transito;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conexión a MySQL exitosa\n";
    
    // Verificar tablas
    $tables = ['users', 'categories', 'questions', 'tests', 'test_sessions', 'user_answers'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        echo "- Tabla $table: " . ($stmt->rowCount() > 0 ? '✓ Existe' : '✗ No existe') . "\n";
    }
    
    // Verificar columnas críticas en questions
    $stmt = $pdo->query("SHOW COLUMNS FROM questions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['nro', 'answer1', 'answer2', 'answer3', 'correct_answer'];
    foreach ($requiredColumns as $column) {
        echo "- Columna questions.$column: " . (in_array($column, $columns) ? '✓ Existe' : '✗ No existe') . "\n";
    }
    
    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM questions");
    $questionCount = $stmt->fetchColumn();
    echo "- Preguntas en BD: $questionCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $categoryCount = $stmt->fetchColumn();
    echo "- Categorías en BD: $categoryCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "- Usuarios en BD: $userCount\n";
    
} catch (PDOException $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Verificar configuración de API
echo "5. VERIFICANDO CONFIGURACIÓN DE API:\n";
if (file_exists('api/config/database.php')) {
    echo "✓ Archivo de configuración de BD existe\n";
} else {
    echo "✗ Archivo de configuración de BD no existe\n";
}

// Verificar .htaccess
if (file_exists('.htaccess')) {
    echo "✓ Archivo .htaccess existe\n";
} else {
    echo "✗ Archivo .htaccess no existe\n";
}
echo "\n";

// 6. Verificar funcionalidades JavaScript
echo "6. VERIFICANDO FUNCIONALIDADES JAVASCRIPT:\n";
if (file_exists('assets/js/app.js')) {
    $jsContent = file_get_contents('assets/js/app.js');
    $requiredFunctions = [
        'showLoginModal',
        'showRegisterModal',
        'login',
        'register',
        'logout',
        'checkAuthStatus',
        'updateUIForLoggedInUser',
        'updateUIForGuest',
        'showSection',
        'loadCategories',
        'startTest',
        'loadHistory',
        'loadProfile',
        'loadUserStats',
        'loadAdminDashboard'
    ];
    
    foreach ($requiredFunctions as $function) {
        echo "- Función $function: " . (strpos($jsContent, "function $function") !== false ? '✓ Existe' : '✗ No existe') . "\n";
    }
} else {
    echo "✗ Archivo app.js no existe\n";
}
echo "\n";

// 7. Verificar estructura HTML
echo "7. VERIFICANDO ESTRUCTURA HTML:\n";
if (file_exists('index.html')) {
    $htmlContent = file_get_contents('index.html');
    $requiredElements = [
        'showLoginModal',
        'showRegisterModal',
        'userMenu',
        'authButtons',
        'user-only',
        'guest-only',
        'admin-only',
        'loginModal',
        'registerModal',
        'addQuestionModal'
    ];
    
    foreach ($requiredElements as $element) {
        echo "- Elemento $element: " . (strpos($htmlContent, $element) !== false ? '✓ Existe' : '✗ No existe') . "\n";
    }
} else {
    echo "✗ Archivo index.html no existe\n";
}
echo "\n";

// 8. Verificar endpoints de API
echo "8. VERIFICANDO ENDPOINTS DE API:\n";
$endpoints = [
    'auth/login',
    'auth/register',
    'auth/profile',
    'categories/with-count',
    'questions/start-test',
    'sessions/by-user',
    'sessions/stats'
];

foreach ($endpoints as $endpoint) {
    $url = "http://localhost/test-transito/api/$endpoint";
    $headers = get_headers($url);
    $status = $headers ? $headers[0] : 'No response';
    echo "- $endpoint: " . (strpos($status, '200') !== false || strpos($status, '404') !== false ? '✓ Accesible' : '✗ No accesible') . " ($status)\n";
}
echo "\n";

// 9. Recomendaciones
echo "9. RECOMENDACIONES:\n";
echo "- Ejecuta 'php install.php' si hay problemas con la BD\n";
echo "- Ejecuta 'php insert_questions.php' si no hay preguntas\n";
echo "- Verifica que Apache y MySQL estén ejecutándose en XAMPP\n";
echo "- Limpia el caché del navegador si hay problemas de JavaScript\n";
echo "- Verifica que mod_rewrite esté habilitado en Apache\n";
echo "- Asegúrate de que la URL base sea correcta en app.js\n\n";

// 10. Resumen
echo "10. RESUMEN:\n";
echo "Para usar el sistema:\n";
echo "1. Accede a: http://localhost/test-transito/\n";
echo "2. Regístrate como nuevo usuario\n";
echo "3. O inicia sesión con admin/admin123\n";
echo "4. Realiza tests y verifica todas las funcionalidades\n\n";

echo "=== VERIFICACIÓN COMPLETADA ===\n";
?>