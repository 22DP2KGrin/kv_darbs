<?php
header('Content-Type: application/json');
require_once 'config/database.php';
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (empty($data['test_id'])) {
    echo json_encode(['success' => false, 'error' => 'test_id required']);
    exit;
}
try{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('DELETE FROM tests WHERE id = ?');
    $stmt->execute([(int)$data['test_id']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e){
    error_log('DB error delete_test: '.$e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
