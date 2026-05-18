<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require_once 'admin_auth_check.php';

if (!checkAdminAuth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Получаем данные запроса
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action']) || !isset($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $action = $data['action'];
    $userId = $data['user_id'];

    // Выполняем запрошенное действие
    switch ($action) {
        case 'delete':
            // Удаляем пользователя
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $message = 'User deleted successfully';
            break;

        case 'block':
            // Блокируем пользователя
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
            $stmt->execute([$userId]);
            $message = 'User blocked successfully';
            break;

        case 'unblock':
            // Разблокируем пользователя
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
            $stmt->execute([$userId]);
            $message = 'User unblocked successfully';
            break;

        case 'make_admin':
            // Назначаем пользователя администратором
            $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE user_id = ?");
            $stmt->execute([$userId]);
            $message = 'User promoted to admin successfully';
            break;

        case 'remove_admin':
            // Убираем права администратора
            $stmt = $pdo->prepare("UPDATE users SET is_admin = 0 WHERE user_id = ?");
            $stmt->execute([$userId]);
            $message = 'Admin privileges removed successfully';
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 
