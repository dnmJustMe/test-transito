<?php
/**
 * Script de prueba para verificar el sistema
 */

echo "=== PRUEBA DEL SISTEMA ===\n\n";

// 1. Probar conexión a la base de datos
echo "1. Probando conexión a la base de datos...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=test_transito", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexión exitosa\n";
} catch (Exception $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Verificar tablas
echo "\n2. Verificando tablas...\n";
$tables = ['users', 'categories', 'questions', 'tests', 'test_sessions', 'user_answers', 'system_config'];
foreach ($tables as $table) {
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $result->fetchColumn();
        echo "✓ Tabla $table: $count registros\n";
    } catch (Exception $e) {
        echo "✗ Error en tabla $table: " . $e->getMessage() . "\n";
    }
}

// 3. Verificar usuario admin
echo "\n3. Verificando usuario administrador...\n";
try {
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "✓ Usuario admin encontrado\n";
        echo "  - Usuario: {$admin['username']}\n";
        echo "  - Email: {$admin['email']}\n";
        echo "  - Rol: {$admin['role']}\n";
    } else {
        echo "✗ Usuario admin no encontrado\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// 4. Verificar categorías
echo "\n4. Verificando categorías...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE deleted_at IS NULL");
    $count = $stmt->fetchColumn();
    echo "✓ Categorías: $count registros\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT name, question_count FROM categories WHERE deleted_at IS NULL LIMIT 5");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as $cat) {
            echo "  - {$cat['name']}: {$cat['question_count']} preguntas\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// 5. Verificar preguntas
echo "\n5. Verificando preguntas...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM questions WHERE deleted_at IS NULL");
    $count = $stmt->fetchColumn();
    echo "✓ Preguntas: $count registros\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, nro, question_text, category_id FROM questions WHERE deleted_at IS NULL LIMIT 3");
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($questions as $q) {
            echo "  - Pregunta {$q['id']} (NRO: {$q['nro']}): " . substr($q['question_text'], 0, 50) . "...\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// 6. Verificar estructura de preguntas
echo "\n6. Verificando estructura de preguntas...\n";
try {
    $stmt = $pdo->query("DESCRIBE questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $requiredColumns = ['id', 'category_id', 'nro', 'question_text', 'answer1', 'answer2', 'answer3', 'correct_answer', 'article_reference', 'image_path'];
    
    $foundColumns = [];
    foreach ($columns as $col) {
        $foundColumns[] = $col['Field'];
    }
    
    $missing = array_diff($requiredColumns, $foundColumns);
    if (empty($missing)) {
        echo "✓ Estructura de preguntas correcta\n";
    } else {
        echo "✗ Columnas faltantes: " . implode(', ', $missing) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// 7. Verificar directorios
echo "\n7. Verificando directorios...\n";
$dirs = [
    'assets/img/questions' => 'Directorio de imágenes',
    'logs' => 'Directorio de logs',
    'api' => 'Directorio de API',
    'assets/css' => 'Directorio de CSS',
    'assets/js' => 'Directorio de JavaScript'
];

foreach ($dirs as $dir => $description) {
    if (is_dir($dir)) {
        echo "✓ $description: $dir\n";
    } else {
        echo "✗ $description: $dir (no existe)\n";
    }
}

// 8. Verificar archivos principales
echo "\n8. Verificando archivos principales...\n";
$files = [
    'index.html' => 'Archivo principal',
    'install.php' => 'Script de instalación',
    'insert_questions.php' => 'Script de preguntas',
    'api/index.php' => 'API principal',
    'api/config/config.php' => 'Configuración',
    'assets/css/style.css' => 'Estilos',
    'assets/js/app.js' => 'JavaScript'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description: $file\n";
    } else {
        echo "✗ $description: $file (no existe)\n";
    }
}

// 9. Probar API endpoints
echo "\n9. Probando endpoints de API...\n";
$baseUrl = 'http://localhost/test-transito/api/';

$endpoints = [
    'categories' => 'Categorías',
    'questions' => 'Preguntas',
    'auth/login' => 'Login'
];

foreach ($endpoints as $endpoint => $description) {
    $url = $baseUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✓ $description: $endpoint\n";
    } else {
        echo "✗ $description: $endpoint (error)\n";
    }
}

echo "\n=== PRUEBA COMPLETADA ===\n";
echo "Si todos los checks muestran ✓, el sistema está funcionando correctamente.\n";
echo "Si hay ✗, revisa los errores antes de usar el sistema.\n\n";
?>