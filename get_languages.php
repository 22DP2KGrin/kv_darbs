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
    $stmt = $pdo->query("SELECT language_id, language_name, language_code FROM languages ORDER BY language_name ASC");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'languages' => $languages]);
} catch (PDOException $e) {
    error_log('Error fetching languages: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching languages']);
}
?>