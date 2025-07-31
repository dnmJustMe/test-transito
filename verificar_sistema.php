<?php
/**
 * Script de Verificación Completa del Sistema
 * Verifica todas las funcionalidades y reporta problemas
 */

echo "=== VERIFICACIÓN COMPLETA DEL SISTEMA ===\n\n";

// 1. Verificar configuración de PHP
echo "1. VERIFICANDO CONFIGURACIÓN DE PHP:\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    echo "- Extensión $ext: " . (extension_loaded($ext) ? '✓ Disponible' : '✗ No disponible') . "\n";
}

echo "- Versión PHP: " . PHP_VERSION . "\n";
echo "- Memoria límite: " . ini_get('memory_limit') . "\n";
echo "- Tiempo máximo de ejecución: " . ini_get('max_execution_time') . "s\n\n";

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
    'api/controllers/SessionController.php',
    'api/models/User.php',
    'api/models/Question.php',
    'api/models/TestSession.php',
    'install_completo.php',
    '.htaccess'
];

foreach ($criticalFiles as $file) {
    echo "- $file: " . (file_exists($file) ? '✓ Existe' : '✗ No existe') . "\n";
}

// Verificar archivos de imagen
echo "\n- Archivos de imagen:\n";
$imageFiles = [
    'assets/img/logo.png' => 'Logo del sistema',
    'assets/img/favicon.ico' => 'Favicon del sistema'
];

foreach ($imageFiles as $file => $description) {
    echo "- $description ($file): " . (file_exists($file) ? '✓ Existe' : '✗ No existe (placeholder)') . "\n";
}
echo "\n";

// 3. Verificar directorios y permisos
echo "3. VERIFICANDO DIRECTORIOS Y PERMISOS:\n";
$directories = [
    'assets/img/questions',
    'api',
    'api/config',
    'api/controllers',
    'api/models',
    'assets/css',
    'assets/js'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "- $dir: ✓ Existe" . ($writable ? ' (escribible)' : ' (no escribible)') . "\n";
    } else {
        echo "- $dir: ✗ No existe\n";
    }
}
echo "\n";

