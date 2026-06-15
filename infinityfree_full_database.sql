-- Full database import for InfinityFree/phpMyAdmin.
-- Select your existing InfinityFree database in phpMyAdmin before importing this file.

SET NAMES utf8mb4;
USE if0_41951739_language;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS exercise_answers;
DROP TABLE IF EXISTS exercise_results;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS test_errors;
DROP TABLE IF EXISTS test_results;
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS tests;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS admin_activity_log;
DROP TABLE IF EXISTS admin_sessions;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS topics;
DROP TABLE IF EXISTS languages;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
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

CREATE TABLE languages (
    language_id INT AUTO_INCREMENT PRIMARY KEY,
    language_name VARCHAR(50) NOT NULL UNIQUE,
    language_code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_language_code (language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE topics (
    topic_id INT AUTO_INCREMENT PRIMARY KEY,
    language_id INT NOT NULL,
    topic_name VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (language_id) REFERENCES languages(language_id) ON DELETE CASCADE,
    INDEX idx_language_difficulty (language_id, difficulty_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tests (
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

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_order INT DEFAULT 0,
    question_type VARCHAR(50) DEFAULT 'single',
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE,
    INDEX idx_question_test (test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_answer_question (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    user_agent VARCHAR(255) NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admins (
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

CREATE TABLE admin_sessions (
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

CREATE TABLE admin_activity_log (
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

CREATE TABLE test_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic_id INT NOT NULL,
    score INT NOT NULL,
    max_score INT NOT NULL,
    time_spent INT NOT NULL COMMENT 'Time spent in seconds',
    completion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(topic_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_topic (user_id, topic_id),
    INDEX idx_user_id (user_id),
    INDEX idx_topic_id (topic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE test_errors (
    error_id INT AUTO_INCREMENT PRIMARY KEY,
    result_id INT NOT NULL,
    question_id INT NOT NULL,
    user_answer TEXT,
    correct_answer TEXT,
    question_text TEXT,
    FOREIGN KEY (result_id) REFERENCES test_results(result_id) ON DELETE CASCADE,
    INDEX idx_result_id (result_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE exercise_results (
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

CREATE TABLE exercise_answers (
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

CREATE TABLE user_progress (
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

INSERT INTO languages (language_id, language_name, language_code) VALUES
(1, 'English', 'en'),
(2, 'French', 'fr'),
(3, 'Spanish', 'es'),
(4, 'Latvian', 'lv');

INSERT INTO topics (topic_id, language_id, topic_name, description, difficulty_level) VALUES
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
(15, 3, 'Vocabulario básico', 'Spanish basic vocabulary exercise.', 'beginner'),
(16, 3, 'Presente de indicativo', 'Spanish present tense practice.', 'beginner'),
(17, 3, 'Mi rutina diaria', 'Spanish daily routine practice.', 'beginner'),
(18, 3, 'Preséntate', 'Spanish self introduction exercise.', 'beginner'),
(19, 4, 'Basic Vocabulary', 'Latvian basic vocabulary exercise.', 'beginner'),
(20, 4, 'Present Simple', 'Latvian grammar style exercise.', 'beginner'),
(21, 4, 'My Daily Routine', 'Latvian daily routine practice.', 'beginner'),
(22, 4, 'Introducing Yourself', 'Latvian self introduction exercise.', 'beginner'),
(42, 4, 'Latviešu valodas līmeņa tests', 'Overall Latvian proficiency level test.', 'advanced'),
(23, 3, 'Pretérito Indefinido', 'Spanish past tense practice.', 'intermediate'),
(24, 3, 'Viajes y direcciones', 'Spanish travel vocabulary and directions.', 'intermediate'),
(25, 3, 'En el restaurante', 'Spanish restaurant dialogues and comprehension.', 'intermediate'),
(26, 3, 'Expresar opiniones', 'Spanish opinion phrases and connectors.', 'intermediate'),
(27, 3, 'Subjuntivo básico', 'Spanish introductory subjunctive practice.', 'advanced'),
(28, 3, 'Expresiones idiomáticas', 'Advanced Spanish idiomatic expressions.', 'advanced'),
(29, 3, 'Correo formal', 'Formal email and register in Spanish.', 'advanced'),
(30, 3, 'Noticias y debate', 'Advanced reading and argumentation in Spanish.', 'advanced'),
(31, 3, 'Prueba de nivel de español', 'Overall Spanish proficiency level test.', 'advanced'),
(32, 2, 'Test de niveau de français', 'Overall French proficiency level test.', 'advanced'),
(33, 2, 'Passé composé', 'French past tense practice.', 'intermediate'),
(34, 2, 'Voyages et directions', 'French travel vocabulary and directions.', 'intermediate'),
(35, 2, 'Au restaurant', 'French restaurant dialogues and comprehension.', 'intermediate'),
(36, 2, 'Exprimer son opinion', 'French opinion phrases and connectors.', 'intermediate'),
(37, 2, 'Subjonctif de base', 'French introductory subjunctive practice.', 'advanced'),
(38, 2, 'Expressions idiomatiques', 'Advanced French idiomatic expressions.', 'advanced'),
(39, 2, 'Email formel', 'Formal email and register in French.', 'advanced'),
(40, 2, 'Actualités et débat', 'Advanced reading and argumentation in French.', 'advanced');

INSERT INTO admins (username, email, password, role, permissions, is_active) VALUES (
    'PlatformAdmin',
    'admin@languageplatform.com',
    '$2y$12$xegYKsnnx6FPV0R7/Zz84ez9AvM.p8U3iXb2obn5mzL0XM1WE/w4K',
    'super_admin',
    JSON_OBJECT(
        'canApproveUsers', true,
        'canManageCourses', true,
        'canManageContent', true,
        'canViewAnalytics', true,
        'canManageAdmins', true,
        'canModerateContent', true,
        'canAccessReports', true
    ),
    TRUE
);
