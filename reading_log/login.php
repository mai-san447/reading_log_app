<?php
// ログインページ（見た目だけ。処理は login_act.php がやる）
require_once __DIR__ . '/functions.php';

$error = isset($_GET['error']);
$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - ログイン</title>
    <link rel="stylesheet" href="style.css?v=20260705-2110">
</head>

<body>
    <header>
        <h1>Book Bank</h1>
        <nav>
            <a href="login.php" class="active" aria-current="page">ログイン</a>
            <a href="register.php">会員登録</a>
        </nav>
    </header>

    <main class="main-wrapper">
        <form action="login_act.php" method="post">
            <fieldset>
                <legend>ログイン</legend>

                <?php if ($registered): ?>
                    <p class="toast">登録できました。ログインしてください。</p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <p class="toast">IDかパスワードが違います。</p>
                <?php endif; ?>

                <label for="login_id">ユーザーID</label>
                <input type="text" id="login_id" name="login_id" required placeholder="ユーザーID">

                <label for="login_pw">パスワード</label>
                <input type="password" id="login_pw" name="login_pw" required placeholder="パスワード">

                <button type="submit">ログイン</button>
            </fieldset>
        </form>
        <p style="text-align:center;">はじめての人は <a href="register.php">会員登録</a> へ</p>
    </main>
</body>

</html>
