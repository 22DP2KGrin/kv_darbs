<?php
$localConfigPath = __DIR__ . '/config/local.php';
$localConfig = file_exists($localConfigPath) ? require $localConfigPath : [];
if (!is_array($localConfig)) {
    $localConfig = [];
}

function appConfigValue($key, $default, $localConfig) {
    if (array_key_exists($key, $localConfig)) {
        return $localConfig[$key];
    }

    $value = getenv($key);
    return $value === false ? $default : $value;
}

function appFirstConfigValue($keys, $default, $localConfig) {
    foreach ($keys as $key) {
        $value = appConfigValue($key, null, $localConfig);
        if ($value !== null && $value !== '') {
            return $value;
        }
    }

    return $default;
}

function appDatabaseUrlConfig() {
    $databaseUrl = getenv('DATABASE_URL');
    if ($databaseUrl === false || $databaseUrl === '') {
        $databaseUrl = getenv('MYSQL_URL');
    }

    if ($databaseUrl === false || $databaseUrl === '') {
        return [];
    }

    $parts = parse_url($databaseUrl);
    if ($parts === false || !isset($parts['host'])) {
        return [];
    }

    return [
        'DB_HOST' => $parts['host'] ?? '',
        'DB_PORT' => isset($parts['port']) ? (string) $parts['port'] : '3306',
        'DB_NAME' => isset($parts['path']) ? ltrim($parts['path'], '/') : '',
        'DB_USER' => isset($parts['user']) ? urldecode($parts['user']) : '',
        'DB_PASS' => isset($parts['pass']) ? urldecode($parts['pass']) : ''
    ];
}

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

$appEnv = appConfigValue('APP_ENV', 'local', $localConfig);
$defaultSocket = $appEnv === 'production' ? '' : '/tmp/mysql.sock';
$databaseUrlConfig = appDatabaseUrlConfig();
$databaseConfig = array_merge($databaseUrlConfig, $localConfig);

// Database configuration. For hosting, set values in config/local.php or environment variables.
define('DB_HOST', appFirstConfigValue(['DB_HOST', 'MYSQLHOST', 'MYSQL_HOST'], 'localhost', $databaseConfig));
define('DB_PORT', appFirstConfigValue(['DB_PORT', 'MYSQLPORT', 'MYSQL_PORT'], '3306', $databaseConfig));
define('DB_NAME', appFirstConfigValue(['DB_NAME', 'MYSQLDATABASE', 'MYSQL_DATABASE'], 'language_learning_platform', $databaseConfig));
define('DB_USER', appFirstConfigValue(['DB_USER', 'MYSQLUSER', 'MYSQL_USER'], 'root', $databaseConfig));
define('DB_PASS', appFirstConfigValue(['DB_PASS', 'MYSQLPASSWORD', 'MYSQL_PASSWORD'], 'root', $databaseConfig));
define('DB_SOCKET', appConfigValue('DB_SOCKET', $defaultSocket, $databaseConfig));
// Session configuration
define('SESSION_LIFETIME', 86400); // 24 hours in seconds

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', $appEnv === 'production' ? 0 : 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

// Set timezone
date_default_timezone_set('UTC');

