<?php
ob_start();
header('Content-Type: application/json');

function sendAdminJson($payload, $statusCode = 200) {
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    error_log('Fatal admin login error: ' . $error['message']);
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error during admin login.',
        'error_details' => $error['message']
    ]);
});

// Set session cookie parameters before starting session
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

error_reporting(E_ALL);
ini_set('display_errors', 0);
if (!session_start()) {
    sendAdminJson([
        'success' => false,
        'message' => 'Could not start admin session.'
    ], 500);
}

// Clear any existing session data
$_SESSION = array();

// If a session cookie exists, destroy it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Regenerate session ID
session_regenerate_id(true);

$rawInput = file_get_contents('php://input');

// Log incoming request
error_log("Admin login attempt - Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Raw input: " . $rawInput);

// Database connection
try {
    require_once __DIR__ . '/db_connect.php';
} catch (Throwable $e) {
    error_log("Admin database connection bootstrap error: " . $e->getMessage());
    sendAdminJson([
        'success' => false,
        'message' => 'Database connection failed.',
        'error_details' => $e->getMessage()
    ], 500);
}

// Function to verify password
function verifyPassword($password, $hash) {
    error_log("Verifying password. Hash from DB: " . $hash);
    $result = password_verify($password, $hash);
    error_log("Password verification result: " . ($result ? 'true' : 'false'));
    return $result;
}

// Function to generate session token
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

// Function to log admin activity
function logAdminActivity($adminId, $action, $description) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (admin_id, action, description, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $adminId, 
            $action, 
            $description, 
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Failed to log admin activity: " . $e->getMessage());
        return false;
    }
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode($rawInput, true);
    error_log("=== Admin Login Attempt ===");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Raw input: " . $rawInput);
    error_log("Decoded input data: " . print_r($data, true));
    error_log("Session status: " . print_r($_SESSION, true));
    
    if (!isset($data['email']) || !isset($data['password'])) {
        error_log("Missing email or password in request");
        sendAdminJson([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
    }

    try {
        // Get admin from database
        error_log("Attempting to connect to database...");
        error_log("Database connection parameters: " . print_r([
            'host' => DB_HOST,
            'dbname' => DB_NAME,
            'user' => DB_USER
        ], true));

        $stmt = $pdo->prepare("
            SELECT id, username, email, password, role, permissions, is_active
            FROM admins
            WHERE email = ?
        ");
        error_log("SQL Query prepared: " . $stmt->queryString);
        error_log("Executing query with email: " . $data['email']);
        
        $stmt->execute([$data['email']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Admin lookup result: " . ($admin ? "Found" : "Not found"));
        if ($admin) {
            error_log("Admin details (excluding password): " . print_r(array_diff_key($admin, ['password' => '']), true));
        }

        if (!$admin) {
            error_log("Admin not found for email: " . $data['email']);
            sendAdminJson([
                'success' => false,
                'message' => 'User not found. Please check your email and try again.'
            ]);
        }

        if (!$admin['is_active']) {
            error_log("Inactive admin account: " . $data['email']);
            sendAdminJson([
                'success' => false,
                'message' => 'This account has been deactivated. Please contact support.'
            ]);
        }

        // Verify password
        error_log("Attempting password verification for admin: " . $admin['id']);
        if (!verifyPassword($data['password'], $admin['password'])) {
            error_log("Password verification failed for admin: " . $admin['id']);
            // Try to log failed login attempt, but don't stop if it fails
            logAdminActivity($admin['id'], 'LOGIN_FAILED', 'Failed login attempt');
            
            sendAdminJson([
                'success' => false,
                'message' => 'Invalid password. Please try again.'
            ]);
        }
        error_log("Password verification successful for admin: " . $admin['id']);

        // Generate session token
        $sessionToken = generateSessionToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        error_log("Generated session token: " . $sessionToken);
        error_log("Session expires at: " . $expiresAt);

        try {
            // Create session in database
            $stmt = $pdo->prepare("
                INSERT INTO admin_sessions (admin_id, session_token, expires_at, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $admin['id'],
                $sessionToken,
                $expiresAt,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            error_log("Session created successfully in database");

            // Set session data
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_token'] = $sessionToken;
            $_SESSION['admin_expires'] = $expiresAt;
            $_SESSION['is_admin'] = true;
            error_log("Session data saved to PHP session: " . print_r($_SESSION, true));

            // Update last login
            $stmt = $pdo->prepare("
                UPDATE admins
                SET last_login = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$admin['id']]);
            error_log("Last login timestamp updated");

            // Try to log successful login
            logAdminActivity($admin['id'], 'LOGIN_SUCCESS', 'Successful login');
            error_log("Login activity logged");

            // Return success response
            $response = [
                'success' => true,
                'message' => 'Login successful',
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'email' => $admin['email'],
                    'role' => $admin['role'],
                    'permissions' => json_decode($admin['permissions'], true)
                ],
                'session' => [
                    'token' => $sessionToken,
                    'expires' => $expiresAt
                ]
            ];
            error_log("Sending success response: " . print_r($response, true));
            sendAdminJson($response);

        } catch (Throwable $e) {
            error_log("Session creation error: " . $e->getMessage());
            sendAdminJson([
                'success' => false,
                'message' => 'Failed to create session. Please try again.',
                'error_details' => $e->getMessage()
            ], 500);
        }

    } catch (Throwable $e) {
        error_log("Admin login error: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Error trace: " . $e->getTraceAsString());
        sendAdminJson([
            'success' => false,
            'message' => 'Admin login failed. Please try again later.',
            'error_details' => $e->getMessage()
        ], 500);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    sendAdminJson([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
