<?php
header('Content-Type: application/json; charset=utf-8');

$localOpenAiConfig = __DIR__ . '/../openai_config.php';
if (file_exists($localOpenAiConfig)) {
    require_once $localOpenAiConfig;
}

function aiLog($message) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    error_log($line, 3, $logDir . '/ai_chat.log');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

function readJsonInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new Exception('Invalid JSON payload');
    }

    return $data;
}

function normalizeMessages($messages) {
    $normalized = [];

    if (!is_array($messages)) {
        return $normalized;
    }

    foreach ($messages as $message) {
        if (!is_array($message)) {
            continue;
        }

        $role = isset($message['role']) ? (string) $message['role'] : 'user';
        $content = isset($message['content']) ? trim((string) $message['content']) : '';

        if ($content === '') {
            continue;
        }

        if (!in_array($role, ['user', 'assistant', 'system', 'developer'], true)) {
            $role = 'user';
        }

        $normalized[] = [
            'role' => $role,
            'content' => $content
        ];
    }

    return $normalized;
}

function stringifyContext($context) {
    if (!is_array($context)) {
        return 'Nav pieejams.';
    }

    $parts = [];

    $map = [
        'pageType' => 'Lapas tips',
        'pageTitle' => 'Lapas nosaukums',
        'sectionTitle' => 'Sadaļa',
        'currentQuestion' => 'Pašreizējais jautājums',
        'visibleOptions' => 'Redzamās atbildes',
        'activeTab' => 'Aktīvā cilne',
        'url' => 'URL'
    ];

    foreach ($map as $key => $label) {
        if (!isset($context[$key]) || $context[$key] === '' || $context[$key] === []) {
            continue;
        }

        $value = $context[$key];
        if (is_array($value)) {
            $value = implode(' | ', array_map('strval', $value));
        }

        $parts[] = $label . ': ' . $value;
    }

    return $parts ? implode("\n", $parts) : 'Nav pieejams.';
}

function extractTextFromResponse($response) {
    if (isset($response['output_text']) && is_string($response['output_text']) && trim($response['output_text']) !== '') {
        return trim($response['output_text']);
    }

    if (!isset($response['output']) || !is_array($response['output'])) {
        return '';
    }

    $chunks = [];

    foreach ($response['output'] as $item) {
        if (!is_array($item) || !isset($item['content']) || !is_array($item['content'])) {
            continue;
        }

        foreach ($item['content'] as $contentItem) {
            if (!is_array($contentItem)) {
                continue;
            }

            if (isset($contentItem['text']) && is_string($contentItem['text'])) {
                $chunks[] = $contentItem['text'];
            }
        }
    }

    return trim(implode("\n", $chunks));
}

