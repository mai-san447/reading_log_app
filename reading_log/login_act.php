<?php
// ログインの処理（IDとパスワードが合っているか確認する）
require_once __DIR__ . '/functions.php';

// セッション（ログイン状態の記録）を始める
session_start();

// フォーム以外（URL直打ちなど）で来たら追い返す
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$login_id = trim((string)($_POST['login_id'] ?? ''));
$login_pw = (string)($_POST['login_pw'] ?? '');

$pdo = connectDb();

// 1. 入力されたIDのユーザーを探す
$stmt = $pdo->prepare('SELECT * FROM gs_user_table WHERE login_id = :login_id');
$stmt->bindValue(':login_id', $login_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. ユーザーがいて、パスワードも合っていればログイン成功
//    （保存されているのは暗号化された文字。password_verify で照合する）
if ($user && password_verify($login_pw, $user['login_pw'])) {
    // なりすまし対策：セッションIDを新しく作り直してから合言葉を保存
    session_regenerate_id(true);
    $_SESSION['chk_ssid'] = session_id();

    // ログインできたら本棚へ
    header('Location: select.php');
    exit();
}

// 3. 失敗したらログインページに戻す
header('Location: login.php?error=1');
exit();
