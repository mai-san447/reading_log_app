<?php

function isLocalRequest()
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    return $host === ''
        || strpos($host, 'localhost') === 0
        || strpos($host, '127.0.0.1') === 0;
}

function loadConfig()
{
    $configPath = __DIR__ . '/config.local.php';

    if (!file_exists($configPath)) {
        exit('ConfigError: config.local.php が見つかりません。config.example.php をコピーして設定してください。');
    }

    $config = require $configPath;

    if (isLocalRequest()) {
        return [
            'db_dsn' => 'mysql:dbname=gs_db_class;charset=utf8;host=127.0.0.1',
            'db_user' => 'root',
            'db_pass' => '',
        ];
    }

    $requiredKeys = ['db_dsn', 'db_user', 'db_pass'];

    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $config)) {
            exit('ConfigError: ' . $key . ' が未設定です。');
        }
    }

    if (trim((string)$config['db_dsn']) === '' || trim((string)$config['db_user']) === '') {
        exit('ConfigError: db_dsn または db_user が未設定です。');
    }

    return $config;
}

function connectDb()
{
    if (isLocalRequest()) {
        // ローカル開発中はエラーを画面に出して原因を追いやすくする
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    } else {
        // 公開先ではエラー内容を画面に出さない（情報漏えい対策）。記録だけ残す
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);
    }

    $config = loadConfig();

    try {
        $pdo = new PDO(
            $config['db_dsn'],
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        exit('DBConnectError:' . $e->getMessage());
    }

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS gs_reading_log (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(128) NOT NULL,
                author VARCHAR(128) NOT NULL,
                cover_image TEXT,
                theme VARCHAR(64),
                price INT NOT NULL DEFAULT 0,
                status VARCHAR(32) NOT NULL,
                memo TEXT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        // ★ログイン用：ユーザーを保管する表を自動で作る
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS gs_user_table (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                login_id VARCHAR(255) NOT NULL UNIQUE,
                login_pw VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        ensureColumnExists($pdo, 'gs_reading_log', 'cover_image', 'TEXT');
        ensureColumnExists($pdo, 'gs_reading_log', 'theme', 'VARCHAR(64)');
        ensureColumnExists($pdo, 'gs_reading_log', 'price', 'INT NOT NULL DEFAULT 0');
        ensureColumnExists($pdo, 'gs_reading_log', 'value_score', 'INT NOT NULL DEFAULT 3');
        ensureColumnExists($pdo, 'gs_reading_log', 'page_count', 'INT NOT NULL DEFAULT 0');
        ensureColumnExists($pdo, 'gs_reading_log', 'acquisition_method', 'VARCHAR(32)');
        ensureColumnExists($pdo, 'gs_reading_log', 'learning_axis', 'VARCHAR(16) NOT NULL DEFAULT "深める"');
        ensureColumnExists($pdo, 'gs_reading_log', 'learning_note', 'TEXT');
        ensureColumnExists($pdo, 'gs_reading_log', 'exit_action', 'VARCHAR(32) NOT NULL DEFAULT "未定"');
        ensureColumnExists($pdo, 'gs_reading_log', 'recovery_amount', 'INT NOT NULL DEFAULT 0');
        ensureColumnExists($pdo, 'gs_reading_log', 'return_due_date', 'DATE NULL');
    } catch (PDOException $e) {
        exit('DBCreateError:' . $e->getMessage());
    }

    return $pdo;
}

function ensureColumnExists($pdo, $tableName, $columnName, $columnDefinition)
{
    $sql = "SHOW COLUMNS FROM {$tableName} LIKE :column_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':column_name', $columnName, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC) !== false) {
        return;
    }

    $pdo->exec("ALTER TABLE {$tableName} ADD {$columnName} {$columnDefinition}");
}

// ★会員登録の合言葉（招待コード）を返す。空 '' なら「誰でも登録可」。
//   公開先の config.local.php で 'register_code' を設定すると、
//   その合言葉を知っている人だけが会員登録できる（無断登録を防ぐ）。
//   ※ローカル(localhost)では loadConfig() が合言葉なしの設定を返すので常に開放（開発しやすさ優先）。
function getRegisterCode()
{
    $config = loadConfig();
    return isset($config['register_code']) ? trim((string)$config['register_code']) : '';
}

