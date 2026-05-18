<?php

require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    echo "Database setup completed successfully";
} catch (Throwable $e) {
    error_log("Database setup failed: " . $e->getMessage());
    throw $e;
}

?>
