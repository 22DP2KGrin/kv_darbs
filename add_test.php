<?php
header('Content-Type: application/json');
require_once 'config/database.php';

$raw = file_get_contents('php://input');
if (!$raw) {
    echo json_encode(['success' => false, 'error' => 'Empty body']);
    exit;
}

$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

if (empty($data['test_name']) || empty($data['language_id']) || empty($data['topic_name'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    $topicName = trim((string)$data['topic_name']);
    $languageId = (int)$data['language_id'];

    $topicStmt = $pdo->prepare('SELECT topic_id FROM topics WHERE language_id = ? AND topic_name = ? LIMIT 1');
    $topicStmt->execute([$languageId, $topicName]);
    $topic = $topicStmt->fetch(PDO::FETCH_ASSOC);

    if ($topic) {
        $topicId = (int)$topic['topic_id'];
    } else {
        $insertTopicStmt = $pdo->prepare('INSERT INTO topics (language_id, topic_name, description, difficulty_level) VALUES (?, ?, ?, ?)');
        $insertTopicStmt->execute([$languageId, $topicName, 'Administrator created test topic.', 'beginner']);
        $topicId = (int)$pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('INSERT INTO tests (test_name, language_id, topic_id) VALUES (?, ?, ?)');
    $stmt->execute([$data['test_name'], $languageId, $topicId]);
    $testId = $pdo->lastInsertId();

    if (!empty($data['questions']) && is_array($data['questions'])) {
        $qStmt = $pdo->prepare('INSERT INTO questions (test_id, question_text, question_order, question_type) VALUES (?, ?, ?, ?)');
        $aStmt = $pdo->prepare('INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)');
        $order = 1;
        foreach ($data['questions'] as $q) {
            $qText = $q['text'] ?? '';
            $qType = $q['type'] ?? 'single';
            $qStmt->execute([$testId, $qText, $order, $qType]);
            $questionId = $pdo->lastInsertId();
            $order++;
            if (!empty($q['answers']) && is_array($q['answers'])) {
                foreach ($q['answers'] as $ans) {
                    $aText = $ans['text'] ?? '';
                    $isCorrect = !empty($ans['is_correct']) ? 1 : 0;
                    $aStmt->execute([$questionId, $aText, $isCorrect]);
                }
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'test_id' => $testId]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('DB error add_test: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
