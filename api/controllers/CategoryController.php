<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/JWT.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private $category;
    
    public function __construct() {
        $this->category = new Category();
    }
    
    public function getAll() {
        try {
            $categories = $this->category->getAll();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getWithQuestionCount() {
        try {
            $categories = $this->category->getWithQuestionCount();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function getById($id) {
        try {
            $category = $this->category->findById($id);
            
            if (!$category) {
                http_response_code(404);
                echo json_encode(['error' => 'Categoría no encontrada']);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $category
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function create() {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['name']) || empty($input['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nombre de categoría requerido']);
                return;
            }
            
            // Crear categoría
            $categoryId = $this->category->create($input);
            $category = $this->category->findById($categoryId);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => $category
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function update($id) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            if (!isset($input['name']) || empty($input['name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nombre de categoría requerido']);
                return;
            }
            
            // Actualizar categoría
            $this->category->update($id, $input);
            $category = $this->category->findById($id);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => $category
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    public function delete($id) {
        try {
            $user = JWT::getCurrentUser();
            
            if (!$user || $user['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso denegado']);
                return;
            }
            
            $this->category->delete($id);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
}
?>