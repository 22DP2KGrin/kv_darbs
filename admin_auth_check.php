<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }

        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !isset($headers['Authorization'])) {
            $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return $headers;
    }
}

function checkAdminAuth() {
    error_log("Checking admin auth...");
    error_log("Session data: " . print_r($_SESSION, true));
    
    // Pārbaudām sesiju
    if (isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        error_log("Session auth passed");
        return true;
    }

    // Ja sesijas nav, pārbaudām tokenu
    $headers = getallheaders();
    error_log("Request headers: " . print_r($headers, true));
    
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    error_log("Auth header: " . $authHeader);
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        error_log("Auth header check failed: empty or invalid format");
        return false;
    }

    $token = $matches[1];
    error_log("Token from header: " . $token);

    // Pārbaudām tokenu datubāzē
    require_once __DIR__ . '/db_connect.php';
    try {
        $stmt = $pdo->prepare("
            SELECT admin_id 
            FROM admin_sessions 
            WHERE session_token = ? 
            AND expires_at > NOW() 
            AND admin_id IN (SELECT id FROM admins WHERE is_active = 1)
        ");
        $stmt->execute([$token]);
        $session = $stmt->fetch();

        if ($session) {
            error_log("Token auth passed for admin_id: " . $session['admin_id']);
            // Iestatām sesiju
            $_SESSION['admin_id'] = $session['admin_id'];
            $_SESSION['is_admin'] = true;
            return true;
        }
    } catch (PDOException $e) {
        error_log("Database error during token check: " . $e->getMessage());
    }

    error_log("Auth check failed");
    return false;
}

// Ja fails tiek izsaukts tieši, pārbaudām autorizāciju
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    if (!checkAdminAuth()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}
?> 
