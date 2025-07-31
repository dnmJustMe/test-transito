<?php
/**
 * Script de Verificación Completa del Sistema con Pruebas Unitarias
 */

class SystemVerifier {
    private $db;
    private $apiBaseUrl;
    private $testResults = [];
    private $testData = [];
    
    public function __construct() {
        $this->apiBaseUrl = 'http://localhost/test-transito/api/';
        $this->initDatabase();
    }
    
    private function initDatabase() {
        try {
            $this->db = new PDO(
                "mysql:host=localhost;dbname=test_transito;charset=utf8mb4",
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage() . "\n");
        }
    }
    
    public function runAllTests() {
        echo "🚀 VERIFICACIÓN COMPLETA DEL SISTEMA\n";
        echo "=" . str_repeat("=", 60) . "\n\n";
        
        $this->testPhpConfiguration();
        $this->testFileStructure();
        $this->testDatabaseConnection();
        $this->testApiEndpoints();
        $this->testUserRegistration();
        $this->testUserLogin();
        $this->testQuestionManagement();
        $this->testTestSystem();
        $this->testLivesSystem();
        $this->testAdminFunctions();
        $this->testCleanup();
        
        $this->generateReport();
    }
    
    private function testPhpConfiguration() {
        echo "📋 1. CONFIGURACIÓN DE PHP\n";
        echo str_repeat("-", 40) . "\n";
        
        $extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        foreach ($extensions as $ext) {
            $status = extension_loaded($ext) ? "✅ Disponible" : "❌ No disponible";
            echo "   - $ext: $status\n";
        }
        echo "   - Versión PHP: " . PHP_VERSION . "\n\n";
    }
    
    private function testFileStructure() {
        echo "📁 2. ESTRUCTURA DE ARCHIVOS\n";
        echo str_repeat("-", 40) . "\n";
        
        $files = [
            'index.html', 'assets/js/app.js', 'assets/css/style.css',
            'api/index.php', 'api/config/database.php',
            'api/controllers/AuthController.php', 'api/controllers/QuestionController.php',
            'api/controllers/SessionController.php', 'api/models/User.php',
            'api/models/Question.php', 'api/models/TestSession.php',
            'install_completo.php', '.htaccess'
        ];
        
        foreach ($files as $file) {
            $status = file_exists($file) ? "✅ Existe" : "❌ No existe";
            echo "   - $file: $status\n";
        }
        echo "\n";
    }
    
