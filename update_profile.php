<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Pārbaudām pieprasījuma metodi
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Iegūstam datus no POST pieprasījuma
$data = json_decode(file_get_contents('php://input'), true);

// Pārbaudām autorizāciju
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'No authorization token provided']);
    exit;
}

try {
    // Pārbaudām tokenu un iegūstam lietotāja ID
    $stmt = $pdo->prepare("SELECT user_id FROM sessions WHERE session_token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired session']);
        exit;
    }

    $userId = $session['user_id'];

    // Pārbaudām, vai e-pastu jau neizmanto cits lietotājs
    if (isset($data['email'])) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email is already taken']);
            exit;
        }
    }

    // Sākam transakciju
    $pdo->beginTransaction();

    // Atjauninām lietotāja pamatinformāciju
    $updateFields = [];
    $params = [];

    if (isset($data['username'])) {
        $updateFields[] = "username = ?";
        $params[] = $data['username'];
    }

    if (isset($data['email'])) {
        $updateFields[] = "email = ?";
        $params[] = $data['email'];
    }

    if (isset($data['language'])) {
        $updateFields[] = "language = ?";
        $params[] = $data['language'];
    }

    if (isset($data['timezone'])) {
        $updateFields[] = "timezone = ?";
        $params[] = $data['timezone'];
    }

    // Ja ir jauna parole, atjauninām to
    if (isset($data['newPassword']) && !empty($data['newPassword'])) {
        // Pārbaudām pašreizējo paroli
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($data['currentPassword'], $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }

        $updateFields[] = "password_hash = ?";
        $params[] = password_hash($data['newPassword'], PASSWORD_DEFAULT);
    }

    if (!empty($updateFields)) {
        $params[] = $userId; // Pievienojam user_id WHERE nosacījumam
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    // Iegūstam atjauninātos lietotāja datus
    $stmt = $pdo->prepare("SELECT user_id, username, email, created_at, language, timezone FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pabeidzam transakciju
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'user' => $user
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?> 
