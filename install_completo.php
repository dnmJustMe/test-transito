<?php
/**
 * Script de Instalación Completa del Sistema de Test de Tránsito
 * Este script hace todo el setup: BD, tablas, datos iniciales y preguntas
 */

echo "=== INSTALACIÓN COMPLETA DEL SISTEMA ===\n\n";

// Configuración de base de datos
$host = 'localhost';
$dbname = 'test_transito';
$username = 'root';
$password = '';

try {
    // Conectar a MySQL sin especificar base de datos
    $pdo = new PDO("mysql:host=$host", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✓ Conexión a MySQL exitosa\n";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de datos '$dbname' creada/verificada\n";
    
    // Conectar a la base de datos específica
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✓ Conectado a la base de datos '$dbname'\n\n";
    
    // 1. Crear tabla de usuarios
    echo "1. CREANDO TABLA DE USUARIOS...\n";
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            lives INT DEFAULT 3,
            last_life_lost TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Tabla 'users' creada\n";
    
    // 2. Crear tabla de preguntas
    echo "\n2. CREANDO TABLA DE PREGUNTAS...\n";
    $pdo->exec("DROP TABLE IF EXISTS questions");
    $pdo->exec("
        CREATE TABLE questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_text TEXT NOT NULL,
            answer1 VARCHAR(255) NOT NULL,
            answer2 VARCHAR(255) NOT NULL,
            answer3 VARCHAR(255) NOT NULL,
            correct_answer INT NOT NULL CHECK (correct_answer IN (1, 2, 3)),
            image_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Tabla 'questions' creada\n";
    
    // 3. Crear tabla de sesiones de test
    echo "\n3. CREANDO TABLA DE SESIONES DE TEST...\n";
    $pdo->exec("DROP TABLE IF EXISTS test_sessions");
    $pdo->exec("
        CREATE TABLE test_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
            question_count INT NOT NULL,
            correct_answers INT NOT NULL,
            score DECIMAL(5,2) NOT NULL,
            passed BOOLEAN NOT NULL,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Tabla 'test_sessions' creada\n";
    
    // 4. Crear tabla de respuestas de usuario
    echo "\n4. CREANDO TABLA DE RESPUESTAS DE USUARIO...\n";
    $pdo->exec("DROP TABLE IF EXISTS user_answers");
    $pdo->exec("
        CREATE TABLE user_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL,
            question_id INT NOT NULL,
            user_answer INT NOT NULL,
            is_correct BOOLEAN NOT NULL,
            answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (session_id) REFERENCES test_sessions(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Tabla 'user_answers' creada\n";
    
    // 5. Crear tabla de configuración del sistema
    echo "\n5. CREANDO TABLA DE CONFIGURACIÓN...\n";
    $pdo->exec("DROP TABLE IF EXISTS system_config");
    $pdo->exec("
        CREATE TABLE system_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            key_name VARCHAR(50) UNIQUE NOT NULL,
            value TEXT NOT NULL,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Tabla 'system_config' creada\n";
    
    // 6. Insertar configuración del sistema
    echo "\n6. INSERTANDO CONFIGURACIÓN DEL SISTEMA...\n";
    $configs = [
        ['app_name', 'Sistema de Test de Tránsito', 'Nombre de la aplicación'],
        ['app_version', '1.0.0', 'Versión de la aplicación'],
        ['max_lives', '3', 'Número máximo de vidas por usuario'],
        ['life_regeneration_minutes', '5', 'Minutos para regenerar una vida'],
        ['passing_score', '80', 'Puntuación mínima para aprobar (%)'],
        ['easy_questions', '20', 'Número de preguntas para dificultad fácil'],
        ['medium_questions', '40', 'Número de preguntas para dificultad media'],
        ['hard_questions', '100', 'Número de preguntas para dificultad difícil']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO system_config (key_name, value, description) VALUES (?, ?, ?)");
    foreach ($configs as $config) {
        $stmt->execute($config);
    }
    echo "✓ Configuración del sistema insertada\n";
    
    // 7. Crear usuario administrador
    echo "\n7. CREANDO USUARIO ADMINISTRADOR...\n";
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, role, lives) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute(['admin', 'admin@test-transito.com', $adminPassword, 'Administrador', 'Sistema', 'admin', 3]);
    echo "✓ Usuario administrador creado (admin/admin123)\n";
    
    // 8. Crear directorios necesarios
    echo "\n8. CREANDO DIRECTORIOS...\n";
    $directories = [
        'assets/img/questions',
        'logs',
        'api',
        'api/config',
        'api/controllers',
        'api/models',
        'assets/css',
        'assets/js'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✓ Directorio '$dir' creado\n";
        } else {
            echo "✓ Directorio '$dir' ya existe\n";
        }
    }
    
    // 9. Insertar preguntas de ejemplo
    echo "\n9. INSERTANDO PREGUNTAS DE EJEMPLO...\n";
    $questions = [
        [
            'question_text' => '¿Qué indica una señal de PARE?',
            'answer1' => 'Que debes detenerte completamente',
            'answer2' => 'Que debes reducir la velocidad',
            'answer3' => 'Que debes ceder el paso',
            'correct_answer' => 1,
            'image_path' => null
        ],
        [
            'question_text' => '¿Cuál es la velocidad máxima en zona urbana?',
            'answer1' => '30 km/h',
            'answer2' => '50 km/h',
            'answer3' => '70 km/h',
            'correct_answer' => 2,
            'image_path' => null
        ],
        [
            'question_text' => '¿Qué hacer ante una luz roja del semáforo?',
            'answer1' => 'Acelerar para pasar rápido',
            'answer2' => 'Detenerse completamente',
            'answer3' => 'Continuar con precaución',
            'correct_answer' => 2,
            'image_path' => null
        ],
        [
            'question_text' => '¿Cuándo usar las luces altas?',
            'answer1' => 'Siempre en la noche',
            'answer2' => 'Solo en carretera sin tráfico',
            'answer3' => 'Nunca',
            'correct_answer' => 2,
            'image_path' => null
        ],
        [
            'question_text' => '¿Qué distancia mantener con el vehículo de adelante?',
            'answer1' => 'Al menos 2 metros',
            'answer2' => 'Al menos 3 segundos',
            'answer3' => 'No importa la distancia',
            'correct_answer' => 2,
            'image_path' => null
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO questions (question_text, answer1, answer2, answer3, correct_answer, image_path) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($questions as $question) {
        $stmt->execute([
            $question['question_text'],
            $question['answer1'],
            $question['answer2'],
            $question['answer3'],
            $question['correct_answer'],
            $question['image_path']
        ]);
    }
    echo "✓ 5 preguntas de ejemplo insertadas\n";
    
    // 10. Insertar estadísticas iniciales
    echo "\n10. INSERTANDO ESTADÍSTICAS INICIALES...\n";
    $pdo->exec("
        INSERT INTO system_config (key_name, value, description) VALUES 
        ('total_users', '1', 'Total de usuarios registrados'),
        ('total_tests', '0', 'Total de tests realizados'),
        ('total_questions', '5', 'Total de preguntas disponibles')
    ");
    echo "✓ Estadísticas iniciales insertadas\n";
    
    echo "\n=== INSTALACIÓN COMPLETADA EXITOSAMENTE ===\n\n";
    echo "CREDENCIALES DE ACCESO:\n";
    echo "- Usuario: admin\n";
    echo "- Contraseña: admin123\n";
    echo "- Email: admin@test-transito.com\n\n";
    
    echo "PRÓXIMOS PASOS:\n";
    echo "1. Accede a: http://localhost/test-transito/\n";
    echo "2. Inicia sesión con las credenciales de admin\n";
    echo "3. Agrega más preguntas desde el panel de administración\n";
    echo "4. Registra usuarios normales para probar el sistema\n\n";
    
    echo "FUNCIONALIDADES DISPONIBLES:\n";
    echo "- Registro e inicio de sesión de usuarios\n";
    echo "- Tests con 3 niveles de dificultad (20, 40, 100 preguntas)\n";
    echo "- Sistema de vidas (3 vidas, regeneración cada 5 minutos)\n";
    echo "- Panel de administración para gestionar preguntas y usuarios\n";
    echo "- Estadísticas públicas y personales\n\n";
    
} catch (PDOException $e) {
    echo "✗ Error durante la instalación: " . $e->getMessage() . "\n";
    exit(1);
}
?>