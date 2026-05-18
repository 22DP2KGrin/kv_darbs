<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'admin_auth_check.php';

if (!checkAdminAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT c.course_id, c.course_name, c.level, c.description, c.is_active,
                c.created_at, l.language_name, l.language_code,
                COUNT(le.lesson_id) AS lesson_count
         FROM courses c
         JOIN languages l ON c.language_id = l.language_id
         LEFT JOIN lessons le ON le.course_id = c.course_id
         GROUP BY c.course_id
         ORDER BY c.created_at DESC"
    );
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'courses' => $courses]);
} catch (PDOException $e) {
    error_log('Error fetching courses: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching courses']);
}
?>