// Function to create database tables
function createTables($pdo) {
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                country VARCHAR(100),
                phone VARCHAR(20),
                birth_date DATE,
                gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
                bio TEXT,
                language VARCHAR(10) DEFAULT 'en',
                timezone VARCHAR(50) DEFAULT 'UTC',
                avatar VARCHAR(255) DEFAULT NULL,
                is_admin BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS languages (
                language_id INT AUTO_INCREMENT PRIMARY KEY,
                language_name VARCHAR(50) NOT NULL UNIQUE,
                language_code VARCHAR(10) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_language_code (language_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS topics (
                topic_id INT AUTO_INCREMENT PRIMARY KEY,
                language_id INT NOT NULL,
                topic_name VARCHAR(100) NOT NULL,
                description TEXT,
                difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE,
                INDEX idx_language_difficulty (language_id, difficulty_level)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS sessions (
                session_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                session_token VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                INDEX idx_session_token (session_token),
                INDEX idx_user_id (user_id),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS test_results (
                result_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                topic_id INT NOT NULL,
                score INT NOT NULL,
                max_score INT NOT NULL,
                time_spent INT NOT NULL,
                completion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (topic_id) REFERENCES topics(topic_id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_topic (user_id, topic_id),
                INDEX idx_result_user_id (user_id),
                INDEX idx_result_topic_id (topic_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS test_errors (
                error_id INT AUTO_INCREMENT PRIMARY KEY,
                result_id INT NOT NULL,
                question_id INT NOT NULL,
                user_answer TEXT,
                correct_answer TEXT,
                question_text TEXT,
                FOREIGN KEY (result_id) REFERENCES test_results(result_id) ON DELETE CASCADE,
                INDEX idx_result_id (result_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS exercise_results (
                exercise_result_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                topic_id INT NOT NULL,
                exercise_slug VARCHAR(120) NOT NULL,
                exercise_type VARCHAR(50) DEFAULT 'practice',
                score INT NOT NULL DEFAULT 0,
                max_score INT NOT NULL DEFAULT 0,
                time_spent INT NOT NULL DEFAULT 0,
                content_text LONGTEXT DEFAULT NULL,
                completion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (topic_id) REFERENCES topics(topic_id) ON DELETE CASCADE,
                INDEX idx_exercise_result_user (user_id),
                INDEX idx_exercise_result_topic (topic_id),
                INDEX idx_exercise_result_slug (exercise_slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS exercise_answers (
                exercise_answer_id INT AUTO_INCREMENT PRIMARY KEY,
                exercise_result_id INT NOT NULL,
                question_id INT DEFAULT NULL,
                question_text TEXT,
                user_answer TEXT,
                correct_answer TEXT,
                is_correct BOOLEAN DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (exercise_result_id) REFERENCES exercise_results(exercise_result_id) ON DELETE CASCADE,
                INDEX idx_exercise_answer_result (exercise_result_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS user_progress (
                progress_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                topic_id INT NOT NULL,
                completed_exercises INT NOT NULL DEFAULT 0,
                exercise_attempts INT NOT NULL DEFAULT 0,
                best_exercise_score DECIMAL(5,2) NOT NULL DEFAULT 0,
                avg_exercise_score DECIMAL(5,2) NOT NULL DEFAULT 0,
                completed_tests INT NOT NULL DEFAULT 0,
                test_attempts INT NOT NULL DEFAULT 0,
                best_test_score DECIMAL(5,2) NOT NULL DEFAULT 0,
                avg_test_score DECIMAL(5,2) NOT NULL DEFAULT 0,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_topic_progress (user_id, topic_id),
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (topic_id) REFERENCES topics(topic_id) ON DELETE CASCADE,
                INDEX idx_progress_user (user_id),
                INDEX idx_progress_topic (topic_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS tests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                test_name VARCHAR(255) NOT NULL,
                language_id INT NOT NULL,
                topic_id INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE,
                FOREIGN KEY (topic_id) REFERENCES topics(topic_id) ON DELETE SET NULL,
                INDEX idx_test_language (language_id),
                INDEX idx_test_topic (topic_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                test_id INT NOT NULL,
                question_text TEXT NOT NULL,
                question_order INT DEFAULT 0,
                question_type VARCHAR(50) DEFAULT 'single',
                FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
                INDEX idx_question_test (test_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS answers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                question_id INT NOT NULL,
                answer_text TEXT NOT NULL,
                is_correct BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
                INDEX idx_answer_question (question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('super_admin', 'admin', 'moderator') NOT NULL DEFAULT 'admin',
                permissions JSON,
                is_active BOOLEAN DEFAULT TRUE,
                last_login DATETIME DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS admin_sessions (
                session_id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                session_token VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
                INDEX idx_admin_session_token (session_token),
                INDEX idx_admin_session_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS admin_activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
                INDEX idx_admin_activity_admin (admin_id),
                INDEX idx_admin_activity_user (user_id),
                INDEX idx_admin_activity_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $legacySessionColumn = $pdo->query("SHOW COLUMNS FROM sessions LIKE 'token'")->fetch();
        if ($legacySessionColumn) {
            $pdo->exec("ALTER TABLE sessions CHANGE token session_token VARCHAR(255) NOT NULL");
        }

        $avatarColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'")->fetch();
        if (!$avatarColumn) {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER timezone");
        }

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO languages (language_id, language_name, language_code) VALUES
            (1, 'English', 'en'),
            (2, 'French', 'fr'),
            (3, 'Spanish', 'es'),
            (4, 'Latvian', 'lv')
        ");
        $stmt->execute();

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO topics (topic_id, language_id, topic_name, description, difficulty_level) VALUES
            (1, 1, 'Basic Vocabulary', 'Basic vocabulary exercise.', 'beginner'),
            (2, 1, 'Present Simple', 'Present simple tense exercise.', 'beginner'),
            (3, 1, 'My Daily Routine', 'Daily routine vocabulary and grammar.', 'beginner'),
            (4, 1, 'Introducing Yourself', 'Self introduction exercise.', 'beginner'),
            (5, 1, 'Opinion Essay', 'Opinion essay writing.', 'intermediate'),
            (6, 1, 'Idiomatic Expressions', 'Idiomatic expressions practice.', 'intermediate'),
            (7, 1, 'Conditionals Wishes', 'Conditionals and wishes exercise.', 'intermediate'),
            (8, 1, 'City vs Country', 'City versus country vocabulary and discussion.', 'intermediate'),
            (9, 1, 'Phrasal Verbs', 'Phrasal verbs practice.', 'intermediate'),
            (10, 1, 'Present Perfect vs Past Simple', 'Present perfect versus past simple exercise.', 'intermediate'),
            (41, 1, 'English Level Test', 'Overall English proficiency level test.', 'advanced'),
            (11, 2, 'Vocabulaire de base', 'French basic vocabulary exercise.', 'beginner'),
            (12, 2, 'Présent de l’indicatif', 'French present tense practice.', 'beginner'),
            (13, 2, 'Ma routine quotidienne', 'French daily routine practice.', 'beginner'),
            (14, 2, 'Présente-toi', 'French self introduction exercise.', 'beginner'),
            (33, 2, 'Passé composé', 'French past tense practice.', 'intermediate'),
            (34, 2, 'Voyages et directions', 'French travel vocabulary and directions.', 'intermediate'),
            (35, 2, 'Au restaurant', 'French restaurant dialogues and comprehension.', 'intermediate'),
            (36, 2, 'Exprimer son opinion', 'French opinion phrases and connectors.', 'intermediate'),
            (37, 2, 'Subjonctif de base', 'French introductory subjunctive practice.', 'advanced'),
            (38, 2, 'Expressions idiomatiques', 'Advanced French idiomatic expressions.', 'advanced'),
            (39, 2, 'Email formel', 'Formal email and register in French.', 'advanced'),
            (40, 2, 'Actualités et débat', 'Advanced reading and argumentation in French.', 'advanced'),
            (32, 2, 'Test de niveau de français', 'Overall French proficiency level test.', 'advanced'),
            (15, 3, 'Vocabulario básico', 'Spanish basic vocabulary exercise.', 'beginner'),
            (16, 3, 'Presente de indicativo', 'Spanish present tense practice.', 'beginner'),
            (17, 3, 'Mi rutina diaria', 'Spanish daily routine practice.', 'beginner'),
            (18, 3, 'Preséntate', 'Spanish self introduction exercise.', 'beginner'),
            (23, 3, 'Pretérito Indefinido', 'Spanish past tense practice.', 'intermediate'),
            (24, 3, 'Viajes y direcciones', 'Spanish travel vocabulary and directions.', 'intermediate'),
            (25, 3, 'En el restaurante', 'Spanish restaurant dialogues and comprehension.', 'intermediate'),
            (26, 3, 'Expresar opiniones', 'Spanish opinion phrases and connectors.', 'intermediate'),
            (27, 3, 'Subjuntivo básico', 'Spanish introductory subjunctive practice.', 'advanced'),
            (28, 3, 'Expresiones idiomáticas', 'Advanced Spanish idiomatic expressions.', 'advanced'),
            (29, 3, 'Correo formal', 'Formal email and register in Spanish.', 'advanced'),
            (30, 3, 'Noticias y debate', 'Advanced reading and argumentation in Spanish.', 'advanced'),
            (31, 3, 'Prueba de nivel de español', 'Overall Spanish proficiency level test.', 'advanced'),
            (19, 4, 'Basic Vocabulary', 'Latvian basic vocabulary exercise.', 'beginner'),
            (20, 4, 'Present Simple', 'Latvian grammar style exercise.', 'beginner'),
            (21, 4, 'My Daily Routine', 'Latvian daily routine practice.', 'beginner'),
            (22, 4, 'Introducing Yourself', 'Latvian self introduction exercise.', 'beginner'),
            (42, 4, 'Latviešu valodas līmeņa tests', 'Overall Latvian proficiency level test.', 'advanced')
        ");
        $stmt->execute();

        $stmt = $pdo->prepare("
            UPDATE topics
            SET topic_name = CASE topic_id
                WHEN 41 THEN 'English Level Test'
                ELSE topic_name
            END,
            description = CASE topic_id
                WHEN 41 THEN 'Overall English proficiency level test.'
                ELSE description
            END,
            difficulty_level = CASE topic_id
                WHEN 41 THEN 'advanced'
                ELSE difficulty_level
            END
            WHERE language_id = 1 OR topic_id = 41
        ");
        $stmt->execute();

        $stmt = $pdo->prepare("
            UPDATE topics
            SET topic_name = CASE topic_id
                WHEN 11 THEN 'Vocabulaire de base'
                WHEN 12 THEN 'Présent de l’indicatif'
                WHEN 13 THEN 'Ma routine quotidienne'
                WHEN 14 THEN 'Présente-toi'
                WHEN 33 THEN 'Passé composé'
                WHEN 34 THEN 'Voyages et directions'
                WHEN 35 THEN 'Au restaurant'
                WHEN 36 THEN 'Exprimer son opinion'
                WHEN 37 THEN 'Subjonctif de base'
                WHEN 38 THEN 'Expressions idiomatiques'
                WHEN 39 THEN 'Email formel'
                WHEN 40 THEN 'Actualités et débat'
                WHEN 32 THEN 'Test de niveau de français'
                ELSE topic_name
            END,
            description = CASE topic_id
                WHEN 11 THEN 'French basic vocabulary exercise.'
                WHEN 12 THEN 'French present tense practice.'
                WHEN 13 THEN 'French daily routine practice.'
                WHEN 14 THEN 'French self introduction exercise.'
                WHEN 33 THEN 'French past tense practice.'
                WHEN 34 THEN 'French travel vocabulary and directions.'
                WHEN 35 THEN 'French restaurant dialogues and comprehension.'
                WHEN 36 THEN 'French opinion phrases and connectors.'
                WHEN 37 THEN 'French introductory subjunctive practice.'
                WHEN 38 THEN 'Advanced French idiomatic expressions.'
                WHEN 39 THEN 'Formal email and register in French.'
                WHEN 40 THEN 'Advanced reading and argumentation in French.'
                WHEN 32 THEN 'Overall French proficiency level test.'
                ELSE description
            END,
            difficulty_level = CASE topic_id
                WHEN 11 THEN 'beginner'
                WHEN 12 THEN 'beginner'
                WHEN 13 THEN 'beginner'
                WHEN 14 THEN 'beginner'
                WHEN 33 THEN 'intermediate'
                WHEN 34 THEN 'intermediate'
                WHEN 35 THEN 'intermediate'
                WHEN 36 THEN 'intermediate'
                WHEN 37 THEN 'advanced'
                WHEN 38 THEN 'advanced'
                WHEN 39 THEN 'advanced'
                WHEN 40 THEN 'advanced'
                WHEN 32 THEN 'advanced'
                ELSE difficulty_level
            END
            WHERE language_id = 2 OR topic_id IN (32, 33, 34, 35, 36, 37, 38, 39, 40)
        ");
        $stmt->execute();

        $stmt = $pdo->prepare("
            UPDATE topics
            SET topic_name = CASE topic_id
                WHEN 42 THEN 'Latviešu valodas līmeņa tests'
                ELSE topic_name
            END,
            description = CASE topic_id
                WHEN 42 THEN 'Overall Latvian proficiency level test.'
                ELSE description
            END,
            difficulty_level = CASE topic_id
                WHEN 42 THEN 'advanced'
                ELSE difficulty_level
            END
            WHERE language_id = 4 OR topic_id = 42
        ");
        $stmt->execute();

        $stmt = $pdo->prepare("
            UPDATE topics
            SET topic_name = CASE topic_id
                WHEN 15 THEN 'Vocabulario básico'
                WHEN 16 THEN 'Presente de indicativo'
                WHEN 17 THEN 'Mi rutina diaria'
                WHEN 18 THEN 'Preséntate'
                WHEN 23 THEN 'Pretérito Indefinido'
                WHEN 24 THEN 'Viajes y direcciones'
                WHEN 25 THEN 'En el restaurante'
                WHEN 26 THEN 'Expresar opiniones'
                WHEN 27 THEN 'Subjuntivo básico'
                WHEN 28 THEN 'Expresiones idiomáticas'
                WHEN 29 THEN 'Correo formal'
                WHEN 30 THEN 'Noticias y debate'
                WHEN 31 THEN 'Prueba de nivel de español'
                ELSE topic_name
            END,
            description = CASE topic_id
                WHEN 15 THEN 'Spanish basic vocabulary exercise.'
                WHEN 16 THEN 'Spanish present tense practice.'
                WHEN 17 THEN 'Spanish daily routine practice.'
                WHEN 18 THEN 'Spanish self introduction exercise.'
                WHEN 23 THEN 'Spanish past tense practice.'
                WHEN 24 THEN 'Spanish travel vocabulary and directions.'
                WHEN 25 THEN 'Spanish restaurant dialogues and comprehension.'
                WHEN 26 THEN 'Spanish opinion phrases and connectors.'
                WHEN 27 THEN 'Spanish introductory subjunctive practice.'
                WHEN 28 THEN 'Advanced Spanish idiomatic expressions.'
                WHEN 29 THEN 'Formal email and register in Spanish.'
                WHEN 30 THEN 'Advanced reading and argumentation in Spanish.'
                WHEN 31 THEN 'Overall Spanish proficiency level test.'
                ELSE description
            END,
            difficulty_level = CASE topic_id
                WHEN 15 THEN 'beginner'
                WHEN 16 THEN 'beginner'
                WHEN 17 THEN 'beginner'
                WHEN 18 THEN 'beginner'
                WHEN 23 THEN 'intermediate'
                WHEN 24 THEN 'intermediate'
                WHEN 25 THEN 'intermediate'
                WHEN 26 THEN 'intermediate'
                WHEN 27 THEN 'advanced'
                WHEN 28 THEN 'advanced'
                WHEN 29 THEN 'advanced'
                WHEN 30 THEN 'advanced'
                WHEN 31 THEN 'advanced'
                ELSE difficulty_level
            END
            WHERE language_id = 3
        ");
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = 'admin@languageplatform.com'");
        $stmt->execute();
        if ((int) $stmt->fetchColumn() === 0) {
            $password = password_hash('LinguaAdmin@2025!', PASSWORD_DEFAULT);
            $permissions = json_encode([
                'canApproveUsers' => true,
                'canManageContent' => true,
                'canViewAnalytics' => true,
                'canManageAdmins' => true,
                'canModerateContent' => true,
                'canAccessReports' => true
            ]);

            $stmt = $pdo->prepare("
                INSERT INTO admins (username, email, password, role, permissions, is_active)
                VALUES ('PlatformAdmin', 'admin@languageplatform.com', ?, 'super_admin', ?, TRUE)
            ");
            $stmt->execute([$password, $permissions]);
        }

        return true;
    } catch (PDOException $e) {
        error_log("Error creating tables: " . $e->getMessage());
        return false;
    }
}

// Function to initialize database connection
function initDatabase() {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $passwordCandidates = [DB_PASS];
    if (DB_PASS === '') {
        $passwordCandidates[] = 'root';
    }

    $dsnCandidates = [];

    if (DB_SOCKET !== '') {
        $dsnCandidates[] = "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }

    $dsnCandidates[] = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $dsnCandidates[] = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    if (DB_HOST !== 'localhost') {
        $dsnCandidates[] = "mysql:host=localhost;dbname=" . DB_NAME . ";charset=utf8mb4";
        $dsnCandidates[] = "mysql:host=localhost;port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }

    $lastError = null;

    foreach ($dsnCandidates as $dsn) {
        foreach ($passwordCandidates as $password) {
            try {
                $pdo = new PDO($dsn, DB_USER, $password, $options);

                // Create tables if they don't exist
                if (!createTables($pdo)) {
                    throw new Exception("Failed to create database tables");
                }

                return $pdo;
            } catch (PDOException $e) {
                $lastError = $e;
            }
        }
    }

    if ($lastError instanceof PDOException) {
        error_log("Database connection error: " . $lastError->getMessage());
    }

    throw new Exception("Database connection failed");
}

function getDBConnection() {
    return initDatabase();
}

// Initialize database connection
try {
    $pdo = initDatabase();
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
}
?>
