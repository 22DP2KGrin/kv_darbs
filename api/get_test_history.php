<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function executeQuery($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("SQL Error in query: $query");
        error_log("Parameters: " . json_encode($params));
        error_log("Error message: " . $e->getMessage());
        throw new Exception("Database query failed: " . $e->getMessage());
    }
}

function repairSpanishExerciseTopics($pdo) {
    $slugMap = [
        'spanish-basic-vocabulary' => 'Vocabulario básico',
        'spanish-present-simple' => 'Presente de indicativo',
        'spanish-mydaily-routine' => 'Mi rutina diaria',
        'spanish-introducing-yourself' => 'Preséntate',
        'spanish-past-tense' => 'Pretérito Indefinido',
        'spanish-travel-vocabulary' => 'Viajes y direcciones',
        'spanish-restaurant-dialogues' => 'En el restaurante',
        'spanish-opinion-connectors' => 'Expresar opiniones',
        'spanish-subjunctive-basics' => 'Subjuntivo básico',
        'spanish-idiomatic-expressions-advanced' => 'Expresiones idiomáticas',
        'spanish-formal-email' => 'Correo formal',
        'spanish-news-and-debate' => 'Noticias y debate'
    ];

    $selectTopicStmt = $pdo->prepare("
        SELECT t.topic_id
        FROM topics t
        JOIN languages l ON t.language_id = l.language_id
        WHERE l.language_code = 'es' AND t.topic_name = ?
        LIMIT 1
    ");

    $updateStmt = $pdo->prepare("
        UPDATE exercise_results
        SET topic_id = ?
        WHERE exercise_slug = ? AND topic_id <> ?
    ");

    foreach ($slugMap as $slug => $topicName) {
        $selectTopicStmt->execute([$topicName]);
        $topic = $selectTopicStmt->fetch(PDO::FETCH_ASSOC);
        if (!$topic) {
            continue;
        }

        $resolvedTopicId = (int) $topic['topic_id'];
        $updateStmt->execute([$resolvedTopicId, $slug, $resolvedTopicId]);
    }
}

function repairFrenchExerciseTopics($pdo) {
    $slugMap = [
        'french-basic-vocabulary' => 'Vocabulaire de base',
        'french-present-simple' => 'Présent de l’indicatif',
        'french-mydaily-routine' => 'Ma routine quotidienne',
        'french-introducing-yourself' => 'Présente-toi',
        'french-passe-compose' => 'Passé composé',
        'french-travel-vocabulary' => 'Voyages et directions',
        'french-restaurant-dialogues' => 'Au restaurant',
        'french-opinion-connectors' => 'Exprimer son opinion',
        'french-subjunctive-basics' => 'Subjonctif de base',
        'french-idiomatic-expressions-advanced' => 'Expressions idiomatiques',
        'french-formal-email' => 'Email formel',
        'french-news-and-debate' => 'Actualités et débat'
    ];

    $selectTopicStmt = $pdo->prepare("
        SELECT t.topic_id
        FROM topics t
        JOIN languages l ON t.language_id = l.language_id
        WHERE l.language_code = 'fr' AND t.topic_name = ?
        LIMIT 1
    ");

    $updateStmt = $pdo->prepare("
        UPDATE exercise_results
        SET topic_id = ?
        WHERE exercise_slug = ? AND topic_id <> ?
    ");

    foreach ($slugMap as $slug => $topicName) {
        $selectTopicStmt->execute([$topicName]);
        $topic = $selectTopicStmt->fetch(PDO::FETCH_ASSOC);
        if (!$topic) {
            continue;
        }

        $resolvedTopicId = (int) $topic['topic_id'];
        $updateStmt->execute([$resolvedTopicId, $slug, $resolvedTopicId]);
    }
}

try {
    // Получаем токен сессии из заголовка или POST данных
    $headers = getallheaders();
    $sessionToken = null;

    // Проверяем различные варианты получения токена
    if (isset($headers['Authorization'])) {
        $sessionToken = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($_POST['session_token'])) {
        $sessionToken = $_POST['session_token'];
    } elseif (isset($_GET['session_token'])) {
        $sessionToken = $_GET['session_token'];
    }

    // Если токен не найден, возвращаем ошибку без редиректа
    if (!$sessionToken) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Session token is required',
            'message' => 'Please log in to view your test history'
        ]);
        exit;
    }

    // Подключаемся к базе данных
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("Failed to connect to the database");
    }

    // Проверяем сессию и получаем информацию о пользователе
    $sessionQuery = "
        SELECT u.user_id, u.username, u.is_active 
        FROM sessions s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.session_token = ? AND s.expires_at > NOW()
    ";
    
    $sessionStmt = executeQuery($pdo, $sessionQuery, [$sessionToken]);
    $userData = $sessionStmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid or expired session',
            'message' => 'Your session has expired. Please log in again.'
        ]);
        exit;
    }

    if (!$userData['is_active']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'User account is inactive',
            'message' => 'Your account is currently inactive. Please contact support.'
        ]);
        exit;
    }

    repairFrenchExerciseTopics($pdo);
    repairSpanishExerciseTopics($pdo);

    // Получаем объединенную историю тестов и упражнений
    $testHistoryQuery = "
        SELECT 
            'test' AS result_type,
            tr.result_id,
            l.language_name,
            l.language_code,
            t.topic_id,
            t.topic_name,
            NULL AS exercise_slug,
            t.difficulty_level,
            tr.score,
            tr.max_score,
            tr.time_spent,
            tr.completion_date,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'question_id', te.question_id,
                        'question_text', te.question_text,
                        'user_answer', te.user_answer,
                        'correct_answer', te.correct_answer,
                        'is_correct', (te.user_answer <=> te.correct_answer)
                    )
                )
                FROM test_errors te
                WHERE te.result_id = tr.result_id
            ) AS question_details
        FROM test_results tr
        JOIN topics t ON tr.topic_id = t.topic_id
        JOIN languages l ON t.language_id = l.language_id
        WHERE tr.user_id = ?
        
        UNION ALL

        SELECT
            'exercise' AS result_type,
            er.exercise_result_id AS result_id,
            l.language_name,
            l.language_code,
            t.topic_id,
            t.topic_name,
            er.exercise_slug,
            t.difficulty_level,
            er.score,
            er.max_score,
            er.time_spent,
            er.completion_date,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'question_id', ea.question_id,
                        'question_text', ea.question_text,
                        'user_answer', ea.user_answer,
                        'correct_answer', ea.correct_answer,
                        'is_correct', ea.is_correct
                    )
                )
                FROM exercise_answers ea
                WHERE ea.exercise_result_id = er.exercise_result_id
            ) AS question_details
        FROM exercise_results er
        JOIN topics t ON er.topic_id = t.topic_id
        JOIN languages l ON t.language_id = l.language_id
        WHERE er.user_id = ?

        ORDER BY completion_date DESC
    ";

    $testHistoryStmt = executeQuery($pdo, $testHistoryQuery, [$userData['user_id'], $userData['user_id']]);
    $testResults = $testHistoryStmt->fetchAll(PDO::FETCH_ASSOC);

    // Обрабатываем результаты тестов
    foreach ($testResults as &$result) {
        if ($result['question_details']) {
            $result['question_details'] = json_decode($result['question_details'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error for result_id {$result['result_id']}: " . json_last_error_msg());
                $result['question_details'] = [];
            }
        } else {
            $result['question_details'] = [];
        }
    }

    // Получаем общую статистику
    $statsQuery = "
        SELECT 
            (
                SELECT COUNT(*) FROM test_results tr WHERE tr.user_id = ?
            ) AS total_tests,
            (
                SELECT COUNT(*) FROM exercise_results er WHERE er.user_id = ?
            ) AS total_exercises,
            (
                SELECT ROUND(AVG(score_percent), 2)
                FROM (
                    SELECT tr.score * 100.0 / NULLIF(tr.max_score, 0) AS score_percent
                    FROM test_results tr
                    WHERE tr.user_id = ?
                    UNION ALL
                    SELECT er.score * 100.0 / NULLIF(er.max_score, 0) AS score_percent
                    FROM exercise_results er
                    WHERE er.user_id = ?
                ) all_scores
            ) AS avg_score,
            (
                SELECT COALESCE(SUM(time_spent), 0) FROM (
                    SELECT tr.time_spent FROM test_results tr WHERE tr.user_id = ?
                    UNION ALL
                    SELECT er.time_spent FROM exercise_results er WHERE er.user_id = ?
                ) all_time
            ) AS total_time,
            (
                SELECT COUNT(*) FROM (
                    SELECT DISTINCT topic_id FROM test_results WHERE user_id = ?
                    UNION
                    SELECT DISTINCT topic_id FROM exercise_results WHERE user_id = ?
                ) all_topics
            ) AS topics_covered,
            (
                SELECT COALESCE(SUM(max_score), 0) FROM (
                    SELECT tr.max_score FROM test_results tr WHERE tr.user_id = ?
                    UNION ALL
                    SELECT er.max_score FROM exercise_results er WHERE er.user_id = ?
                ) all_questions
            ) AS total_questions,
            (
                SELECT COALESCE(SUM(score), 0) FROM (
                    SELECT tr.score FROM test_results tr WHERE tr.user_id = ?
                    UNION ALL
                    SELECT er.score FROM exercise_results er WHERE er.user_id = ?
                ) all_correct
            ) AS correct_answers
    ";

    $statsStmt = executeQuery($pdo, $statsQuery, [
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id'],
        $userData['user_id']
    ]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Получаем статистику по темам
    $topicStatsQuery = "
        SELECT 
            l.language_name,
            l.language_code,
            t.topic_name,
            COALESCE(SUM(combined.attempts), 0) as attempts,
            ROUND(COALESCE(AVG(combined.score_percent), 0), 2) as avg_score,
            COALESCE(SUM(combined.correct_answers), 0) as correct_answers,
            COALESCE(SUM(combined.total_questions), 0) as total_questions
        FROM topics t
        JOIN languages l ON t.language_id = l.language_id
        LEFT JOIN (
            SELECT topic_id, 1 AS attempts, score * 100.0 / NULLIF(max_score, 0) AS score_percent, score AS correct_answers, max_score AS total_questions
            FROM test_results
            WHERE user_id = ?
            UNION ALL
            SELECT topic_id, 1 AS attempts, score * 100.0 / NULLIF(max_score, 0) AS score_percent, score AS correct_answers, max_score AS total_questions
            FROM exercise_results
            WHERE user_id = ?
        ) combined ON t.topic_id = combined.topic_id
        GROUP BY t.topic_id, t.topic_name, l.language_name, l.language_code
        HAVING attempts > 0
        ORDER BY attempts DESC
    ";

    $topicStatsStmt = executeQuery($pdo, $topicStatsQuery, [$userData['user_id'], $userData['user_id']]);
    $topicStats = $topicStatsStmt->fetchAll(PDO::FETCH_ASSOC);

    $languageStatsQuery = "
        SELECT
            l.language_id,
            l.language_name,
            l.language_code,
            COUNT(*) AS attempts,
            ROUND(AVG(combined.score_percent), 2) AS avg_score,
            SUM(combined.correct_answers) AS correct_answers,
            SUM(combined.total_questions) AS total_questions
        FROM languages l
        JOIN (
            SELECT t.language_id, tr.score * 100.0 / NULLIF(tr.max_score, 0) AS score_percent, tr.score AS correct_answers, tr.max_score AS total_questions
            FROM test_results tr
            JOIN topics t ON tr.topic_id = t.topic_id
            WHERE tr.user_id = ?
            UNION ALL
            SELECT t.language_id, er.score * 100.0 / NULLIF(er.max_score, 0) AS score_percent, er.score AS correct_answers, er.max_score AS total_questions
            FROM exercise_results er
            JOIN topics t ON er.topic_id = t.topic_id
            WHERE er.user_id = ?
        ) combined ON l.language_id = combined.language_id
        GROUP BY l.language_id, l.language_name, l.language_code
        ORDER BY attempts DESC, l.language_name ASC
    ";

    $languageStatsStmt = executeQuery($pdo, $languageStatsQuery, [$userData['user_id'], $userData['user_id']]);
    $languageStats = $languageStatsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Обновляем время истечения сессии
    $updateSessionQuery = "
        UPDATE sessions 
        SET expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR) 
        WHERE session_token = ?
    ";
    executeQuery($pdo, $updateSessionQuery, [$sessionToken]);

    // Формируем ответ
    echo json_encode([
        'success' => true,
        'user' => [
            'username' => $userData['username'],
            'total_questions' => (int)$stats['total_questions'],
            'correct_answers' => (int)$stats['correct_answers'],
            'topics_covered' => (int)$stats['topics_covered']
        ],
        'statistics' => [
            'total_tests' => (int)$stats['total_tests'],
            'total_exercises' => (int)$stats['total_exercises'],
            'avg_score' => round((float)$stats['avg_score'], 2),
            'total_time' => (int)$stats['total_time'],
            'topics_covered' => (int)$stats['topics_covered']
        ],
        'language_statistics' => $languageStats,
        'topic_statistics' => $topicStats,
        'test_results' => $testResults
    ]);

} catch (Exception $e) {
    error_log("Error in get_test_history.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get test history',
        'message' => $e->getMessage()
    ]);
}
?> 
