<?php
header('Content-Type: application/json');
require_once 'admin_auth_check.php';
if (!checkAdminAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once 'config.php';
try {
    $pdo = initDatabase();
    
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Active users (last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['active_users'] = $stmt->fetchColumn();
    
    // Total courses
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    $stats['total_courses'] = $stmt->fetchColumn();
    
    // Total lessons
    $stmt = $pdo->query("SELECT COUNT(*) FROM lessons");
    $stats['total_lessons'] = $stmt->fetchColumn();
    
    // Total tests completed
    $stmt = $pdo->query("SELECT COUNT(*) FROM test_results");
    $stats['total_tests'] = $stmt->fetchColumn();
    
    // Average score
    $stmt = $pdo->query("SELECT ROUND(AVG(score/max_score*100), 1) FROM test_results WHERE max_score > 0");
    $stats['avg_score'] = $stmt->fetchColumn() ?? 0;
    
    // Recent test results with user info
    $stmt = $pdo->query("
        SELECT u.username, u.email, t.topic_name, tr.score, tr.max_score, 
               ROUND(tr.score/tr.max_score*100) as percentage,
               tr.time_spent, tr.completion_date
        FROM test_results tr
        JOIN users u ON tr.user_id = u.user_id
        JOIN topics t ON tr.topic_id = t.topic_id
        ORDER BY tr.completion_date DESC
        LIMIT 50
    ");
    $stats['recent_results'] = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (PDOException $e) {
    error_log("Error in admin_stats.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
