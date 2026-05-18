<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'admin_auth_check.php';

if (!checkAdminAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['course_id']) || empty($data['lesson_title'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$courseId = (int)$data['course_id'];
$lessonTitle = trim($data['lesson_title']);
$lessonDescription = trim($data['lesson_description'] ?? '');

try {
    $courseStmt = $pdo->prepare('SELECT course_id FROM courses WHERE course_id = ? LIMIT 1');
    $courseStmt->execute([$courseId]);
    if (!$courseStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Course not found']);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO lessons (course_id, lesson_title, lesson_description, created_at, is_active)
         VALUES (?, ?, ?, NOW(), TRUE)"
    );
    $stmt->execute([$courseId, $lessonTitle, $lessonDescription]);

    echo json_encode([
        'success' => true,
        'message' => 'Lesson added successfully',
        'lesson_id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    error_log('Error adding lesson: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error adding lesson']);
}
?>