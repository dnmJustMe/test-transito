<?php
echo "PHP funciona correctamente!<br>";
echo "Versión PHP: " . phpversion() . "<br>";
echo "Fecha actual: " . date('Y-m-d H:i:s') . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";

// Verificar si existe config.php
if (file_exists('config/config.php')) {
    echo "✅ config/config.php existe<br>";
} else {
    echo "❌ config/config.php NO existe<br>";
}

// Verificar si existe public/index.php
if (file_exists('public/index.php')) {
    echo "✅ public/index.php existe<br>";
} else {
    echo "❌ public/index.php NO existe<br>";
}

// Verificar permisos de escritura
if (is_writable('.')) {
    echo "✅ Directorio tiene permisos de escritura<br>";
} else {
    echo "❌ Directorio NO tiene permisos de escritura<br>";
}

// Verificar extensiones PHP necesarias
$extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión $ext cargada<br>";
    } else {
        echo "❌ Extensión $ext NO cargada<br>";
    }
}
?>