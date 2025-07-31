-- Script para arreglar el usuario admin
USE test_transito;

-- Eliminar el usuario admin existente si existe
DELETE FROM users WHERE username = 'admin' OR email = 'admin@test-transito.com';

-- Crear el usuario admin con contraseña 'admin123'
-- Hash generado con PHP password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, first_name, last_name, role, is_active, created_at, updated_at) VALUES 
('admin', 'admin@test-transito.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin', 1, NOW(), NOW());

-- Verificar que se creó correctamente
SELECT id, username, email, first_name, last_name, role, is_active FROM users WHERE username = 'admin';