    private function testDatabaseConnection() {
        echo "🗄️  3. BASE DE DATOS\n";
        echo str_repeat("-", 40) . "\n";
        
        try {
            $tables = ['users', 'questions', 'test_sessions', 'user_answers', 'system_config'];
            foreach ($tables as $table) {
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $exists = $stmt->rowCount() > 0;
                $status = $exists ? "✅ Existe" : "❌ No existe";
                echo "   - Tabla $table: $status\n";
            }
            
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $adminCount = $stmt->fetch()['count'];
            echo "   - Usuarios admin: $adminCount\n";
            
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM questions");
            $questionCount = $stmt->fetch()['count'];
            echo "   - Preguntas: $questionCount\n";
            
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    private function testApiEndpoints() {
        echo "🌐 4. ENDPOINTS DE LA API\n";
        echo str_repeat("-", 40) . "\n";
        
        $endpoints = [
            'sessions/public-stats' => 'GET',
            'auth/register' => 'POST',
            'auth/login' => 'POST',
            'questions' => 'GET'
        ];
        
        foreach ($endpoints as $endpoint => $method) {
            $url = $this->apiBaseUrl . $endpoint;
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'timeout' => 5
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            $status = $response !== false ? "✅ Responde" : "❌ No responde";
            echo "   - $method $endpoint: $status\n";
        }
        echo "\n";
    }
    
    private function testUserRegistration() {
        echo "👤 5. PRUEBA DE REGISTRO\n";
        echo str_repeat("-", 40) . "\n";
        
        $testUser = [
            'username' => 'test_user_' . time(),
            'email' => 'test_' . time() . '@test.com',
            'password' => 'test123',
            'first_name' => 'Test',
            'last_name' => 'User'
        ];
        
        $this->testData['testUser'] = $testUser;
        
        $url = $this->apiBaseUrl . 'auth/register';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($testUser),
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $responseData = json_decode($response, true);
        
        if ($responseData && $responseData['success']) {
            echo "   ✅ Registro exitoso\n";
            $this->testResults['registration'] = true;
        } else {
            echo "   ❌ Error: " . ($responseData['message'] ?? 'Error desconocido') . "\n";
            $this->testResults['registration'] = false;
        }
        echo "\n";
    }
    
    private function testUserLogin() {
        echo "🔐 6. PRUEBA DE LOGIN\n";
        echo str_repeat("-", 40) . "\n";
        
        if (!isset($this->testData['testUser'])) {
            echo "   ⚠️  No hay usuario de prueba\n\n";
            return;
        }
        
        $loginData = [
            'email' => $this->testData['testUser']['email'],
            'password' => $this->testData['testUser']['password']
        ];
        
        $url = $this->apiBaseUrl . 'auth/login';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($loginData),
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $responseData = json_decode($response, true);
        
        if ($responseData && $responseData['success']) {
            echo "   ✅ Login exitoso\n";
            $this->testData['token'] = $responseData['data']['token'];
            $this->testResults['login'] = true;
        } else {
            echo "   ❌ Error: " . ($responseData['message'] ?? 'Error desconocido') . "\n";
            $this->testResults['login'] = false;
        }
        echo "\n";
    }
    
    private function testQuestionManagement() {
        echo "❓ 7. GESTIÓN DE PREGUNTAS\n";
        echo str_repeat("-", 40) . "\n";
        
        if (!isset($this->testData['token'])) {
            echo "   ⚠️  No hay token disponible\n\n";
            return;
        }
        
        $url = $this->apiBaseUrl . 'questions';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $this->testData['token'],
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $responseData = json_decode($response, true);
        
        if ($responseData && $responseData['success']) {
            echo "   ✅ Listado exitoso\n";
            $this->testResults['question_list'] = true;
        } else {
            echo "   ❌ Error al obtener preguntas\n";
            $this->testResults['question_list'] = false;
        }
        echo "\n";
    }
    
    private function testTestSystem() {
        echo "📝 8. SISTEMA DE TESTS\n";
        echo str_repeat("-", 40) . "\n";
        
        if (!isset($this->testData['token'])) {
            echo "   ⚠️  No hay token disponible\n\n";
            return;
        }
        
        $url = $this->apiBaseUrl . 'questions/start-test';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode(['difficulty' => 'easy']),
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $responseData = json_decode($response, true);
        
        if ($responseData && $responseData['success']) {
            echo "   ✅ Test iniciado exitosamente\n";
            $this->testResults['test_system'] = true;
        } else {
            echo "   ❌ Error al iniciar test\n";
            $this->testResults['test_system'] = false;
        }
        echo "\n";
    }
    
    private function testLivesSystem() {
        echo "❤️  9. SISTEMA DE VIDAS\n";
        echo str_repeat("-", 40) . "\n";
        
        if (!isset($this->testData['token'])) {
            echo "   ⚠️  No hay token disponible\n\n";
            return;
        }
        
        $url = $this->apiBaseUrl . 'auth/lives';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $this->testData['token'],
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $responseData = json_decode($response, true);
        
        if ($responseData && $responseData['success']) {
            echo "   ✅ Sistema de vidas funcionando\n";
            $this->testResults['lives_system'] = true;
        } else {
            echo "   ❌ Error en sistema de vidas\n";
            $this->testResults['lives_system'] = false;
        }
        echo "\n";
    }
    
    private function testAdminFunctions() {
        echo "👑 10. FUNCIONES DE ADMIN\n";
        echo str_repeat("-", 40) . "\n";
        
        $adminData = [
            'email' => 'admin@test.com',
            'password' => 'admin123'
        ];
        
        $url = $this->apiBaseUrl . 'auth/login';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($adminData),
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $responseData = json_decode($response, true);
        
        if ($responseData && $responseData['success'] && $responseData['data']['user']['role'] === 'admin') {
            echo "   ✅ Login de admin exitoso\n";
            $this->testResults['admin_login'] = true;
        } else {
            echo "   ❌ Error en login de admin\n";
            $this->testResults['admin_login'] = false;
        }
        echo "\n";
    }
    
    private function testCleanup() {
        echo "🧹 11. LIMPIEZA\n";
        echo str_repeat("-", 40) . "\n";
        
        if (isset($this->testData['testUser'])) {
            try {
                $stmt = $this->db->prepare("DELETE FROM users WHERE email = ?");
                $stmt->execute([$this->testData['testUser']['email']]);
                echo "   ✅ Usuario de prueba eliminado\n";
            } catch (Exception $e) {
                echo "   ❌ Error al eliminar: " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }
    
    private function generateReport() {
        echo "📊 RESUMEN FINAL\n";
        echo str_repeat("=", 60) . "\n\n";
        
        $totalTests = count($this->testResults);
        $passedTests = count(array_filter($this->testResults));
        $failedTests = $totalTests - $passedTests;
        
        echo "📈 ESTADÍSTICAS:\n";
        echo "   - Total de pruebas: $totalTests\n";
        echo "   - Exitosas: $passedTests\n";
        echo "   - Fallidas: $failedTests\n";
        echo "   - Tasa de éxito: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";
        
        if ($failedTests === 0) {
            echo "🎉 ¡TODAS LAS PRUEBAS PASARON!\n";
            echo "✅ El sistema está funcionando correctamente\n\n";
        } else {
            echo "⚠️  ALGUNAS PRUEBAS FALLARON\n";
            echo "❌ Revisa los errores anteriores\n\n";
        }
        
        echo "📋 FUNCIONALIDADES VERIFICADAS:\n";
        $functionalities = [
            'registration' => 'Registro de usuarios',
            'login' => 'Inicio de sesión',
            'question_list' => 'Listado de preguntas',
            'test_system' => 'Sistema de tests',
            'lives_system' => 'Sistema de vidas',
            'admin_login' => 'Acceso de administrador'
        ];
        
        foreach ($functionalities as $key => $description) {
            $status = isset($this->testResults[$key]) && $this->testResults[$key] ? "✅" : "❌";
            echo "   $status $description\n";
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🏁 VERIFICACIÓN COMPLETADA\n";
    }
}

// Ejecutar verificación
$verifier = new SystemVerifier();
$verifier->runAllTests();
?>