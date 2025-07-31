<?php
/**
 * Script de Debugging para la API
 */

// Configuración
$apiBaseUrl = 'http://localhost/test-transito/api/';

echo "🔍 DEBUGGING DE LA API\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Probar endpoint de estadísticas públicas
echo "1. PROBANDO sessions/public-stats:\n";
$url = $apiBaseUrl . 'sessions/public-stats';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    echo "   ✅ Responde correctamente\n";
    echo "   📄 Respuesta: " . substr($response, 0, 200) . "...\n";
} else {
    echo "   ❌ No responde\n";
    echo "   🔗 URL: $url\n";
}
echo "\n";

// 2. Probar registro de usuario
echo "2. PROBANDO auth/register:\n";
$testUser = [
    'username' => 'test_user_' . time(),
    'email' => 'test_' . time() . '@test.com',
    'password' => 'test123',
    'first_name' => 'Test',
    'last_name' => 'User'
];

$url = $apiBaseUrl . 'auth/register';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testUser),
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    echo "   ✅ Responde correctamente\n";
    echo "   📄 Respuesta: " . $response . "\n";
    if ($data && $data['success']) {
        echo "   ✅ Registro exitoso\n";
    } else {
        echo "   ❌ Error en registro: " . ($data['message'] ?? 'Error desconocido') . "\n";
    }
} else {
    echo "   ❌ No responde\n";
    echo "   🔗 URL: $url\n";
    echo "   📄 Datos enviados: " . json_encode($testUser) . "\n";
}
echo "\n";

// 3. Probar login de usuario
echo "3. PROBANDO auth/login:\n";
$loginData = [
    'email' => $testUser['email'],
    'password' => $testUser['password']
];

$url = $apiBaseUrl . 'auth/login';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($loginData),
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    echo "   ✅ Responde correctamente\n";
    echo "   📄 Respuesta: " . $response . "\n";
    if ($data && $data['success']) {
        echo "   ✅ Login exitoso\n";
        echo "   🔑 Token: " . (isset($data['data']['token']) ? 'Sí' : 'No') . "\n";
    } else {
        echo "   ❌ Error en login: " . ($data['message'] ?? 'Error desconocido') . "\n";
    }
} else {
    echo "   ❌ No responde\n";
    echo "   🔗 URL: $url\n";
    echo "   📄 Datos enviados: " . json_encode($loginData) . "\n";
}
echo "\n";

// 4. Probar login de admin
echo "4. PROBANDO login de admin:\n";
$adminData = [
    'email' => 'admin@test-transito.com',
    'password' => 'admin123'
];

$url = $apiBaseUrl . 'auth/login';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($adminData),
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    echo "   ✅ Responde correctamente\n";
    echo "   📄 Respuesta: " . $response . "\n";
    if ($data && $data['success']) {
        echo "   ✅ Login de admin exitoso\n";
        echo "   👑 Rol: " . ($data['data']['user']['role'] ?? 'No especificado') . "\n";
    } else {
        echo "   ❌ Error en login de admin: " . ($data['message'] ?? 'Error desconocido') . "\n";
    }
} else {
    echo "   ❌ No responde\n";
    echo "   🔗 URL: $url\n";
    echo "   📄 Datos enviados: " . json_encode($adminData) . "\n";
}
echo "\n";

// 5. Verificar base de datos
echo "5. VERIFICANDO BASE DE DATOS:\n";
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=test_transito;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Verificar usuario admin
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "   ✅ Usuario admin encontrado\n";
        echo "   📧 Email: " . $admin['email'] . "\n";
        echo "   👤 Username: " . $admin['username'] . "\n";
        echo "   👑 Rol: " . $admin['role'] . "\n";
    } else {
        echo "   ❌ No se encontró usuario admin\n";
    }
    
    // Verificar estructura de tabla users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   📋 Columnas de tabla users:\n";
    foreach ($columns as $column) {
        echo "      - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error de base de datos: " . $e->getMessage() . "\n";
}
echo "\n";

echo "🏁 DEBUGGING COMPLETADO\n";
?>