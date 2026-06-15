<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config.php';

$expectedToken = getenv('IMPORT_TOKEN');
$providedToken = $_GET['token'] ?? $_POST['token'] ?? '';
$confirmed = ($_GET['confirm'] ?? $_POST['confirm'] ?? '') === '1';

function maskedDatabaseUrlInfo() {
    $databaseUrl = getenv('DATABASE_URL');
    if ($databaseUrl === false || $databaseUrl === '') {
        return [
            'status' => 'EMPTY',
            'host' => 'EMPTY',
            'database' => 'EMPTY',
        ];
    }

    $parts = parse_url($databaseUrl);
    if ($parts === false) {
        return [
            'status' => 'SET but invalid',
            'host' => 'INVALID',
            'database' => 'INVALID',
        ];
    }

    return [
        'status' => 'SET',
        'host' => $parts['host'] ?? 'EMPTY',
        'database' => isset($parts['path']) ? ltrim($parts['path'], '/') : 'EMPTY',
    ];
}

if ($expectedToken === false || $expectedToken === '') {
    http_response_code(403);
    echo "Import is disabled.\n";
    echo "Set IMPORT_TOKEN in hosting environment variables first.\n";
    exit;
}

if (!hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    echo "Invalid import token.\n";
    exit;
}

if (!$confirmed) {
    $databaseUrlInfo = maskedDatabaseUrlInfo();

    echo "Ready to import database into DB_NAME=" . DB_NAME . ".\n";
    echo "Effective DB_HOST=" . DB_HOST . "\n";
    echo "DATABASE_URL=" . $databaseUrlInfo['status'] . "\n";
    echo "DATABASE_URL host=" . $databaseUrlInfo['host'] . "\n";
    echo "DATABASE_URL database=" . $databaseUrlInfo['database'] . "\n";
    echo "This will DROP and recreate project tables.\n";
    echo "Open this URL again with &confirm=1 to continue.\n";
    exit;
}

$sqlPath = __DIR__ . '/infinityfree_full_database.sql';
if (!is_file($sqlPath)) {
    http_response_code(500);
    echo "SQL file was not found: {$sqlPath}\n";
    exit;
}

function splitSqlStatements($sql) {
    $statements = [];
    $current = '';
    $length = strlen($sql);
    $quote = null;
    $escape = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $current .= $char;

        if ($escape) {
            $escape = false;
            continue;
        }

        if ($quote !== null && $char === '\\') {
            $escape = true;
            continue;
        }

        if ($char === "'" || $char === '"') {
            if ($quote === null) {
                $quote = $char;
            } elseif ($quote === $char) {
                $quote = null;
            }
            continue;
        }

        if ($char === ';' && $quote === null) {
            $statement = trim($current);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $current = '';
        }
    }

    $tail = trim($current);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}

function removeDatabaseSelection($sql) {
    $lines = preg_split('/\R/', $sql);
    $filtered = [];

    foreach ($lines as $line) {
        if (preg_match('/^\s*USE\s+/i', $line)) {
            continue;
        }
        $filtered[] = $line;
    }

    return implode("\n", $filtered);
}

$dsn = DB_SOCKET !== ''
    ? 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=utf8mb4'
    : 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $sql = file_get_contents($sqlPath);
    if ($sql === false) {
        throw new RuntimeException('Could not read SQL file.');
    }

    $sql = removeDatabaseSelection($sql);
    $statements = splitSqlStatements($sql);

    $executed = 0;
    foreach ($statements as $statement) {
        if ($statement === '' || strpos(ltrim($statement), '--') === 0) {
            continue;
        }

        $pdo->exec($statement);
        $executed++;
    }

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    echo "SUCCESS: Database import completed.\n";
    echo "Database: " . DB_NAME . "\n";
    echo "Executed statements: {$executed}\n";
    echo "Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "- {$table}\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERROR: Database import failed.\n";
    echo "Database: " . DB_NAME . "\n";
    echo "Host: " . DB_HOST . "\n";
    echo "Port: " . DB_PORT . "\n";
    echo "User: " . DB_USER . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}