function sendLocalReply($action, $messages, $pageContext, $reason = '') {
    if ($reason !== '') {
        aiLog('Serving local AI reply. Reason: ' . $reason);
    } else {
        aiLog('Serving local AI reply');
    }

    echo json_encode([
        'success' => true,
        'reply' => buildLocalReply($action, $messages, $pageContext),
        'model' => 'local-site-assistant'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function normalizeLocalText($text) {
    $text = mb_strtolower((string) $text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function localQuestionMatchScore($message, $question) {
    if ($message === '' || $question === '') {
        return 0;
    }

    if (str_contains($message, $question) || str_contains($question, $message)) {
        return 100;
    }

    $questionWords = array_values(array_filter(explode(' ', $question), function($word) {
        return mb_strlen($word, 'UTF-8') > 2;
    }));

    if (!$questionWords) {
        return 0;
    }

    $score = 0;
    foreach ($questionWords as $word) {
        if (str_contains($message, $word)) {
            $score++;
        }
    }

    return $score;
}

function buildLocalReply($action, $messages, $pageContext) {
    $lastMessage = '';
    if ($messages) {
        $last = end($messages);
        $lastMessage = mb_strtolower(trim((string) ($last['content'] ?? '')), 'UTF-8');
    }

    $pageType = mb_strtolower((string) ($pageContext['pageType'] ?? ''), 'UTF-8');
    $sectionTitle = (string) ($pageContext['sectionTitle'] ?? $pageContext['pageTitle'] ?? '');
    $question = trim((string) ($pageContext['currentQuestion'] ?? ''));
    $options = is_array($pageContext['visibleOptions'] ?? null) ? $pageContext['visibleOptions'] : [];
    $currentCorrectAnswer = is_array($pageContext['currentCorrectAnswer'] ?? null) ? $pageContext['currentCorrectAnswer'] : null;
    $testAnswers = is_array($pageContext['testAnswers'] ?? null) ? $pageContext['testAnswers'] : [];

    $asksForAnswer =
        str_contains($lastMessage, 'atbild') ||
        str_contains($lastMessage, 'pareiz') ||
        str_contains($lastMessage, 'correct') ||
        str_contains($lastMessage, 'answer');

    if ($testAnswers) {
        $normalizedMessage = normalizeLocalText($lastMessage);
        $bestMatch = null;
        $bestScore = 0;

        foreach ($testAnswers as $answerItem) {
            if (!is_array($answerItem)) {
                continue;
            }

            $answerQuestion = trim((string) ($answerItem['question'] ?? ''));
            $answerText = trim((string) ($answerItem['answer'] ?? ''));

            if ($answerQuestion === '' || $answerText === '') {
                continue;
            }

            $normalizedQuestion = normalizeLocalText($answerQuestion);
            $score = localQuestionMatchScore($normalizedMessage, $normalizedQuestion);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = [
                    'question' => $answerQuestion,
                    'answer' => $answerText
                ];
            }
        }

        if ($bestMatch && ($asksForAnswer || $bestScore >= 3)) {
            return "Pareizā atbilde: " . $bestMatch['answer'] . "\nJautājums: " . $bestMatch['question'];
        }
    }

    if ($asksForAnswer && $currentCorrectAnswer && trim((string) ($currentCorrectAnswer['answer'] ?? '')) !== '') {
        $answerText = trim((string) $currentCorrectAnswer['answer']);
        $answerQuestion = trim((string) ($currentCorrectAnswer['question'] ?? $question));

        return "Pareizā atbilde: " . $answerText . ($answerQuestion !== '' ? "\nJautājums: " . $answerQuestion : '');
    }

    if (
        str_contains($lastMessage, 'kur') ||
        str_contains($lastMessage, 'where')
    ) {
        if (
            str_contains($lastMessage, 'english') ||
            str_contains($lastMessage, 'angļu')
        ) {
            return "Angļu vingrinājumi un administratora izveidotie angļu testi ir lapā `exercises_english.html`, sadaļā `Pieejamie vingrinājumi`.";
        }

        if (
            str_contains($lastMessage, 'vēstur') ||
            str_contains($lastMessage, 'history')
        ) {
            return "Testu vēsture ir lapā `test_history.html`. Tur parādās rezultāti pēc testa pabeigšanas.";
        }
    }

    if (
        str_contains($lastMessage, 'admin') ||
        str_contains($lastMessage, 'izveidot')
    ) {
        return "Administratora izveidotie testi tiek piesaistīti valodai un tēmai. Angļu tests parādās `exercises_english.html` sadaļā `Pieejamie vingrinājumi`. Lai to varētu pildīt, testam jābūt jautājumiem un pareizajām atbildēm.";
    }

    if (
        str_contains($lastMessage, 'kļūd') ||
        str_contains($lastMessage, 'error')
    ) {
        return "Ja vietnē parādās kļūda, pārbaudi: lapa atvērta caur lokālu serveri, lietotājs ir pieslēdzies, PHP API atgriež JSON, un `logs/php_errors.log` nav jaunas kļūdas. AI kļūdām bieži iemesls ir OpenAI API atslēga, kvota vai tīkls.";
    }

    if (
        str_contains($lastMessage, 'vēstur') ||
        str_contains($lastMessage, 'history')
    ) {
        return "Testa vēsture ir lapā `test_history.html`. Tā rāda rezultātus, kas saglabāti pēc testa pabeigšanas.\nJa vēsture ir tukša, pārbaudi: lietotājs ir pieslēdzies, tests ir pabeigts un `api/save_test_result.php` saglabāja rezultātu.";
    }

    if (
        str_contains($lastMessage, 'login') ||
        str_contains($lastMessage, 'pieteik') ||
        str_contains($lastMessage, 'account')
    ) {
        return "Pieteikšanās notiek lapā `login.html`, un pieprasījums iet uz `api/login_process.php`. Pēc veiksmīgas pieteikšanās tiek saglabāts `userSessionToken`, un header rāda profila ikonu.";
    }

    if (
        str_contains($lastMessage, 'profile') ||
        str_contains($lastMessage, 'profils')
    ) {
        return "Profils ir lapā `profile.html`. Tur jāparādās pašreizējā lietotāja datiem. Ja profils neatveras, pārbaudi, vai lietotājs ir pieslēdzies un vai ir `userSessionToken`.";
    }

    if (
        str_contains($lastMessage, 'vietn') ||
        str_contains($lastMessage, 'platform')
    ) {
        return "Vietnē ir sākumlapa, valodu vingrinājumi, testi, profils un testa vēsture. Ja lietotājs ir pieslēdzies, header rāda profila ikonu; ja nav, rāda pieteikšanās un reģistrācijas pogas.";
    }

    if (
        str_contains($lastMessage, 'test') ||
        str_contains($lastMessage, 'uzdev') ||
        str_contains($lastMessage, 'vingrin')
    ) {
        return "Testi un vingrinājumi atveras pēc pieteikšanās kontā. Testa lapās var atbildēt uz jautājumiem, un pēc pabeigšanas rezultātam jāparādās testa vēsturē.";
    }

    if ($action === 'hint' || str_contains($lastMessage, 'mājien') || str_contains($lastMessage, 'hint')) {
        if ($question !== '') {
            return "Mājiens:\n"
                . "Vispirms nosaki, ko pārbauda šis jautājums: gramatiku, vārda nozīmi vai teikuma loģiku.\n"
                . "Pēc tam izslēdz variantus, kas acīmredzami neiederas teikumā vai neatbilst laikam un formai.";
        }

        return "Mājiens:\n"
            . "Sāc ar pašu pamatu: kas šeit tiek pārbaudīts - gramatika, vārdu krājums vai izpratne? Kad noteiksi tēmu, atbilde kļūs daudz skaidrāka.";
    }

    if ($action === 'explain_question' || str_contains($lastMessage, 'paskaidro šo jaut')) {
        if ($question !== '') {
            $reply = "Šis jautājums pārbauda, vai tu saproti uzdevuma loģiku.\n";
            $reply .= "Jautājums: " . $question . "\n";
            $reply .= "Ko darīt:\n";
            $reply .= "1. Izlasi jautājumu līdz galam.\n";
            $reply .= "2. Nosaki, kāda forma vai nozīme te ir vajadzīga.\n";
            $reply .= "3. Salīdzini variantus un atmet tos, kas neiederas.\n";

            if ($options) {
                $reply .= "Redzamie varianti: " . implode(' | ', array_map('strval', $options));
            }

            return $reply;
        }

        return "Es varu paskaidrot jautājumu soli pa solim: vispirms saprotam, ko tieši prasa uzdevums, tad atrodam svarīgo noteikumu un tikai pēc tam izvēlamies atbildi.";
    }

    if (str_contains($lastMessage, 'present simple') || str_contains(mb_strtolower($sectionTitle, 'UTF-8'), 'present simple')) {
        return "Present Simple parasti lieto ieradumiem, regulārām darbībām un faktiem.\n"
            . "Piemērs: \"She goes to school every day.\"\n"
            . "Svarīgi: ar he/she/it darbības vārdam bieži pievieno -s vai -es.";
    }

    if (
        str_contains($lastMessage, 'present perfect') ||
        str_contains($lastMessage, 'past simple') ||
        str_contains(mb_strtolower($sectionTitle, 'UTF-8'), 'present perfect')
    ) {
        return "Īsa atšķirība:\n"
            . "Past Simple runā par pabeigtu darbību noteiktā laikā pagātnē.\n"
            . "Present Perfect savieno pagātni ar tagadni un bieži nepasaka precīzu laiku.\n"
            . "Atslēgvārdi kā \"yesterday\" bieži ved uz Past Simple, bet \"already\", \"yet\", \"ever\" bieži palīdz atpazīt Present Perfect.";
    }

    if (
        str_contains($lastMessage, 'vocabulary') ||
        str_contains($lastMessage, 'vārdu') ||
        str_contains(mb_strtolower($sectionTitle, 'UTF-8'), 'vocabulary')
    ) {
        return "Vārdu krājumā mēģini iet šādi:\n"
            . "1. Skaties uz vārdu kontekstā.\n"
            . "2. Padomā, vai tas ir darbības vārds, lietvārds vai īpašības vārds.\n"
            . "3. Meklē, kurš variants loģiski vislabāk iederas teikumā.";
    }

    if ($pageType === 'test') {
        return "Tu pašlaik esi testa lapā. Es varu paskaidrot jautājumu, dot mājienu vai atgādināt noteikumu, kas te noder.";
    }

    if ($pageType === 'exercise') {
        return "Tu pašlaik esi vingrinājuma lapā. Es varu paskaidrot tēmu, dot mājienu vai palīdzēt saprast, kā izvēlēties atbildi.";
    }

    return "Pašlaik pilnais AI režīms nav pieejams, tāpēc es varu atbildēt tikai uz pamata jautājumiem par vietni, profilu, pieteikšanos, testiem un vingrinājumiem. Brīvām atbildēm vajag derīgu OpenAI API atslēgu ar aktīvu kvotu.";
}

$messages = [];
$pageContext = [];
$action = 'general';

try {
    $payload = readJsonInput();
    $messages = normalizeMessages($payload['messages'] ?? []);
    $pageContext = $payload['pageContext'] ?? [];
    $action = isset($payload['action']) ? trim((string) $payload['action']) : 'general';

    aiLog('Incoming chat request. Action=' . $action . ', messages=' . count($messages));

    if (!$messages) {
        throw new Exception('No messages provided');
    }

    $apiKey = getenv('OPENAI_API_KEY') ?: (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
    $model = getenv('OPENAI_MODEL') ?: (defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4o-mini');
    $contextText = stringifyContext($pageContext);
    $forceLocalMode = getenv('OPENAI_LOCAL_ONLY') === '1' || (defined('OPENAI_LOCAL_ONLY') && OPENAI_LOCAL_ONLY);

    if (!$apiKey || $forceLocalMode) {
        sendLocalReply($action, $messages, $pageContext, !$apiKey ? 'missing API key' : 'local mode enabled');
    }

    if (!function_exists('curl_init')) {
        aiLog('Missing cURL extension');
        sendLocalReply($action, $messages, $pageContext, 'missing PHP cURL extension');
    }

    $instructions = "Tu esi draudzīgs AI palīgs valodu apguves platformā Katrīnas projektā.\n"
        . "Platformas struktūra: `index.html` ir sākumlapa; valodu vingrinājumi ir `exercises_english.html`, `exercises_french.html`, `exercises_spanish.html`, `exercises_latvian.html`; valodu līmeņa testi ir `english/test.html`, `french/test.html`, `spanish/test.html`, `latvian/test.html`; administratora izveidotie testi tiek rādīti attiecīgās valodas vingrinājumu lapas sadaļā `Pieejamie vingrinājumi`; profils ir `profile.html`; vēsture ir `test_history.html`; pieteikšanās ir `login.html`; reģistrācija ir `signup.html`.\n"
        . "Tu vari palīdzēt lietotājam saprast vietni, atrast lapas, paskaidrot testu jautājumus, dot mācību plānu, skaidrot kļūdas un palīdzēt sagatavoties testiem.\n"
        . "Atbildi uz jebkuru lietotāja jautājumu: par mācībām, valodām, programmēšanu, ikdienas tēmām vai pašreizējo lapu.\n"
        . "Runā vienkārši, skaidri, atbalstoši un īsi, bet dod pietiekami pilnu atbildi, ja jautājums to prasa.\n"
        . "Atbildi tajā pašā valodā, kurā lietotājs jautā. Ja lietotājs jautā krieviski, atbildi krieviski. Ja latviski, atbildi latviski.\n"
        . "Ja students atrodas testā vai vingrinājumā, dod priekšroku mājieniem, skaidrojumam un domāšanas virzienam, nevis uzreiz gala atbildei.\n"
        . "Ja lietotājs tieši prasa pilnu atbildi, vispirms īsi paskaidro noteikumu vai loģiku un tikai tad, ja nepieciešams, dod minimālu tiešu palīdzību.\n"
        . "Ja iespējams, izmanto lapas kontekstu, lai atbilde būtu saistīta ar redzamo jautājumu.\n"
        . "Ja lietotājs ir apjucis, piedāvā nākamo soli.\n"
        . "Neizdomā faktus: ja neesi pārliecināts, pasaki to un piedāvā, kā pārbaudīt.\n"
        . "Darbības režīms: " . $action . "\n\n"
        . "Pašreizējās lapas konteksts:\n" . $contextText;

    $requestBody = [
        'model' => $model,
        'instructions' => $instructions,
        'input' => $messages,
        'store' => false,
        'text' => [
            'verbosity' => 'medium'
        ]
    ];

    $ch = curl_init('https://api.openai.com/v1/responses');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($requestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 60
    ]);

    $rawResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($rawResponse === false) {
        aiLog('cURL transport error: ' . $curlError);
        sendLocalReply($action, $messages, $pageContext, 'OpenAI transport error: ' . $curlError);
    }

    $decoded = json_decode($rawResponse, true);

    if ($statusCode >= 400) {
        $errorMessage = $decoded['error']['message'] ?? ('OpenAI request failed with status ' . $statusCode);
        aiLog('OpenAI HTTP error ' . $statusCode . ': ' . $errorMessage);
        sendLocalReply($action, $messages, $pageContext, 'OpenAI HTTP error ' . $statusCode);
    }

    if (!is_array($decoded)) {
        aiLog('Invalid JSON from OpenAI: ' . substr($rawResponse, 0, 400));
        sendLocalReply($action, $messages, $pageContext, 'invalid OpenAI JSON');
    }

    $reply = extractTextFromResponse($decoded);

    if ($reply === '') {
        aiLog('Empty text reply from OpenAI');
        sendLocalReply($action, $messages, $pageContext, 'empty OpenAI text reply');
    }

    aiLog('Chat reply generated successfully with model ' . $model);

    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'model' => $model
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    aiLog('Chat error: ' . $e->getMessage());

    if ($messages) {
        echo json_encode([
            'success' => true,
            'reply' => buildLocalReply($action, $messages, is_array($pageContext) ? $pageContext : []),
            'model' => 'local-site-assistant'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
