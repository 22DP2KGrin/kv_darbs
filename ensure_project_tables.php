<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/database.php';

$requiredTables = [
    'users',
    'languages',
    'topics',
    'tests',
    'questions',
    'answers',
    'sessions',
    'test_results',
    'test_errors',
    'exercise_results',
    'exercise_answers',
    'user_progress',
    'admins',
    'admin_sessions',
    'admin_activity_log'
];

echo "<h1>Project Tables Status</h1>";
echo "<p>Database: <strong>" . htmlspecialchars(DB_NAME) . "</strong></p>";
echo "<p>Host: <strong>" . htmlspecialchars(DB_HOST) . "</strong></p>";
echo "<p>Port: <strong>" . htmlspecialchars(DB_PORT) . "</strong></p>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";

    echo "<h2>Required Tables</h2>";
    echo "<ul>";
    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
        if ($stmt->fetch()) {
            echo "<li style='color: green;'>✓ $table</li>";
        } else {
            echo "<li style='color: red;'>✗ $table</li>";
        }
    }
    echo "</ul>";

    echo "<h2>New Tables Preview</h2>";
    foreach (['exercise_results', 'exercise_answers', 'user_progress'] as $table) {
        echo "<h3>" . htmlspecialchars($table) . "</h3>";
        $countStmt = $pdo->query("SELECT COUNT(*) AS count FROM `$table`");
        $count = (int) $countStmt->fetch()['count'];
        echo "<p>Rows: <strong>$count</strong></p>";
    }
} catch (Throwable $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
