<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once 'admin_auth_check.php';

// Check if user is logged in and is admin
if (!checkAdminAuth()) {
    error_log("Unauthorized access attempt in get_exercises.php");
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $exerciseFiles = glob(__DIR__ . '/exercises/*.html') ?: [];
    $exercises = array_map(function ($file, $index) {
        $name = basename($file, '.html');
        return [
            'exercise_id' => $index + 1,
            'exercise_name' => ucwords(str_replace('-', ' ', $name)),
            'topic_name' => ucwords(str_replace('-', ' ', $name)),
            'time_limit' => null,
            'total_questions' => null,
            'description' => 'Exercise page stored in the project files.',
            'path' => 'exercises/' . basename($file)
        ];
    }, $exerciseFiles, array_keys($exerciseFiles));

    echo json_encode([
        'success' => true,
        'exercises' => $exercises
    ]);

} catch (PDOException $e) {
    error_log("Error fetching exercises from database: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error fetching exercises from database',
        'details' => $e->getMessage()
    ]);
}
?> 
