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
        return "Es varu palīdzēt testa laikā bez pilnas atbildes došanas.\n"
            . "Es varu paskaidrot jautājumu, dot mājienu vai atgādināt noteikumu, kas te noder. Uzraksti, ko tieši nesaproti.";
    }

    if ($pageType === 'exercise') {
        return "Es varu palīdzēt ar šo vingrinājumu.\n"
            . "Varu paskaidrot tēmu, dot mājienu vai palīdzēt saprast, kā domāt par atbildi, neatklājot visu uzreiz.";
    }

    return "Šobrīd es darbojos bezmaksas lokālajā režīmā.\n"
        . "Es varu palīdzēt ar angļu gramatiku, vārdu krājumu, testiem un vingrinājumiem.\n"
        . "Pamēģini uzrakstīt: \"Paskaidro Present Simple\" vai \"Dod mājienu šim jautājumam\".";
}

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
        aiLog('Serving local AI reply');
        echo json_encode([
            'success' => true,
            'reply' => buildLocalReply($action, $messages, $pageContext),
            'model' => 'local-assistant'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    if (!function_exists('curl_init')) {
        aiLog('Missing cURL extension');
        throw new Exception('PHP cURL paplašinājums nav pieejams uz servera.');
    }

    $instructions = "Tu esi draudzīgs AI valodu pasniedzējs latviešu valodā.\n"
        . "Palīdzi studentam saprast angļu valodu, vingrinājumus un testu jautājumus.\n"
        . "Runā vienkārši, skaidri, atbalstoši un īsi.\n"
        . "Ja students atrodas testā vai vingrinājumā, dod priekšroku mājieniem, skaidrojumam un domāšanas virzienam, nevis uzreiz gala atbildei.\n"
        . "Ja lietotājs tieši prasa pilnu atbildi, vispirms īsi paskaidro noteikumu vai loģiku un tikai tad, ja nepieciešams, dod minimālu tiešu palīdzību.\n"
        . "Ja iespējams, izmanto lapas kontekstu, lai atbilde būtu saistīta ar redzamo jautājumu.\n"
        . "Ja lietotājs ir apjucis, piedāvā nākamo soli.\n"
        . "Atbildi latviešu valodā, bet angļu piemērus atstāj angliski.\n"
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
        throw new Exception('Neizdevās sazināties ar OpenAI: ' . $curlError);
    }

    $decoded = json_decode($rawResponse, true);

    if ($statusCode >= 400) {
        $errorMessage = $decoded['error']['message'] ?? ('OpenAI request failed with status ' . $statusCode);
        aiLog('OpenAI HTTP error ' . $statusCode . ': ' . $errorMessage);
        if (str_contains(mb_strtolower($errorMessage, 'UTF-8'), 'quota')) {
            aiLog('Falling back to local AI reply because of quota error');
            echo json_encode([
                'success' => true,
                'reply' => buildLocalReply($action, $messages, $pageContext),
                'model' => 'local-assistant'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }
        throw new Exception($errorMessage);
    }

    if (!is_array($decoded)) {
        aiLog('Invalid JSON from OpenAI: ' . substr($rawResponse, 0, 400));
        throw new Exception('Nederīga atbilde no OpenAI');
    }

    $reply = extractTextFromResponse($decoded);

    if ($reply === '') {
        aiLog('Empty text reply from OpenAI');
        throw new Exception('OpenAI neatgrieza teksta atbildi');
    }

    aiLog('Chat reply generated successfully with model ' . $model);

    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'model' => $model
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    aiLog('Chat error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