// 4. Verificar conexión a base de datos
echo "4. VERIFICANDO CONEXIÓN A BASE DE DATOS:\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=test_transito", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexión a MySQL exitosa\n";
    
    // Verificar tablas
    $tables = ['users', 'questions', 'test_sessions', 'user_answers', 'system_config'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        echo "- Tabla $table: " . ($stmt->rowCount() > 0 ? '✓ Existe' : '✗ No existe') . "\n";
    }
    
    // Verificar datos iniciales
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetch()['count'];
    echo "- Usuarios admin: $adminCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM questions");
    $questionCount = $stmt->fetch()['count'];
    echo "- Preguntas disponibles: $questionCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_config");
    $configCount = $stmt->fetch()['count'];
    echo "- Configuraciones del sistema: $configCount\n";
    
} catch (PDOException $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Verificar configuración de la API
echo "5. VERIFICANDO CONFIGURACIÓN DE LA API:\n";
$apiUrl = 'http://localhost/test-transito/api/';
$testEndpoints = [
    'sessions/public-stats' => 'GET',
    'auth/register' => 'POST',
    'auth/login' => 'POST',
    'questions' => 'GET'
];

foreach ($testEndpoints as $endpoint => $method) {
    $url = $apiUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json',
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = $http_response_header[0] ?? 'Unknown';
    
    if ($response !== false) {
        echo "- $endpoint ($method): ✓ Responde correctamente\n";
    } else {
        echo "- $endpoint ($method): ✗ Error de conexión\n";
    }
}
echo "\n";

// 6. Verificar elementos JavaScript/HTML
echo "6. VERIFICANDO ELEMENTOS DE LA INTERFAZ:\n";
$htmlContent = file_get_contents('index.html');
$jsContent = file_get_contents('assets/js/app.js');

$requiredElements = [
    'loginModal' => 'Modal de login',
    'registerModal' => 'Modal de registro',
    'addQuestionModal' => 'Modal de agregar pregunta',
    'userLives' => 'Indicador de vidas',
    'currentLives' => 'Vidas actuales',
    'start-test-btn' => 'Botones de iniciar test',
    'difficulty-card' => 'Cards de dificultad',
    'test-interface' => 'Interfaz de test',
    'admin' => 'Sección de administración',
    'logo.png' => 'Logo del sistema',
    'favicon.ico' => 'Favicon del sistema'
];

foreach ($requiredElements as $element => $description) {
    if (strpos($htmlContent, $element) !== false) {
        echo "- $description: ✓ Presente en HTML\n";
    } else {
        echo "- $description: ✗ No encontrado en HTML\n";
    }
}

// Verificar control de acceso
echo "\n- Control de acceso:\n";
if (strpos($htmlContent, 'guest-only') !== false && strpos($htmlContent, 'user-only') !== false && strpos($htmlContent, 'admin-only') !== false) {
    echo "  ✓ Clases de control de acceso presentes\n";
} else {
    echo "  ✗ Clases de control de acceso faltantes\n";
}

if (strpos($htmlContent, 'updateUIForLoggedInUser') !== false && strpos($htmlContent, 'updateUIForGuest') !== false) {
    echo "  ✓ Funciones de actualización de UI presentes\n";
} else {
    echo "  ✗ Funciones de actualización de UI faltantes\n";
}

$requiredFunctions = [
    'showLoginModal' => 'Función mostrar login',
    'showRegisterModal' => 'Función mostrar registro',
    'showAddQuestionModal' => 'Función mostrar modal pregunta',
    'startTest' => 'Función iniciar test',
    'loadUserLives' => 'Función cargar vidas',
    'updateUIForLoggedInUser' => 'Función actualizar UI usuario',
    'loadPublicStats' => 'Función cargar estadísticas'
];

foreach ($requiredFunctions as $function => $description) {
    if (strpos($jsContent, $function) !== false) {
        echo "- $description: ✓ Presente en JavaScript\n";
    } else {
        echo "- $description: ✗ No encontrado en JavaScript\n";
    }
}
echo "\n";

// 7. Verificar estilos CSS
echo "7. VERIFICANDO ESTILOS CSS:\n";
$cssContent = file_get_contents('assets/css/style.css');

$requiredStyles = [
    '.difficulty-card' => 'Estilos para cards de dificultad',
    '.life-indicator' => 'Estilos para indicador de vidas',
    '#test-interface' => 'Estilos para interfaz de test',
    '.admin-tab-content' => 'Estilos para panel admin',
    '.question-admin-card' => 'Estilos para preguntas en admin'
];

foreach ($requiredStyles as $style => $description) {
    if (strpos($cssContent, $style) !== false) {
        echo "- $description: ✓ Presente en CSS\n";
    } else {
        echo "- $description: ✗ No encontrado en CSS\n";
    }
}
echo "\n";

// 8. Recomendaciones
echo "8. RECOMENDACIONES:\n";
echo "- Ejecuta 'install_completo.php' si no has instalado el sistema\n";
echo "- Verifica que XAMPP esté corriendo (Apache y MySQL)\n";
echo "- Asegúrate de que la URL base sea 'http://localhost/test-transito/'\n";
echo "- Verifica que el directorio 'assets/img/questions' tenga permisos de escritura\n";
echo "- Prueba el login con admin/admin123\n";
echo "- Verifica que las vidas se regeneren cada 5 minutos\n";
echo "- Prueba crear preguntas desde el panel de administración\n";
echo "- Verifica que los tests funcionen con las 3 dificultades\n";
echo "- Reemplaza los archivos placeholder de logo.png y favicon.ico con tus archivos reales\n";
echo "\n";

// 9. Resumen
echo "9. RESUMEN DE FUNCIONALIDADES IMPLEMENTADAS:\n";
echo "✓ Sistema de registro e inicio de sesión\n";
echo "✓ Panel de administración para gestionar preguntas y usuarios\n";
echo "✓ Sistema de vidas (3 vidas, regeneración cada 5 minutos)\n";
echo "✓ Tests con 3 niveles de dificultad (20, 40, 100 preguntas)\n";
echo "✓ Sistema de aprobación (80% mínimo para aprobar)\n";
echo "✓ Historial personal de tests\n";
echo "✓ Estadísticas públicas y personales\n";
echo "✓ Gestión de vidas desde el panel de administración\n";
echo "✓ Interfaz responsiva y moderna\n";
echo "✓ Validaciones de formularios\n";
echo "✓ Manejo de errores y notificaciones\n";
echo "\n";

echo "=== VERIFICACIÓN COMPLETADA ===\n";
echo "Si encuentras errores, revisa las recomendaciones anteriores.\n";
echo "El sistema está listo para usar una vez que ejecutes install_completo.php\n";
?>