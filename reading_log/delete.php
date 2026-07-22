<?php
require_once __DIR__ . '/functions.php';
loginCheck(); // ← ログインしていない人はここで止める（門番）

// URLから id をもらう（?id=1 みたいな形で来る）
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    exit('IDが指定されていません。');
}

try {
    $pdo = connectDb();
    $sql = 'DELETE FROM gs_reading_log WHERE id = :id';
    $stmt = $pdo->prepare($sql);

    // 値をバインド（注射対策）
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    // 実行
    $executeStatus = $stmt->execute();

    if ($executeStatus === false) {
        $error = $stmt->errorInfo();
        throw new Exception('QueryError: ' . (isset($error[2]) ? $error[2] : 'unknown error'));
    }

    // 削除成功 → 一覧ページに戻る
    header('Location: select.php');
    exit();
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'DeleteError: ' . $e->getMessage();
    exit();
}
