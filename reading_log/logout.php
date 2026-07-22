<?php
// ログアウトの処理（ログイン状態を消す）
require_once __DIR__ . '/functions.php';

session_start();

// 1. セッションの中身を空にする
$_SESSION = [];

// 2. クッキーに残っているセッション情報も消す
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// 3. セッションを完全に破棄する
session_destroy();

// 4. ログインページへ
header('Location: login.php');
exit();
