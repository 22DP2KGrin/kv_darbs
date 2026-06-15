<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';

function sendAvatarResponse($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

function getRequestToken() {
    $headers = getallheaders();

    if (isset($headers['Authorization']) && preg_match('/Bearer\s+(\S+)/', $headers['Authorization'], $matches)) {
        return $matches[1];
    }

    if (isset($headers['X-Session-Token'])) {
        return $headers['X-Session-Token'];
    }

    if (isset($_POST['session_token'])) {
        return $_POST['session_token'];
    }

    return null;
}

function ensureAvatarColumn($pdo) {
    $column = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'")->fetch();
    if (!$column) {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER timezone");
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendAvatarResponse(false, 'Nederīga pieprasījuma metode');
}

$token = getRequestToken();
if (!$token) {
    sendAvatarResponse(false, 'Nav autorizācijas tokena');
}

try {
    $pdo = getDBConnection();
    ensureAvatarColumn($pdo);

    $stmt = $pdo->prepare("SELECT u.user_id, u.avatar FROM sessions s JOIN users u ON s.user_id = u.user_id WHERE s.session_token = ? AND s.expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendAvatarResponse(false, 'Sesija nav derīga vai ir beigusies');
    }

    $avatar = null;
    $preset = $_POST['preset_avatar'] ?? '';
    $allowedPresets = ['preset-1', 'preset-2', 'preset-3', 'preset-4', 'preset-5', 'preset-6'];

    if ($preset !== '') {
        if (!in_array($preset, $allowedPresets, true)) {
            sendAvatarResponse(false, 'Nederīgs avatārs');
        }
        $avatar = $preset;
    } elseif (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['avatar_file']['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'Fails ir pārāk liels servera iestatījumiem',
                UPLOAD_ERR_FORM_SIZE => 'Fails ir pārāk liels',
                UPLOAD_ERR_PARTIAL => 'Fails tika augšupielādēts tikai daļēji',
                UPLOAD_ERR_NO_TMP_DIR => 'Serverī nav pagaidu mapes augšupielādēm',
                UPLOAD_ERR_CANT_WRITE => 'Serveris nevar saglabāt failu',
                UPLOAD_ERR_EXTENSION => 'Serveris apturēja faila augšupielādi'
            ];
            sendAvatarResponse(false, $uploadErrors[$_FILES['avatar_file']['error']] ?? 'Neizdevās augšupielādēt failu');
        }

        if ($_FILES['avatar_file']['size'] > 2 * 1024 * 1024) {
            sendAvatarResponse(false, 'Attēlam jābūt mazākam par 2 MB');
        }

        $imageInfo = getimagesize($_FILES['avatar_file']['tmp_name']);
        if ($imageInfo === false) {
            sendAvatarResponse(false, 'Izvēlētais fails nav attēls');
        }

        $mimeType = $imageInfo['mime'] ?? '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMimeType = $finfo->file($_FILES['avatar_file']['tmp_name']);
            if ($detectedMimeType) {
                $mimeType = $detectedMimeType;
            }
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($extensions[$mimeType])) {
            sendAvatarResponse(false, 'Atļauti tikai JPG, PNG un WebP attēli');
        }

        $uploadDir = __DIR__ . '/uploads/avatars';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            sendAvatarResponse(false, 'Neizdevās izveidot attēlu mapi');
        }

        $fileName = 'avatar_' . (int) $user['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extensions[$mimeType];
        $targetPath = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($_FILES['avatar_file']['tmp_name'], $targetPath)) {
            sendAvatarResponse(false, 'Neizdevās saglabāt augšupielādēto attēlu');
        }

        $avatar = 'uploads/avatars/' . $fileName;
    } else {
        sendAvatarResponse(false, 'Izvēlies avatāru vai augšupielādē attēlu');
    }

    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
    $stmt->execute([$avatar, $user['user_id']]);

    sendAvatarResponse(true, 'Avatārs veiksmīgi atjaunināts', [
        'avatar' => $avatar
    ]);
} catch (Throwable $e) {
    error_log('Avatar update error: ' . $e->getMessage());
    sendAvatarResponse(false, 'Neizdevās atjaunināt avatāru');
}
