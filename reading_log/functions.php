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
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

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
