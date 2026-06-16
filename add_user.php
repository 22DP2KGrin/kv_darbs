<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once 'admin_auth_check.php';

if (!checkAdminAuth()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get POST data (accept JSON)
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields (language/timezone optional)
$required_fields = ['username', 'email', 'password'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Provide defaults for optional fields
if (empty($data['language'])) {
    $data['language'] = 'en';
}
if (empty($data['timezone'])) {
    $data['timezone'] = 'UTC';
}

try {
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$data['username'], $data['email']]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Username or email already exists']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, language, timezone, created_at, is_active)
        VALUES (?, ?, ?, ?, ?, NOW(), 1)
    ");

    $stmt->execute([
        $data['username'],
        $data['email'],
        $hashed_password,
        $data['language'],
        $data['timezone']
    ]);

    echo json_encode([
        'message' => 'User added successfully',
        'user_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log("Error adding user: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error adding user']);
}
?> 
