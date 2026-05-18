<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/database.php';

echo "<h1>Spanish Database Repair</h1>";

$topicUpdates = [
    15 => ['name' => 'Vocabulario básico', 'description' => 'Spanish basic vocabulary exercise.', 'difficulty' => 'beginner'],
    16 => ['name' => 'Presente de indicativo', 'description' => 'Spanish present tense practice.', 'difficulty' => 'beginner'],
    17 => ['name' => 'Mi rutina diaria', 'description' => 'Spanish daily routine practice.', 'difficulty' => 'beginner'],
    18 => ['name' => 'Preséntate', 'description' => 'Spanish self introduction exercise.', 'difficulty' => 'beginner'],
    23 => ['name' => 'Pretérito Indefinido', 'description' => 'Spanish past tense practice.', 'difficulty' => 'intermediate'],
    24 => ['name' => 'Viajes y direcciones', 'description' => 'Spanish travel vocabulary and directions.', 'difficulty' => 'intermediate'],
    25 => ['name' => 'En el restaurante', 'description' => 'Spanish restaurant dialogues and comprehension.', 'difficulty' => 'intermediate'],
    26 => ['name' => 'Expresar opiniones', 'description' => 'Spanish opinion phrases and connectors.', 'difficulty' => 'intermediate'],
    27 => ['name' => 'Subjuntivo básico', 'description' => 'Spanish introductory subjunctive practice.', 'difficulty' => 'advanced'],
    28 => ['name' => 'Expresiones idiomáticas', 'description' => 'Advanced Spanish idiomatic expressions.', 'difficulty' => 'advanced'],
    29 => ['name' => 'Correo formal', 'description' => 'Formal email and register in Spanish.', 'difficulty' => 'advanced'],
    30 => ['name' => 'Noticias y debate', 'description' => 'Advanced reading and argumentation in Spanish.', 'difficulty' => 'advanced'],
    31 => ['name' => 'Prueba de nivel de español', 'description' => 'Overall Spanish proficiency level test.', 'difficulty' => 'advanced']
];

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

try {
    $pdo = getDBConnection();
    echo "<p style='color:green;'>✓ Database connection successful</p>";

    $pdo->beginTransaction();

    $findSpanishLanguageStmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = 'es' LIMIT 1");
    $findSpanishLanguageStmt->execute();
    $spanishLanguage = $findSpanishLanguageStmt->fetch(PDO::FETCH_ASSOC);

    if (!$spanishLanguage) {
        throw new Exception("Spanish language record not found in languages table.");
    }

    $spanishLanguageId = (int) $spanishLanguage['language_id'];

    $updateTopicStmt = $pdo->prepare("
        UPDATE topics
        SET topic_name = ?, description = ?, difficulty_level = ?, language_id = ?
        WHERE topic_id = ?
    ");

    $insertTopicStmt = $pdo->prepare("
        INSERT INTO topics (topic_id, language_id, topic_name, description, difficulty_level)
        VALUES (?, ?, ?, ?, ?)
    ");

    $checkTopicStmt = $pdo->prepare("SELECT topic_id FROM topics WHERE topic_id = ? LIMIT 1");

    echo "<h2>Topic Updates</h2><ul>";
    foreach ($topicUpdates as $topicId => $config) {
        $checkTopicStmt->execute([$topicId]);
        if ($checkTopicStmt->fetch()) {
            $updateTopicStmt->execute([
                $config['name'],
                $config['description'],
                $config['difficulty'],
                $spanishLanguageId,
                $topicId
            ]);
            echo "<li>Updated topic_id {$topicId}: " . htmlspecialchars($config['name']) . "</li>";
        } else {
            $insertTopicStmt->execute([
                $topicId,
                $spanishLanguageId,
                $config['name'],
                $config['description'],
                $config['difficulty']
            ]);
            echo "<li>Inserted topic_id {$topicId}: " . htmlspecialchars($config['name']) . "</li>";
        }
    }
    echo "</ul>";

    $findTopicByNameStmt = $pdo->prepare("
        SELECT topic_id
        FROM topics
        WHERE language_id = ? AND topic_name = ?
        LIMIT 1
    ");

    $updateExerciseResultsStmt = $pdo->prepare("
        UPDATE exercise_results
        SET topic_id = ?
        WHERE exercise_slug = ?
    ");

    echo "<h2>Exercise Result Repairs</h2><ul>";
    foreach ($slugMap as $slug => $topicName) {
        $findTopicByNameStmt->execute([$spanishLanguageId, $topicName]);
        $topic = $findTopicByNameStmt->fetch(PDO::FETCH_ASSOC);
        if (!$topic) {
            echo "<li style='color:red;'>Missing topic for slug " . htmlspecialchars($slug) . "</li>";
            continue;
        }

        $resolvedTopicId = (int) $topic['topic_id'];
        $updateExerciseResultsStmt->execute([$resolvedTopicId, $slug]);
        echo "<li>Updated exercise_results for slug " . htmlspecialchars($slug) . " -> topic_id {$resolvedTopicId}</li>";
    }
    echo "</ul>";

    $pdo->commit();

    echo "<h2>Current Spanish Topics</h2>";
    $topicsStmt = $pdo->query("
        SELECT topic_id, topic_name, difficulty_level
        FROM topics
        WHERE language_id = {$spanishLanguageId}
        ORDER BY topic_id
    ");
    $topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>";
    echo "<tr><th>topic_id</th><th>topic_name</th><th>difficulty_level</th></tr>";
    foreach ($topics as $topic) {
        echo "<tr>";
        echo "<td>" . (int) $topic['topic_id'] . "</td>";
        echo "<td>" . htmlspecialchars($topic['topic_name']) . "</td>";
        echo "<td>" . htmlspecialchars($topic['difficulty_level']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<p style='margin-top:20px; color:green; font-weight:bold;'>Spanish database repair completed successfully.</p>";
    echo "<p>Now refresh TablePlus manually.</p>";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color:red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
