<?php
// Archivo temporal para debugging - ELIMINAR EN PRODUCCIÓN

echo "<h2>🔧 Información del Sistema</h2>";

// Información PHP
echo "<h3>PHP</h3>";
echo "Versión: " . phpversion() . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Verificar archivos clave
echo "<h3>Archivos del Sistema</h3>";
$files = [
    'config/config.php',
    'public/index.php',
    'classes/Auth.php',
    '.htaccess'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NO existe<br>";
    }
}

// Verificar carpetas
echo "<h3>Carpetas del Sistema</h3>";
$folders = [
    'config',
    'public',
    'classes',
    'models',
    'api',
    'templates',
    'logs',
    'uploads'
];

foreach ($folders as $folder) {
    if (is_dir($folder)) {
        echo "✅ $folder/ existe<br>";
    } else {
        echo "❌ $folder/ NO existe<br>";
    }
}

// Verificar extensiones PHP
echo "<h3>Extensiones PHP</h3>";
$extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext cargada<br>";
    } else {
        echo "❌ $ext NO cargada<br>";
    }
}

// Intentar cargar config
echo "<h3>Prueba de Configuración</h3>";
try {
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "✅ config.php cargado correctamente<br>";
        echo "SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'NO DEFINIDO') . "<br>";
        echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NO DEFINIDO') . "<br>";
    } else {
        echo "❌ config.php no encontrado<br>";
    }
} catch (Exception $e) {
    echo "❌ Error cargando config.php: " . $e->getMessage() . "<br>";
}

// Mostrar errores PHP
echo "<h3>Últimos Errores PHP</h3>";
if (file_exists('logs/error.log')) {
    $errors = file_get_contents('logs/error.log');
    echo "<pre>" . htmlspecialchars(substr($errors, -1000)) . "</pre>";
} else {
    echo "No hay archivo de errores o no se han registrado errores.<br>";
}

echo "<br><strong>⚠️ ELIMINAR ESTE ARCHIVO EN PRODUCCIÓN</strong>";
?>