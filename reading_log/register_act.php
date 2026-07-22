<?php
// 会員登録の処理（フォームから送られてきた内容を保存する）
require_once __DIR__ . '/functions.php';

// フォーム以外（URL直打ちなど）で来たら追い返す
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

$login_id = trim((string)($_POST['login_id'] ?? ''));
$login_pw = (string)($_POST['login_pw'] ?? '');

// 1. 空っぽチェック
if ($login_id === '' || $login_pw === '') {
    header('Location: register.php?error=empty');
    exit();
}

// 1-2. 合言葉（招待コード）チェック
//   公開先で 'register_code' が設定されていれば、正しい合言葉が無いと登録させない。
//   hash_equals は「時間差から合言葉を推測される攻撃」を防ぐための安全な比較。
$requiredCode = getRegisterCode();
if ($requiredCode !== '') {
    $inputCode = trim((string)($_POST['register_code'] ?? ''));
    if (!hash_equals($requiredCode, $inputCode)) {
        header('Location: register.php?error=code');
        exit();
    }
}

$pdo = connectDb();

// 2. 同じIDがすでに登録されていないか確認する
$stmt = $pdo->prepare('SELECT id FROM gs_user_table WHERE login_id = :login_id');
$stmt->bindValue(':login_id', $login_id, PDO::PARAM_STR);
$stmt->execute();
if ($stmt->fetch()) {
    header('Location: register.php?error=exists');
    exit();
}

// 3. パスワードを暗号化する（hash.php で習ったやつ！そのままは保存しない）
$hash = password_hash($login_pw, PASSWORD_DEFAULT);

// 4. 保存する
$stmt = $pdo->prepare('INSERT INTO gs_user_table (login_id, login_pw) VALUES (:login_id, :login_pw)');
$stmt->bindValue(':login_id', $login_id, PDO::PARAM_STR);
$stmt->bindValue(':login_pw', $hash, PDO::PARAM_STR);
$stmt->execute();

// 5. 登録できたらログインページへ
header('Location: login.php?registered=1');
exit();
