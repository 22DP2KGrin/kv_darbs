<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'admin_auth_check.php';

if (!checkAdminAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Read the incoming JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$userId = (int)$data['user_id'];

try {
    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    echo json_encode(['message' => 'User deleted successfully']);
} catch (PDOException $e) {
    error_log('Error deleting user: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error deleting user']);
}
?>