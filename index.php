<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>データ登録</title>
    <link href="css/style.css" rel="stylesheet">

</head>

<body>

    <!-- Head[Start] -->
    <header>
        <nav>
            <a href="select.php">データ一覧</a>
        </nav>
    </header>
    <!-- Head[End] -->

    <!-- Main[Start] -->
    <main>
        <form method="post" action="insert.php">
            <fieldset>
                <legend>フリーアンケート</legend>
                <label for="name">名前</label>
                <input type="text" id="name" name="name" required placeholder="山田 太郎">

                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" required placeholder="example@email.com">

                <label for="content">内容</label>
                <textarea id="content" name="content" rows="4" required placeholder="ご意見やご感想をお聞かせください"></textarea>

                <input type="submit" value="送信する">
            </fieldset>
        </form>
    </main>
    <!-- Main[End] -->


</body>

</html>