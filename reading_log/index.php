<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>読書ログ登録</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>読書ログ登録</h1>
        <nav>
            <a href="select.php">登録済みログを見る</a>
        </nav>
    </header>

    <main class="main-wrapper">
        <section class="page-header">
            <h2>読んだ本をあとからでも見返せるように保存します。</h2>
            <p>タイトル、著者、読書状況、メモを残して、自分だけの読書ログを作成しましょう。</p>
        </section>

        <form action="insert.php" method="post">
            <fieldset>
                <legend>本の情報を入力する</legend>

                <label for="title">本のタイトル</label>
                <input type="text" id="title" name="title" required placeholder="例：7つの習慣">

                <label for="author">著者</label>
                <input type="text" id="author" name="author" required placeholder="例：スティーブン・R・コヴィー">

                <label for="status">読書状況</label>
                <select id="status" name="status">
                    <option value="未読">未読</option>
                    <option value="読書中">読書中</option>
                    <option value="読了" selected>読了</option>
                </select>

                <label for="memo">ひとことメモ</label>
                <textarea id="memo" name="memo" rows="5" placeholder="感想や読みどころを記録"></textarea>

                <button type="submit">ログを保存する</button>
            </fieldset>
        </form>
    </main>
</body>

</html>
