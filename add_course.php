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

if (empty($data['course_name']) || empty($data['language_id']) || empty($data['level'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$courseName = trim($data['course_name']);
$languageId = (int)$data['language_id'];
$level = trim($data['level']);
$description = trim($data['description'] ?? '');

if (!in_array($level, ['beginner', 'intermediate', 'advanced'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid level']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO courses (course_name, language_id, level, description, created_at, is_active)
         VALUES (?, ?, ?, ?, NOW(), TRUE)"
    );
    $stmt->execute([$courseName, $languageId, $level, $description]);

    echo json_encode([
        'success' => true,
        'message' => 'Course created successfully',
        'course_id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    error_log('Error creating course: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error creating course']);
}
?>