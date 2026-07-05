<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/insert-debug.log';
if (!file_exists($logFile)) {
    @file_put_contents($logFile, '');
}
function logInsert($message)
{
    global $logFile;
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", 3, $logFile);
}

if (!file_exists(__DIR__ . '/functions.php')) {
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Missing functions.php in reading_log folder.');
}

require_once __DIR__ . '/functions.php';

if (!function_exists('connectDb')) {
    header('Content-Type: text/plain; charset=UTF-8');
    exit('functions.php loaded but connectDb() not found.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$rawTitle = isset($_POST['title']) ? $_POST['title'] : '';
$rawAuthor = isset($_POST['author']) ? $_POST['author'] : '';
$rawStatus = isset($_POST['status']) ? $_POST['status'] : '読了';
$rawMemo = isset($_POST['memo']) ? $_POST['memo'] : '';
$rawCoverImage = isset($_POST['cover_image']) ? $_POST['cover_image'] : '';

$title = trim($rawTitle);
$author = trim($rawAuthor);
$readingStatus = trim($rawStatus);
$memo = trim($rawMemo);
$coverImage = trim($rawCoverImage);

logInsert('insert.php started');
logInsert('POST=' . print_r($_POST, true));
logInsert('Parsed title=' . $title . ' author=' . $author . ' status=' . $readingStatus);

if ($title === '' || $author === '') {
    logInsert('Validation failed: title or author empty');
    exit('タイトルと著者を入力してください。');
}
if (!isValidHttpUrl($coverImage)) {
    logInsert('Validation failed: invalid cover image URL');
    exit('表紙画像URLが正しくありません。');
}

try {
    $pdo = connectDb();
    logInsert('connectDb succeeded');
    $sql = 'INSERT INTO gs_reading_log (`title`, `author`, `cover_image`, `status`, `memo`) VALUES (:title, :author, :cover_image, :status, :memo)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':author', $author, PDO::PARAM_STR);
    $stmt->bindValue(':cover_image', $coverImage, PDO::PARAM_STR);
    $stmt->bindValue(':status', $readingStatus, PDO::PARAM_STR);
    $stmt->bindValue(':memo', $memo, PDO::PARAM_STR);

    $executeStatus = $stmt->execute();
    logInsert('executeStatus=' . var_export($executeStatus, true));

    if ($executeStatus === false) {
        $error = $stmt->errorInfo();
        logInsert('Statement error: ' . print_r($error, true));
        throw new Exception('QueryError: ' . (isset($error[2]) ? $error[2] : 'unknown error'));
    }

    logInsert('Insert succeeded');
    header('Location: select.php?saved=1');
    exit();
} catch (Exception $e) {
    logInsert('Insert exception: ' . $e->getMessage());
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'InsertError: ' . $e->getMessage() . "\n";
    if (method_exists($e, 'getTraceAsString')) {
        echo $e->getTraceAsString();
    }
    exit();
}




