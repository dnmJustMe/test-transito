<?php
/**
 * Script de prueba de base de datos
 */

echo "🔍 PRUEBA DE BASE DE DATOS\n";
echo "=" . str_repeat("=", 40) . "\n\n";

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=localhost;dbname=test_transito;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conexión a base de datos exitosa\n\n";
    
    // Verificar tabla users
    echo "📋 VERIFICANDO TABLA USERS:\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    echo "\n";
    
    // Verificar usuario admin
    echo "👑 VERIFICANDO USUARIO ADMIN:\n";
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "   ✅ Usuario admin encontrado\n";
        echo "   📧 Email: " . $admin['email'] . "\n";
        echo "   👤 Username: " . $admin['username'] . "\n";
        echo "   👑 Rol: " . $admin['role'] . "\n";
        echo "   ❤️  Vidas: " . $admin['lives'] . "\n";
    } else {
        echo "   ❌ No se encontró usuario admin\n";
    }
    echo "\n";
    
    // Probar inserción de usuario
    echo "👤 PROBANDO INSERCIÓN DE USUARIO:\n";
    $testUser = [
        'username' => 'test_user_' . time(),
        'email' => 'test_' . time() . '@test.com',
        'password' => password_hash('test123', PASSWORD_DEFAULT),
        'first_name' => 'Test',
        'last_name' => 'User',
        'role' => 'user',
        'lives' => 3
    ];
    
    $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, lives) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $testUser['username'],
        $testUser['email'],
        $testUser['password'],
        $testUser['first_name'],
        $testUser['last_name'],
        $testUser['role'],
        $testUser['lives']
    ]);
    
    if ($result) {
        echo "   ✅ Usuario insertado exitosamente\n";
        $userId = $pdo->lastInsertId();
        echo "   🆔 ID: $userId\n";
        
        // Verificar que se puede encontrar
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "   ✅ Usuario encontrado por ID\n";
        } else {
            echo "   ❌ Usuario no encontrado por ID\n";
        }
        
        // Verificar por email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$testUser['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "   ✅ Usuario encontrado por email\n";
        } else {
            echo "   ❌ Usuario no encontrado por email\n";
        }
        
        // Verificar password
        if (password_verify('test123', $user['password'])) {
            echo "   ✅ Password verificado correctamente\n";
        } else {
            echo "   ❌ Error en verificación de password\n";
        }
        
        // Limpiar usuario de prueba
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        echo "   🧹 Usuario de prueba eliminado\n";
        
    } else {
        echo "   ❌ Error al insertar usuario\n";
    }
    echo "\n";
    
    // Verificar configuración del sistema
    echo "⚙️  VERIFICANDO CONFIGURACIÓN:\n";
    $stmt = $pdo->query("SELECT * FROM system_config");
    $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($configs as $config) {
        echo "   - " . $config['key_name'] . ": " . $config['value'] . "\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "🏁 PRUEBA COMPLETADA\n";
?>