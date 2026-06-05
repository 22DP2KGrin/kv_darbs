<?php
header('Content-Type: application/json');
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    $params = [];
    $sql = "SELECT t.id, t.test_name, t.language_id, l.language_name, l.language_code, t.topic_id, tp.topic_name, tp.difficulty_level, (
        SELECT COUNT(*) FROM questions q WHERE q.test_id = t.id
    ) AS question_count, t.created_at FROM tests t
    LEFT JOIN languages l ON t.language_id = l.language_id
    LEFT JOIN topics tp ON t.topic_id = tp.topic_id";
    if (!empty($_GET['language_id'])) {
        $sql .= ' WHERE t.language_id = ?';
        $params[] = (int)$_GET['language_id'];
    }
    $sql .= ' ORDER BY t.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'tests' => $tests]);
} catch (PDOException $e) {
    error_log('DB error get_tests: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
