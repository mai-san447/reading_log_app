<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: select.php');
    exit();
}

// フォームから来たデータを取得
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$author = isset($_POST['author']) ? trim($_POST['author']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '読了';
$memo = isset($_POST['memo']) ? trim($_POST['memo']) : '';

// バリデーション（タイトルと著者が空でないかチェック）
if ($id === 0 || $title === '' || $author === '') {
    exit('IDまたは必須項目が不足しています。');
}

try {
    $pdo = connectDb();
    $sql = 'UPDATE gs_reading_log SET title = :title, author = :author, status = :status, memo = :memo WHERE id = :id';
    $stmt = $pdo->prepare($sql);

    // 値をバインド（注射対策）
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':author', $author, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':memo', $memo, PDO::PARAM_STR);

    // 実行
    $executeStatus = $stmt->execute();

    if ($executeStatus === false) {
        $error = $stmt->errorInfo();
        throw new Exception('QueryError: ' . (isset($error[2]) ? $error[2] : 'unknown error'));
    }

    // 更新成功 → 一覧ページに戻る
    header('Location: select.php');
    exit();
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'UpdateError: ' . $e->getMessage();
    exit();
}

