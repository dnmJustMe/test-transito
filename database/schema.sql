-- Esquema de la base de datos para el sistema de tests de tránsito
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS test_transito CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE test_transito;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías de preguntas
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    question_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de preguntas
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    nro INT NOT NULL, -- Número de pregunta para las imágenes
    question_text TEXT NOT NULL,
    answer1 VARCHAR(255) NOT NULL,
    answer2 VARCHAR(255) NOT NULL,
    answer3 VARCHAR(255) NOT NULL,
    correct_answer INT NOT NULL CHECK (correct_answer IN (1, 2, 3)),
    article_reference VARCHAR(20),
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabla de tests
CREATE TABLE tests (
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
);

-- Tabla de sesiones de test
CREATE TABLE test_sessions (
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
);

-- Tabla de respuestas de usuario
CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_answer INT NOT NULL CHECK (selected_answer IN (1, 2, 3)),
    is_correct BOOLEAN DEFAULT 0,
    time_taken INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES test_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Tabla de configuración del sistema
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos iniciales
INSERT INTO categories (name, description) VALUES
('Normas Generales', 'Normas generales de circulación y comportamiento en la vía'),
('Señales y Semáforos', 'Señales de tránsito, semáforos y marcas viales'),
('Prioridades y Pasos', 'Prioridades de paso, intersecciones y pasos a nivel'),
('Adelantamientos', 'Normas sobre adelantamientos y maniobras'),
('Vehículos y Carga', 'Especificaciones de vehículos y transporte de carga'),
('Circulación Especial', 'Normas para circulación de vehículos especiales'),
('Estacionamiento', 'Normas sobre estacionamiento y parada'),
('Velocidades', 'Límites de velocidad y control de velocidad'),
('Transporte de Carga', 'Normas específicas para transporte de carga'),
('Estacionamiento y Parada', 'Normas sobre estacionamiento y parada de vehículos'),
('Semáforos y Señales', 'Semáforos, señales luminosas y marcas viales'),
('Señales de Peligro', 'Señales de peligro y advertencia'),
('Marcas Viales', 'Marcas viales y líneas en el pavimento'),
('Condiciones del Vehículo', 'Condiciones técnicas del vehículo'),
('Alumbrado', 'Sistema de alumbrado y luces'),
('Equipamiento', 'Equipamiento obligatorio del vehículo'),
('Licencias de Conducción', 'Tipos de licencias y requisitos'),
('Multas y Sanciones', 'Sistema de multas y sanciones'),
('Infracciones Graves', 'Infracciones graves y sus consecuencias'),
('Casos Prácticos', 'Casos prácticos de infracciones múltiples');

-- Insertar usuario administrador
INSERT INTO users (username, email, password, first_name, last_name, role) VALUES 
('admin', 'admin@test-transito.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin');

-- Insertar configuración del sistema
INSERT INTO system_config (config_key, config_value, description) VALUES
('site_name', 'Sistema de Test de Tránsito', 'Nombre del sitio web'),
('max_questions_per_test', '20', 'Número máximo de preguntas por test'),
('passing_score', '70', 'Puntuación mínima para aprobar'),
('test_time_limit', '1200', 'Tiempo límite del test en segundos'),
('questions_per_category', '5', 'Preguntas por categoría en test mixto');

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_questions_category ON questions(category_id);
CREATE INDEX idx_questions_active ON questions(is_active);
CREATE INDEX idx_test_sessions_user ON test_sessions(user_id);
CREATE INDEX idx_test_sessions_status ON test_sessions(status);
CREATE INDEX idx_user_answers_session ON user_answers(session_id);
CREATE INDEX idx_user_answers_question ON user_answers(question_id);