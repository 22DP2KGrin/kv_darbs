<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/database.php';

echo "<h1>French Database Repair</h1>";

$topicUpdates = [
    11 => ['name' => 'Vocabulaire de base', 'description' => 'French basic vocabulary exercise.', 'difficulty' => 'beginner'],
    12 => ['name' => 'Présent de l’indicatif', 'description' => 'French present tense practice.', 'difficulty' => 'beginner'],
    13 => ['name' => 'Ma routine quotidienne', 'description' => 'French daily routine practice.', 'difficulty' => 'beginner'],
    14 => ['name' => 'Présente-toi', 'description' => 'French self introduction exercise.', 'difficulty' => 'beginner'],
    33 => ['name' => 'Passé composé', 'description' => 'French past tense practice.', 'difficulty' => 'intermediate'],
    34 => ['name' => 'Voyages et directions', 'description' => 'French travel vocabulary and directions.', 'difficulty' => 'intermediate'],
    35 => ['name' => 'Au restaurant', 'description' => 'French restaurant dialogues and comprehension.', 'difficulty' => 'intermediate'],
    36 => ['name' => 'Exprimer son opinion', 'description' => 'French opinion phrases and connectors.', 'difficulty' => 'intermediate'],
    37 => ['name' => 'Subjonctif de base', 'description' => 'French introductory subjunctive practice.', 'difficulty' => 'advanced'],
    38 => ['name' => 'Expressions idiomatiques', 'description' => 'Advanced French idiomatic expressions.', 'difficulty' => 'advanced'],
    39 => ['name' => 'Email formel', 'description' => 'Formal email and register in French.', 'difficulty' => 'advanced'],
    40 => ['name' => 'Actualités et débat', 'description' => 'Advanced reading and argumentation in French.', 'difficulty' => 'advanced'],
    32 => ['name' => 'Test de niveau de français', 'description' => 'Overall French proficiency level test.', 'difficulty' => 'advanced']
];

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

try {
    $pdo = getDBConnection();
    echo "<p style='color:green;'>✓ Database connection successful</p>";

    $pdo->beginTransaction();

    $findFrenchLanguageStmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = 'fr' LIMIT 1");
    $findFrenchLanguageStmt->execute();
    $frenchLanguage = $findFrenchLanguageStmt->fetch(PDO::FETCH_ASSOC);

    if (!$frenchLanguage) {
        throw new Exception("French language record not found in languages table.");
    }

    $frenchLanguageId = (int) $frenchLanguage['language_id'];

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
                $frenchLanguageId,
                $topicId
            ]);
            echo "<li>Updated topic_id {$topicId}: " . htmlspecialchars($config['name']) . "</li>";
        } else {
            $insertTopicStmt->execute([
                $topicId,
                $frenchLanguageId,
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
        $findTopicByNameStmt->execute([$frenchLanguageId, $topicName]);
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

    echo "<h2>Current French Topics</h2>";
    $topicsStmt = $pdo->query("
        SELECT topic_id, topic_name, difficulty_level
        FROM topics
        WHERE language_id = {$frenchLanguageId}
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

    echo "<p style='margin-top:20px; color:green; font-weight:bold;'>French database repair completed successfully.</p>";
    echo "<p>Now refresh TablePlus manually.</p>";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color:red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
