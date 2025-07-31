<?php
/**
 * Script de instalación para el sistema de test de tránsito
 * Este script configura la base de datos y soluciona problemas comunes
 */

// Configuración
$config = [
    'db_host' => 'localhost',
    'db_name' => 'test_transito',
    'db_user' => 'root',
    'db_pass' => '',
    'admin_password' => 'admin123'
];

echo "=== INSTALADOR DEL SISTEMA DE TEST DE TRÁNSITO ===\n\n";

try {
    // 1. Conectar a MySQL
    echo "1. Conectando a MySQL...\n";
    $pdo = new PDO("mysql:host={$config['db_host']}", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexión exitosa\n\n";

    // 2. Crear base de datos si no existe
    echo "2. Creando base de datos...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['db_name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de datos creada/verificada\n\n";

    // 3. Seleccionar la base de datos
    $pdo->exec("USE {$config['db_name']}");

    // 4. Crear tablas
    echo "3. Creando tablas...\n";
    
    // Tabla users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL
    )");
    
    // Tabla categories
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        question_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL
    )");
    
    // Tabla questions
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        question_text TEXT NOT NULL,
        answer1 VARCHAR(255) NOT NULL,
        answer2 VARCHAR(255) NOT NULL,
        answer3 VARCHAR(255) NOT NULL,
        correct_answer INT NOT NULL CHECK (correct_answer IN (1, 2, 3)),
        article_reference VARCHAR(20),
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at TIMESTAMP NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");
    
    // Tabla tests
    $pdo->exec("CREATE TABLE IF NOT EXISTS tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        category_id INT,
        total_questions INT NOT NULL,
        correct_answers INT DEFAULT 0,
        score DECIMAL(5,2) DEFAULT 0,
        passed BOOLEAN DEFAULT 0,
        time_taken INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");
    
    // Tabla test_sessions
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        test_id INT NOT NULL,
        status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
        start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        end_time TIMESTAMP NULL,
        score DECIMAL(5,2) DEFAULT 0,
        total_questions INT DEFAULT 0,
        correct_answers INT DEFAULT 0,
        time_taken INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
    )");
    
    // Tabla user_answers
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        question_id INT NOT NULL,
        selected_answer INT NOT NULL CHECK (selected_answer IN (1, 2, 3)),
        is_correct BOOLEAN DEFAULT 0,
        time_taken INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES test_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    )");
    
    // Tabla system_config
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(50) UNIQUE NOT NULL,
        config_value TEXT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    echo "✓ Tablas creadas\n\n";

    // 5. Crear usuario admin
    echo "4. Creando usuario administrador...\n";
    $adminPassword = password_hash($config['admin_password'], PASSWORD_DEFAULT);
    
    // Eliminar admin existente
    $pdo->exec("DELETE FROM users WHERE username = 'admin' OR email = 'admin@test-transito.com'");
    
    // Crear nuevo admin
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@test-transito.com', $adminPassword, 'Administrador', 'Sistema', 'admin', 1]);
    
    echo "✓ Usuario admin creado\n";
    echo "  Usuario: admin\n";
    echo "  Contraseña: {$config['admin_password']}\n\n";

    // 6. Insertar categorías por defecto
    echo "5. Insertando categorías por defecto...\n";
    $categories = [
        ['Normas Generales', 'Normas generales de circulación y comportamiento en la vía'],
        ['Señales y Semáforos', 'Señales de tránsito, semáforos y marcas viales'],
        ['Prioridades y Pasos', 'Prioridades de paso, intersecciones y pasos a nivel'],
        ['Adelantamientos', 'Normas sobre adelantamientos y maniobras'],
        ['Vehículos y Carga', 'Especificaciones de vehículos y transporte de carga'],
        ['Circulación Especial', 'Normas para circulación de vehículos especiales'],
        ['Estacionamiento', 'Normas sobre estacionamiento y parada'],
        ['Velocidades', 'Límites de velocidad y control de velocidad'],
        ['Transporte de Carga', 'Normas específicas para transporte de carga'],
        ['Estacionamiento y Parada', 'Normas sobre estacionamiento y parada de vehículos'],
        ['Semáforos y Señales', 'Semáforos, señales luminosas y marcas viales'],
        ['Señales de Peligro', 'Señales de peligro y advertencia'],
        ['Marcas Viales', 'Marcas viales y líneas en el pavimento'],
        ['Condiciones del Vehículo', 'Condiciones técnicas del vehículo'],
        ['Alumbrado', 'Sistema de alumbrado y luces'],
        ['Equipamiento', 'Equipamiento obligatorio del vehículo'],
        ['Licencias de Conducción', 'Tipos de licencias y requisitos'],
        ['Multas y Sanciones', 'Sistema de multas y sanciones'],
        ['Infracciones Graves', 'Infracciones graves y sus consecuencias'],
        ['Casos Prácticos', 'Casos prácticos de infracciones múltiples']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    
    echo "✓ Categorías insertadas\n\n";

    // 7. Crear directorios necesarios
    echo "6. Creando directorios...\n";
    $dirs = [
        'assets/img/questions',
        'logs'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✓ Directorio creado: $dir\n";
        } else {
            echo "✓ Directorio existe: $dir\n";
        }
    }
    
    echo "\n=== INSTALACIÓN COMPLETADA ===\n";
    echo "El sistema está listo para usar.\n";
    echo "Accede a: http://localhost/test-transito/\n";
    echo "Usuario admin: admin / {$config['admin_password']}\n\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>