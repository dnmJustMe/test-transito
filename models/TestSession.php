<?php
/**
 * Modelo de Sesión de Test
 */

class TestSession {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nueva sesión de test
     */
    public function create($userId, $totalQuestions) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO test_sessions (user_id, total_questions, correct_answers, score_percentage, status) 
                VALUES (?, ?, 0, 0, 'completed')
            ");
            
            $result = $stmt->execute([$userId, $totalQuestions]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            logMessage("Error creando sesión de test: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Finalizar sesión de test
     */
    public function finish($sessionId, $correctAnswers, $scorePercentage, $timeTaken = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE test_sessions 
                SET correct_answers = ?, score_percentage = ?, time_taken = ?, completed_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$correctAnswers, $scorePercentage, $timeTaken, $sessionId]);
            
        } catch (Exception $e) {
            logMessage("Error finalizando sesión de test: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener sesión por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT ts.*, u.username, u.first_name, u.last_name 
                FROM test_sessions ts 
                INNER JOIN users u ON ts.user_id = u.id 
                WHERE ts.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo sesión de test: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener historial de tests de un usuario
     */
    public function getUserHistory($userId, $page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Contar total
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) FROM test_sessions WHERE user_id = ? AND status = 'completed'
            ");
            $countStmt->execute([$userId]);
            $total = $countStmt->fetchColumn();
            
            // Obtener historial
            $stmt = $this->db->prepare("
                SELECT id, total_questions, correct_answers, score_percentage, 
                       time_taken, started_at, completed_at
                FROM test_sessions 
                WHERE user_id = ? AND status = 'completed'
                ORDER BY started_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $limit, $offset]);
            $history = $stmt->fetchAll();
            
            return [
                'history' => $history,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            logMessage("Error obteniendo historial de usuario: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener todas las sesiones (para admin)
     */
    public function getAll($page = 1, $limit = 20, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            $whereConditions = ["ts.status = 'completed'"];
            $params = [];
            
            // Filtros
            if (!empty($filters['user_id'])) {
                $whereConditions[] = "ts.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = "DATE(ts.started_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = "DATE(ts.started_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (isset($filters['passed']) && $filters['passed'] !== '') {
                $passingScore = DEFAULT_PASSING_SCORE;
                if ($filters['passed']) {
                    $whereConditions[] = "ts.score_percentage >= ?";
                    $params[] = $passingScore;
                } else {
                    $whereConditions[] = "ts.score_percentage < ?";
                    $params[] = $passingScore;
                }
            }
            
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
            
            // Contar total
            $countSql = "SELECT COUNT(*) FROM test_sessions ts INNER JOIN users u ON ts.user_id = u.id" . $whereClause;
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Obtener sesiones
            $sql = "
                SELECT ts.*, u.username, u.first_name, u.last_name 
                FROM test_sessions ts 
                INNER JOIN users u ON ts.user_id = u.id" . $whereClause . "
                ORDER BY ts.started_at DESC 
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $sessions = $stmt->fetchAll();
            
            return [
                'sessions' => $sessions,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            logMessage("Error obteniendo sesiones: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener respuestas de una sesión
     */
    public function getSessionAnswers($sessionId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ta.*, q.question_text, q.option_1, q.option_2, q.option_3, 
                       q.correct_answer, q.image_path, q.category
                FROM test_answers ta
                INNER JOIN questions q ON ta.question_id = q.id
                WHERE ta.test_session_id = ?
                ORDER BY ta.id
            ");
            $stmt->execute([$sessionId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logMessage("Error obteniendo respuestas de sesión: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Guardar respuesta de test
     */
    public function saveAnswer($sessionId, $questionId, $selectedAnswer, $isCorrect, $answerTime = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO test_answers (test_session_id, question_id, selected_answer, is_correct, answer_time) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                selected_answer = VALUES(selected_answer), 
                is_correct = VALUES(is_correct),
                answer_time = VALUES(answer_time)
            ");
            
            return $stmt->execute([$sessionId, $questionId, $selectedAnswer, $isCorrect, $answerTime]);
            
        } catch (Exception $e) {
            logMessage("Error guardando respuesta: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener estadísticas generales
     */
    public function getGeneralStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_tests,
                    COUNT(DISTINCT user_id) as total_users,
                    AVG(score_percentage) as avg_score,
                    COUNT(CASE WHEN score_percentage >= ? THEN 1 END) as passed_tests,
                    AVG(time_taken) as avg_time
                FROM test_sessions 
                WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([DEFAULT_PASSING_SCORE]);
            return $stmt->fetch();
        } catch (Exception $e) {
            logMessage("Error obteniendo estadísticas generales: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener estadísticas por categoría
     */
    public function getStatsByCategory() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    q.category,
                    COUNT(ta.id) as total_answers,
                    COUNT(CASE WHEN ta.is_correct = 1 THEN 1 END) as correct_answers,
                    ROUND((COUNT(CASE WHEN ta.is_correct = 1 THEN 1 END) / COUNT(ta.id)) * 100, 2) as success_rate
                FROM test_answers ta
                INNER JOIN questions q ON ta.question_id = q.id
                INNER JOIN test_sessions ts ON ta.test_session_id = ts.id
                WHERE ts.status = 'completed' AND ts.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY q.category
                ORDER BY success_rate DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logMessage("Error obteniendo estadísticas por categoría: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener mejores puntuaciones
     */
    public function getTopScores($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT ts.score_percentage, ts.total_questions, ts.completed_at,
                       u.username, u.first_name, u.last_name
                FROM test_sessions ts
                INNER JOIN users u ON ts.user_id = u.id
                WHERE ts.status = 'completed'
                ORDER BY ts.score_percentage DESC, ts.completed_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logMessage("Error obteniendo mejores puntuaciones: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de usuario específico
     */
    public function getUserStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_tests,
                    AVG(score_percentage) as avg_score,
                    MAX(score_percentage) as best_score,
                    MIN(score_percentage) as worst_score,
                    COUNT(CASE WHEN score_percentage >= ? THEN 1 END) as passed_tests,
                    AVG(time_taken) as avg_time,
                    MIN(time_taken) as best_time
                FROM test_sessions 
                WHERE user_id = ? AND status = 'completed'
            ");
            $stmt->execute([DEFAULT_PASSING_SCORE, $userId]);
            $stats = $stmt->fetch();
            
            // Obtener progreso reciente (últimos 10 tests)
            $progressStmt = $this->db->prepare("
                SELECT score_percentage, completed_at 
                FROM test_sessions 
                WHERE user_id = ? AND status = 'completed'
                ORDER BY completed_at DESC 
                LIMIT 10
            ");
            $progressStmt->execute([$userId]);
            $progress = $progressStmt->fetchAll();
            
            return [
                'stats' => $stats,
                'progress' => array_reverse($progress) // Orden cronológico
            ];
            
        } catch (Exception $e) {
            logMessage("Error obteniendo estadísticas de usuario: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Eliminar sesión
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            // Eliminar respuestas primero
            $stmt1 = $this->db->prepare("DELETE FROM test_answers WHERE test_session_id = ?");
            $stmt1->execute([$id]);
            
            // Eliminar sesión
            $stmt2 = $this->db->prepare("DELETE FROM test_sessions WHERE id = ?");
            $result = $stmt2->execute([$id]);
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Error eliminando sesión: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}