<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function isLoggedIn() {
    // Pārbaudām, vai lietotājs ir autorizēts (parasts lietotājs vai administrators)
    if (isset($_SESSION['user_id'])) {
         // Parasts lietotājs: pārbaudām, vai lietotājs ir aktīvs
         require_once __DIR__ . '/../config/database.php';
         try {
              $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
              $stmt->execute([$_SESSION['user_id']]);
              $user = $stmt->fetch(PDO::FETCH_ASSOC);
              if ($user && $user['is_active']) {
                   return true;
              } else {
                   error_log("User (user_id: " . $_SESSION['user_id'] . ") not found or inactive.");
              }
         } catch (PDOException $e) {
              error_log("Database error in isLoggedIn (user): " . $e->getMessage());
         }
    } else if (isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
         // Administrators: ja sesijā ir admin_id un is_admin ir true, uzskatām par autorizētu
         return true;
    }
    // Ja nav autorizēts ne parasts lietotājs, ne administrators, atgriežam false
    return false;
}

// Ja fails tiek izsaukts tieši, pārbaudām autorizāciju
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}
?> 