-- Base de datos para el Sistema de Test de Licencia Cuba
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS test_licencia_cuba CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE test_licencia_cuba;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de preguntas
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    option_1 TEXT NOT NULL,
    option_2 TEXT NOT NULL,
    option_3 TEXT NOT NULL,
    correct_answer TINYINT NOT NULL CHECK (correct_answer IN (1, 2, 3)),
    category VARCHAR(100) DEFAULT 'general',
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_difficulty (difficulty),
    INDEX idx_active (is_active)
);

-- Tabla de tests realizados
CREATE TABLE test_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    score_percentage DECIMAL(5,2) NOT NULL,
    time_taken INT DEFAULT NULL, -- en segundos
    status ENUM('completed', 'abandoned') DEFAULT 'completed',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_score (user_id, score_percentage),
    INDEX idx_date (started_at)
);

-- Tabla de respuestas del test (detalle)
CREATE TABLE test_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_session_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_answer TINYINT CHECK (selected_answer IN (1, 2, 3)),
    is_correct BOOLEAN NOT NULL,
    answer_time INT DEFAULT NULL, -- tiempo en segundos para responder
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_session_id) REFERENCES test_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_test_question (test_session_id, question_id)
);

-- Tabla de sesiones de usuario
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);

-- Tabla de configuración del sistema
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar usuario administrador por defecto
INSERT INTO users (username, email, password, first_name, last_name, role, email_verified) 
VALUES ('admin', 'admin@testlicencia.cu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin', TRUE);
-- Contraseña por defecto: password

-- Insertar configuraciones por defecto
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'Test Licencia Cuba', 'Nombre del sitio'),
('max_questions_per_test', '100', 'Máximo número de preguntas por test'),
('passing_score', '80', 'Puntuación mínima para aprobar (%)'),
('session_timeout', '7200', 'Tiempo de sesión en segundos (2 horas)'),
('allow_registration', '1', 'Permitir registro de nuevos usuarios'),
('maintenance_mode', '0', 'Modo mantenimiento activado');

-- Insertar algunas preguntas de ejemplo
INSERT INTO questions (question_text, option_1, option_2, option_3, correct_answer, category, created_by) VALUES
('¿Cuál es la velocidad máxima permitida en zonas urbanas?', '40 km/h', '50 km/h', '60 km/h', 2, 'velocidad', 1),
('¿Qué significa una luz roja en el semáforo?', 'Precaución', 'Deténgase', 'Avance con cuidado', 2, 'señales', 1),
('¿A qué distancia debe mantenerse de otro vehículo?', '1 metro', '3 metros', '5 metros', 2, 'distancia', 1),
('¿Cuándo debe usar las luces altas?', 'Siempre', 'En carreteras oscuras sin tráfico', 'En la ciudad', 2, 'luces', 1),
('¿Qué debe hacer al aproximarse a un paso de peatones?', 'Acelerar', 'Reducir velocidad y ceder el paso', 'Tocar la bocina', 2, 'peatones', 1);