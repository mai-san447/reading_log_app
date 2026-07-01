<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$title = trim((string)($_POST['title'] ?? ''));
$url = trim((string)($_POST['url'] ?? ''));
$category = trim((string)($_POST['category'] ?? 'Other'));
$memo = trim((string)($_POST['memo'] ?? ''));

if ($title === '' || $url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    exit('正しいタイトルとURLを入力してください。');
}

$pdo = connectDb();

$stmt = $pdo->prepare(
    'INSERT INTO gs_bookmark_table(title, url, category, memo) VALUES(:title, :url, :category, :memo)'
);
$stmt->bindValue(':title', $title, PDO::PARAM_STR);
$stmt->bindValue(':url', $url, PDO::PARAM_STR);
$stmt->bindValue(':category', $category, PDO::PARAM_STR);
$stmt->bindValue(':memo', $memo, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('ErrorMessage:' . $error[2]);
}

header('Location: select.php');
exit();
