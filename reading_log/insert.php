<?php
require_once __DIR__ . '/functions.php';
loginCheck(); // ← ログインしていない人はここで止める（門番）

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$author = isset($_POST['author']) ? trim($_POST['author']) : '';
$acquisitionMethod = isset($_POST['acquisition_method']) ? trim($_POST['acquisition_method']) : 'その他';
$theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
$price = isset($_POST['price']) ? normalizePrice($_POST['price']) : 0;
$recoveryAmount = isset($_POST['recovery_amount']) ? normalizeRecoveryAmount($_POST['recovery_amount']) : 0;
$pageCount = isset($_POST['page_count']) ? normalizePageCount($_POST['page_count']) : 0;
$valueScore = isset($_POST['value_score']) ? normalizeValueScore($_POST['value_score']) : 3;
$learningAxis = isset($_POST['learning_axis']) ? normalizeLearningAxis($_POST['learning_axis']) : '深める';
$exitAction = isset($_POST['exit_action']) ? normalizeExitAction($_POST['exit_action']) : '未定';
$returnDueDate = isset($_POST['return_due_date']) ? normalizeReturnDueDate($_POST['return_due_date']) : '';
$readingNote = isset($_POST['learning_note']) ? cleanReadingNote($_POST['learning_note']) : '';
$readingStatus = isset($_POST['status']) ? trim($_POST['status']) : '読了';
$coverImage = isset($_POST['cover_image']) ? trim($_POST['cover_image']) : '';

if ($title === '' || $author === '') {
    exit('タイトルと著者を入力してください。');
}

if (!isValidHttpUrl($coverImage)) {
    exit('表紙画像URLが正しくありません。');
}

try {
    $pdo = connectDb();

    $sql = 'INSERT INTO gs_reading_log (`title`, `author`, `cover_image`, `acquisition_method`, `theme`, `price`, `recovery_amount`, `page_count`, `value_score`, `learning_axis`, `exit_action`, `return_due_date`, `learning_note`, `status`, `memo`) VALUES (:title, :author, :cover_image, :acquisition_method, :theme, :price, :recovery_amount, :page_count, :value_score, :learning_axis, :exit_action, :return_due_date, :learning_note, :status, :memo)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':author', $author, PDO::PARAM_STR);
    $stmt->bindValue(':cover_image', $coverImage, PDO::PARAM_STR);
    $stmt->bindValue(':acquisition_method', $acquisitionMethod, PDO::PARAM_STR);
    $stmt->bindValue(':theme', $theme, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_INT);
    $stmt->bindValue(':recovery_amount', $recoveryAmount, PDO::PARAM_INT);
    $stmt->bindValue(':page_count', $pageCount, PDO::PARAM_INT);
    $stmt->bindValue(':value_score', $valueScore, PDO::PARAM_INT);
    $stmt->bindValue(':learning_axis', $learningAxis, PDO::PARAM_STR);
    $stmt->bindValue(':exit_action', $exitAction, PDO::PARAM_STR);
    $stmt->bindValue(':return_due_date', $returnDueDate !== '' ? $returnDueDate : null, $returnDueDate !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':learning_note', $readingNote, PDO::PARAM_STR);
    $stmt->bindValue(':status', $readingStatus, PDO::PARAM_STR);
    $stmt->bindValue(':memo', $readingNote, PDO::PARAM_STR);

    $executeStatus = $stmt->execute();

    if ($executeStatus === false) {
        $error = $stmt->errorInfo();
        throw new Exception('QueryError: ' . (isset($error[2]) ? $error[2] : 'unknown error'));
    }

    header('Location: select.php?saved=1');
    exit();
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'InsertError: ' . $e->getMessage();
    exit();
}
