<?php
header('Content-Type: application/json');
require_once '../config/database.php';

function respond($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requireFields($data, $fields) {
    foreach ($fields as $field) {
        if (!array_key_exists($field, $data)) {
            throw new Exception("Missing required field: $field");
        }
    }
}

function normalizeAnswerList($answers) {
    if (!is_array($answers)) {
        return [];
    }

    $normalized = [];

    foreach ($answers as $answer) {
        if (!is_array($answer)) {
            continue;
        }

        $normalized[] = [
            'question_id' => isset($answer['question_id']) ? (int) $answer['question_id'] : null,
            'question_text' => isset($answer['question_text']) ? (string) $answer['question_text'] : '',
            'user_answer' => array_key_exists('user_answer', $answer) ? (string) $answer['user_answer'] : null,
            'correct_answer' => array_key_exists('correct_answer', $answer) ? (string) $answer['correct_answer'] : null,
            'is_correct' => array_key_exists('is_correct', $answer) ? (int) !!$answer['is_correct'] : null
        ];
    }

    return $normalized;
}

function getTopicConfigByKeyOrSlug($data) {
    $topicKey = isset($data['topic_key']) ? trim((string) $data['topic_key']) : '';
    $exerciseSlug = isset($data['exercise_slug']) ? trim((string) $data['exercise_slug']) : '';

    $topicMap = [
        'english-level-test' => [
            'language_code' => 'en',
            'topic_name' => 'English Level Test',
            'description' => 'Overall English proficiency level test.',
            'difficulty_level' => 'advanced'
        ],
        'french-level-test' => [
            'language_code' => 'fr',
            'topic_name' => 'Test de niveau de français',
            'description' => 'Overall French proficiency level test.',
            'difficulty_level' => 'advanced'
        ],
        'french-basic-vocabulary' => [
            'language_code' => 'fr',
            'topic_name' => 'Vocabulaire de base',
            'description' => 'French basic vocabulary exercise.',
            'difficulty_level' => 'beginner'
        ],
        'french-present-simple' => [
            'language_code' => 'fr',
            'topic_name' => 'Présent de l’indicatif',
            'description' => 'French present tense practice.',
            'difficulty_level' => 'beginner'
        ],
        'french-mydaily-routine' => [
            'language_code' => 'fr',
            'topic_name' => 'Ma routine quotidienne',
            'description' => 'French daily routine practice.',
            'difficulty_level' => 'beginner'
        ],
        'french-introducing-yourself' => [
            'language_code' => 'fr',
            'topic_name' => 'Présente-toi',
            'description' => 'French self introduction exercise.',
            'difficulty_level' => 'beginner'
        ],
        'french-passe-compose' => [
            'language_code' => 'fr',
            'topic_name' => 'Passé composé',
            'description' => 'French past tense practice.',
            'difficulty_level' => 'intermediate'
        ],
        'french-travel-vocabulary' => [
            'language_code' => 'fr',
            'topic_name' => 'Voyages et directions',
            'description' => 'French travel vocabulary and directions.',
            'difficulty_level' => 'intermediate'
        ],
        'french-restaurant-dialogues' => [
            'language_code' => 'fr',
            'topic_name' => 'Au restaurant',
            'description' => 'French restaurant dialogues and comprehension.',
            'difficulty_level' => 'intermediate'
        ],
        'french-opinion-connectors' => [
            'language_code' => 'fr',
            'topic_name' => 'Exprimer son opinion',
            'description' => 'French opinion phrases and connectors.',
            'difficulty_level' => 'intermediate'
        ],
        'french-subjunctive-basics' => [
            'language_code' => 'fr',
            'topic_name' => 'Subjonctif de base',
            'description' => 'French introductory subjunctive practice.',
            'difficulty_level' => 'advanced'
        ],
        'french-idiomatic-expressions-advanced' => [
            'language_code' => 'fr',
            'topic_name' => 'Expressions idiomatiques',
            'description' => 'Advanced French idiomatic expressions.',
            'difficulty_level' => 'advanced'
        ],
        'french-formal-email' => [
            'language_code' => 'fr',
            'topic_name' => 'Email formel',
            'description' => 'Formal email and register in French.',
            'difficulty_level' => 'advanced'
        ],
        'french-news-and-debate' => [
            'language_code' => 'fr',
            'topic_name' => 'Actualités et débat',
            'description' => 'Advanced reading and argumentation in French.',
            'difficulty_level' => 'advanced'
        ],
        'spanish-level-test' => [
            'language_code' => 'es',
            'topic_name' => 'Prueba de nivel de español',
            'description' => 'Overall Spanish proficiency level test.',
            'difficulty_level' => 'advanced'
        ],
        'spanish-basic-vocabulary' => [
            'language_code' => 'es',
            'topic_name' => 'Vocabulario básico',
            'description' => 'Spanish basic vocabulary exercise.',
            'difficulty_level' => 'beginner'
        ],
        'spanish-present-simple' => [
            'language_code' => 'es',
            'topic_name' => 'Presente de indicativo',
            'description' => 'Spanish present tense practice.',
            'difficulty_level' => 'beginner'
        ],
        'spanish-mydaily-routine' => [
            'language_code' => 'es',
            'topic_name' => 'Mi rutina diaria',
            'description' => 'Spanish daily routine practice.',
            'difficulty_level' => 'beginner'
        ],
        'spanish-introducing-yourself' => [
            'language_code' => 'es',
            'topic_name' => 'Preséntate',
            'description' => 'Spanish self introduction exercise.',
            'difficulty_level' => 'beginner'
        ],
        'spanish-past-tense' => [
            'language_code' => 'es',
            'topic_name' => 'Pretérito Indefinido',
            'description' => 'Spanish past tense practice.',
            'difficulty_level' => 'intermediate'
        ],
        'spanish-travel-vocabulary' => [
            'language_code' => 'es',
            'topic_name' => 'Viajes y direcciones',
            'description' => 'Spanish travel vocabulary and directions.',
            'difficulty_level' => 'intermediate'
        ],
        'spanish-restaurant-dialogues' => [
            'language_code' => 'es',
            'topic_name' => 'En el restaurante',
            'description' => 'Spanish restaurant dialogues and comprehension.',
            'difficulty_level' => 'intermediate'
        ],
        'spanish-opinion-connectors' => [
            'language_code' => 'es',
            'topic_name' => 'Expresar opiniones',
            'description' => 'Spanish opinion phrases and connectors.',
            'difficulty_level' => 'intermediate'
        ],
        'spanish-subjunctive-basics' => [
            'language_code' => 'es',
            'topic_name' => 'Subjuntivo básico',
            'description' => 'Spanish introductory subjunctive practice.',
            'difficulty_level' => 'advanced'
        ],
        'spanish-idiomatic-expressions-advanced' => [
            'language_code' => 'es',
            'topic_name' => 'Expresiones idiomáticas',
            'description' => 'Advanced Spanish idiomatic expressions.',
            'difficulty_level' => 'advanced'
        ],
        'spanish-formal-email' => [
            'language_code' => 'es',
            'topic_name' => 'Correo formal',
            'description' => 'Formal email and register in Spanish.',
            'difficulty_level' => 'advanced'
        ],
        'spanish-news-and-debate' => [
            'language_code' => 'es',
            'topic_name' => 'Noticias y debate',
            'description' => 'Advanced reading and argumentation in Spanish.',
            'difficulty_level' => 'advanced'
        ],
        'latvian-level-test' => [
            'language_code' => 'lv',
            'topic_name' => 'Latviešu valodas līmeņa tests',
            'description' => 'Overall Latvian proficiency level test.',
            'difficulty_level' => 'advanced'
        ]
    ];

    if ($topicKey !== '' && isset($topicMap[$topicKey])) {
        return $topicMap[$topicKey];
    }

    if ($exerciseSlug !== '' && isset($topicMap[$exerciseSlug])) {
        return $topicMap[$exerciseSlug];
    }

    return null;
}

function resolveTopicId($pdo, $data, $fallbackTopicId) {
    $config = getTopicConfigByKeyOrSlug($data);
    if (!$config) {
        return (int) $fallbackTopicId;
    }

    $lookupStmt = $pdo->prepare("
        SELECT t.topic_id
        FROM topics t
        JOIN languages l ON t.language_id = l.language_id
        WHERE l.language_code = ? AND t.topic_name = ?
        LIMIT 1
    ");
    $lookupStmt->execute([$config['language_code'], $config['topic_name']]);
    $existing = $lookupStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        return (int) $existing['topic_id'];
    }

    $languageStmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = ? LIMIT 1");
    $languageStmt->execute([$config['language_code']]);
    $language = $languageStmt->fetch(PDO::FETCH_ASSOC);

    if (!$language) {
        throw new Exception('Language not found for topic mapping');
    }

    $insertStmt = $pdo->prepare("
        INSERT INTO topics (language_id, topic_name, description, difficulty_level)
        VALUES (?, ?, ?, ?)
    ");
    $insertStmt->execute([
        (int) $language['language_id'],
        $config['topic_name'],
        $config['description'],
        $config['difficulty_level']
    ]);

    return (int) $pdo->lastInsertId();
}

function updateUserProgress($pdo, $userId, $topicId) {
    $exerciseStatsStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS attempts,
            COALESCE(MAX(score * 100.0 / NULLIF(max_score, 0)), 0) AS best_score,
            COALESCE(AVG(score * 100.0 / NULLIF(max_score, 0)), 0) AS avg_score
        FROM exercise_results
        WHERE user_id = ? AND topic_id = ?
    ");
    $exerciseStatsStmt->execute([$userId, $topicId]);
    $exerciseStats = $exerciseStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $testStatsStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS attempts,
            COALESCE(MAX(score * 100.0 / NULLIF(max_score, 0)), 0) AS best_score,
            COALESCE(AVG(score * 100.0 / NULLIF(max_score, 0)), 0) AS avg_score
        FROM test_results
        WHERE user_id = ? AND topic_id = ?
    ");
    $testStatsStmt->execute([$userId, $topicId]);
    $testStats = $testStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $completedExercises = (int) ($exerciseStats['attempts'] ?? 0);
    $exerciseAttempts = (int) ($exerciseStats['attempts'] ?? 0);
    $bestExerciseScore = round((float) ($exerciseStats['best_score'] ?? 0), 2);
    $avgExerciseScore = round((float) ($exerciseStats['avg_score'] ?? 0), 2);

    $completedTests = (int) ($testStats['attempts'] ?? 0);
    $testAttempts = (int) ($testStats['attempts'] ?? 0);
    $bestTestScore = round((float) ($testStats['best_score'] ?? 0), 2);
    $avgTestScore = round((float) ($testStats['avg_score'] ?? 0), 2);

    $stmt = $pdo->prepare("
        INSERT INTO user_progress (
            user_id,
            topic_id,
            completed_exercises,
            exercise_attempts,
            best_exercise_score,
            avg_exercise_score,
            completed_tests,
            test_attempts,
            best_test_score,
            avg_test_score,
            last_activity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE
            completed_exercises = VALUES(completed_exercises),
            exercise_attempts = VALUES(exercise_attempts),
            best_exercise_score = VALUES(best_exercise_score),
            avg_exercise_score = VALUES(avg_exercise_score),
            completed_tests = VALUES(completed_tests),
            test_attempts = VALUES(test_attempts),
            best_test_score = VALUES(best_test_score),
            avg_test_score = VALUES(avg_test_score),
            last_activity = CURRENT_TIMESTAMP
    ");

    $stmt->execute([
        $userId,
        $topicId,
        $completedExercises,
        $exerciseAttempts,
        $bestExerciseScore,
        $avgExerciseScore,
        $completedTests,
        $testAttempts,
        $bestTestScore,
        $avgTestScore
    ]);
}

// Iegūstam sesijas tokenu no galvenes
$sessionToken = $_SERVER['HTTP_X_SESSION_TOKEN'] ?? null;

if (!$sessionToken) {
    respond(['success' => false, 'error' => 'No session token provided'], 401);
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT s.user_id, u.is_active
        FROM sessions s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.session_token = ?
          AND s.expires_at > NOW()
          AND u.is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$sessionToken]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        respond(['success' => false, 'error' => 'User not authenticated'], 401);
    }

    $userId = (int) $session['user_id'];
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!is_array($data)) {
        respond(['success' => false, 'error' => 'Invalid JSON data'], 400);
    }

    requireFields($data, ['score', 'max_score', 'time_spent']);

    $resultType = isset($data['result_type']) ? (string) $data['result_type'] : 'test';
    $topicId = resolveTopicId($pdo, $data, isset($data['topic_id']) ? (int) $data['topic_id'] : 0);
    $score = (int) $data['score'];
    $maxScore = (int) $data['max_score'];
    $timeSpent = (int) $data['time_spent'];

    $topicStmt = $pdo->prepare("SELECT topic_id FROM topics WHERE topic_id = ?");
    $topicStmt->execute([$topicId]);
    if (!$topicStmt->fetch()) {
        throw new Exception('Topic not found');
    }

    $pdo->beginTransaction();

    if ($resultType === 'exercise') {
        $exerciseSlug = isset($data['exercise_slug']) ? trim((string) $data['exercise_slug']) : '';
        $exerciseType = isset($data['exercise_type']) ? trim((string) $data['exercise_type']) : 'practice';
        $contentText = isset($data['content_text']) ? (string) $data['content_text'] : null;
        $answers = normalizeAnswerList($data['answers'] ?? []);

        if ($exerciseSlug === '') {
            throw new Exception('Missing required field: exercise_slug');
        }

        $insertExerciseStmt = $pdo->prepare("
            INSERT INTO exercise_results (
                user_id,
                topic_id,
                exercise_slug,
                exercise_type,
                score,
                max_score,
                time_spent,
                content_text
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insertExerciseStmt->execute([
            $userId,
            $topicId,
            $exerciseSlug,
            $exerciseType,
            $score,
            $maxScore,
            $timeSpent,
            $contentText
        ]);

        $exerciseResultId = (int) $pdo->lastInsertId();

        if ($answers) {
            $insertAnswerStmt = $pdo->prepare("
                INSERT INTO exercise_answers (
                    exercise_result_id,
                    question_id,
                    question_text,
                    user_answer,
                    correct_answer,
                    is_correct
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($answers as $answer) {
                $insertAnswerStmt->execute([
                    $exerciseResultId,
                    $answer['question_id'],
                    $answer['question_text'],
                    $answer['user_answer'],
                    $answer['correct_answer'],
                    $answer['is_correct']
                ]);
            }
        }

        updateUserProgress($pdo, $userId, $topicId);
        $pdo->commit();

        respond([
            'success' => true,
            'message' => 'Exercise result saved successfully',
            'result_type' => 'exercise',
            'result_id' => $exerciseResultId
        ]);
    }

    $errors = normalizeAnswerList($data['errors'] ?? []);

    $existingResultStmt = $pdo->prepare("
        SELECT result_id
        FROM test_results
        WHERE user_id = ? AND topic_id = ?
    ");
    $existingResultStmt->execute([$userId, $topicId]);
    $existingResult = $existingResultStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingResult) {
        $resultId = (int) $existingResult['result_id'];
        $updateStmt = $pdo->prepare("
            UPDATE test_results
            SET score = ?, max_score = ?, time_spent = ?, completion_date = CURRENT_TIMESTAMP
            WHERE result_id = ?
        ");
        $updateStmt->execute([$score, $maxScore, $timeSpent, $resultId]);

        $deleteErrorsStmt = $pdo->prepare("DELETE FROM test_errors WHERE result_id = ?");
        $deleteErrorsStmt->execute([$resultId]);
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO test_results (user_id, topic_id, score, max_score, time_spent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([$userId, $topicId, $score, $maxScore, $timeSpent]);
        $resultId = (int) $pdo->lastInsertId();
    }

    if ($errors) {
        $insertErrorStmt = $pdo->prepare("
            INSERT INTO test_errors (result_id, question_id, user_answer, correct_answer, question_text)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($errors as $error) {
            $insertErrorStmt->execute([
                $resultId,
                $error['question_id'],
                $error['user_answer'],
                $error['correct_answer'],
                $error['question_text']
            ]);
        }
    }

    updateUserProgress($pdo, $userId, $topicId);
    $pdo->commit();

    respond([
        'success' => true,
        'message' => 'Test result saved successfully',
        'result_type' => 'test',
        'result_id' => $resultId
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error saving result: ' . $e->getMessage());
    respond([
        'success' => false,
        'error' => 'Failed to save result: ' . $e->getMessage()
    ], 500);
}
?>