// ★門番：ログインしているか確認する。していなければログインページへ追い返す
function loginCheck()
{
    // セッションがまだ始まっていなければ始める
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 合言葉（chk_ssid）が、いまのセッションIDと一致しなければ「入っちゃダメ」
    if (!isset($_SESSION['chk_ssid']) || $_SESSION['chk_ssid'] !== session_id()) {
        header('Location: login.php');
        exit();
    }
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function isValidHttpUrl($value)
{
    if ($value === '') {
        return true;
    }

    return filter_var($value, FILTER_VALIDATE_URL) !== false
        && preg_match('/^https?:\/\//', $value) === 1;
}

// ★PHP05の応用：アップロードされた表紙画像を安全に保存し、DB/imgタグで使う相対パス（img/xxx.jpg）を返す。
//   画像が選ばれていなければ '' を返す。おかしなファイルは例外を投げて呼び出し側で止める。
//   安全のため：本当に画像か検証／拡張子はホワイトリスト／ファイル名はランダム（ユーザー名は使わない）／サイズ上限。
function handleCoverUpload($fieldName, $destDir = null, $maxBytes = 5242880)
{
    // 未選択（UPLOAD_ERR_NO_FILE）なら何もしない
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('画像のアップロードに失敗しました（エラーコード: ' . (int)$file['error'] . '）。');
    }

    // サイズ上限（既定5MB）
    if ($file['size'] > $maxBytes) {
        throw new Exception('画像が大きすぎます（' . round($maxBytes / 1048576, 1) . 'MBまで）。');
    }

    // 本当に画像かを中身で判定し、安全な拡張子に対応づける（拡張子や名前は信用しない）
    $info = @getimagesize($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $mime = ($info !== false && isset($info['mime'])) ? $info['mime'] : '';
    if (!isset($allowed[$mime])) {
        throw new Exception('対応していない画像形式です（JPEG / PNG / GIF / WebP のみ）。');
    }
    $ext = $allowed[$mime];

    // 保存先フォルダ（無ければ作る）
    if ($destDir === null) {
        $destDir = __DIR__ . '/img';
    }
    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new Exception('保存先フォルダ（img）を作成できませんでした。');
    }

    // ランダムなファイル名にして上書き・パス改ざんを防ぐ
    $fileName = uniqid('cover_', true) . '.' . $ext;
    $destPath = $destDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new Exception('画像の保存に失敗しました。imgフォルダの書き込み権限を確認してください。');
    }

    // ページから <img src> やDBに入れる相対パスを返す
    return 'img/' . $fileName;
}

function normalizePrice($value)
{
    $price = (int)$value;
    return max(0, $price);
}

function normalizeRecoveryAmount($value)
{
    $amount = (int)$value;
    return max(0, $amount);
}

function normalizeValueScore($value)
{
    $score = (int)$value;
    return min(5, max(1, $score));
}

function normalizePageCount($value)
{
    $pageCount = (int)$value;
    return max(0, $pageCount);
}

function normalizeReturnDueDate($value)
{
    $date = trim((string)$value);

    if ($date === '') {
        return '';
    }

    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        return '';
    }

    return $date;
}

function calculateNetInvestment($price, $recoveryAmount = 0)
{
    $amount = normalizePrice($price) - normalizeRecoveryAmount($recoveryAmount);
    return max(0, $amount);
}

function calculateRoiScore($price, $valueScore, $recoveryAmount = 0)
{
    $score = normalizeValueScore($valueScore);
    $amount = calculateNetInvestment($price, $recoveryAmount);

    if ($amount === 0) {
        return null;
    }

    return round($score / $amount * 1000, 2);
}

function formatRoiScore($price, $valueScore, $recoveryAmount = 0)
{
    $amount = calculateNetInvestment($price, $recoveryAmount);
    $score = normalizeValueScore($valueScore);

    if ($amount === 0) {
        return $score > 0 ? '∞' : '-';
    }

    return formatRoiValue(calculateRoiScore($price, $score, $recoveryAmount));
}

function roiScoreLabel($roi)
{
    if ($roi === null) {
        return '-';
    }

    if ($roi === INF) {
        return '最高';
    }

    $value = (float)$roi;

    if ($value >= 4) {
        return '高い';
    }

    if ($value >= 2) {
        return '良い';
    }

    if ($value >= 1) {
        return 'ふつう';
    }

    return '低い';
}

function formatRoiValue($roi)
{
    if ($roi === null) {
        return '-';
    }

    if ($roi === INF) {
        return '∞';
    }

    return number_format((float)$roi, 2);
}

function renderStars($valueScore)
{
    $score = normalizeValueScore($valueScore);
    return str_repeat('★', $score) . str_repeat('☆', 5 - $score);
}

function cleanReadingNote($value)
{
    $note = (string)$value;
    $note = preg_replace('/<br\s*\/?>\s*<b>Warning<\/b>:\s*.*?(?:<br\s*\/?>)?/is', '', $note);
    $note = trim($note);

    if (function_exists('mb_substr')) {
        return mb_substr($note, 0, 1000, 'UTF-8');
    }

    return substr($note, 0, 1000);
}

function statusClass($status)
{
    if ($status === '未読') { return 'status-unread'; }
    if ($status === '読書中') { return 'status-reading'; }
    if ($status === '読了') { return 'status-done'; }
    return '';
}

function normalizeLearningAxis($value)
{
    $allowed = ['深める', '広げる', '両方'];
    return in_array($value, $allowed, true) ? $value : '深める';
}

function normalizeExitAction($value)
{
    $allowed = ['未定', '本棚に残す', '古本屋で売る', 'メルカリで売る', '図書館に返す', '返却済み', 'デジタルで保管'];
    return in_array($value, $allowed, true) ? $value : '未定';
}
