<?php
// 会員登録ページ（見た目だけ。処理は register_act.php がやる）
require_once __DIR__ . '/functions.php';

$error = $_GET['error'] ?? '';
// 合言葉（招待コード）が設定されていれば、登録フォームに合言葉欄を出す
$requireCode = getRegisterCode() !== '';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - 会員登録</title>
    <link rel="stylesheet" href="style.css?v=20260705-2110">
</head>

<body>
    <header>
        <h1>Book Bank</h1>
        <nav>
            <a href="login.php">ログイン</a>
            <a href="register.php" class="active" aria-current="page">会員登録</a>
        </nav>
    </header>

    <main class="main-wrapper">
        <form action="register_act.php" method="post">
            <fieldset>
                <legend>会員登録</legend>

                <?php if ($error === 'exists'): ?>
                    <p class="toast">そのIDはすでに使われています。別のIDにしてください。</p>
                <?php elseif ($error === 'empty'): ?>
                    <p class="toast">IDとパスワードの両方を入力してください。</p>
                <?php elseif ($error === 'code'): ?>
                    <p class="toast">合言葉が違います。管理者に確認してください。</p>
                <?php endif; ?>

                <label for="login_id">ユーザーID</label>
                <input type="text" id="login_id" name="login_id" required placeholder="好きなIDを決めてね">

                <label for="login_pw">パスワード</label>
                <input type="password" id="login_pw" name="login_pw" required placeholder="4文字以上">

                <?php if ($requireCode): ?>
                    <label for="register_code">合言葉（招待コード）</label>
                    <input type="text" id="register_code" name="register_code" required placeholder="管理者から聞いた合言葉">
                <?php endif; ?>

                <button type="submit">登録する</button>
            </fieldset>
        </form>
        <p style="text-align:center;">すでに登録した人は <a href="login.php">ログイン</a> へ</p>
    </main>
</body>

</html>
