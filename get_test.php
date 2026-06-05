<?php
header('Content-Type: application/json');
require_once 'config/database.php';

if (!isset($_GET['test_id'])) {
    echo json_encode(['success' => false, 'error' => 'test_id required']);
    exit;
}

$testId = (int)$_GET['test_id'];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT t.*, l.language_name, tp.topic_name FROM tests t LEFT JOIN languages l ON t.language_id = l.language_id LEFT JOIN topics tp ON t.topic_id = tp.topic_id WHERE t.id = ?');
    $stmt->execute([$testId]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$test) {
        echo json_encode(['success' => false, 'error' => 'Test not found']);
        exit;
    }

    $qStmt = $pdo->prepare('SELECT id, question_text, question_order, question_type FROM questions WHERE test_id = ? ORDER BY question_order ASC');
    $qStmt->execute([$testId]);
    $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

    $aStmt = $pdo->prepare('SELECT id, question_id, answer_text, is_correct FROM answers WHERE question_id = ?');
    foreach ($questions as &$q) {
        $aStmt->execute([$q['id']]);
        $q['answers'] = $aStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'test' => $test, 'questions' => $questions]);
} catch (PDOException $e) {
    error_log('DB error get_test: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
