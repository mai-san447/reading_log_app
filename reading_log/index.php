<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>読書ログ登録</title>
    <link rel="stylesheet" href="style.css">
    <script src="books-config.js" defer></script>
    <script src="books-search.js" defer></script>
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
        <section class="book-search-panel" aria-labelledby="book-search-title">
            <h2 id="book-search-title">Google Books から本を探す</h2>
            <div class="book-search-row">
                <label class="sr-only" for="book-keyword">本のタイトルや著者で検索</label>
                <input type="search" id="book-keyword" placeholder="例：7つの習慣、村上春樹">
                <button type="button" id="book-search-button" class="secondary-button">検索</button>
            </div>
            <p id="book-search-message" class="search-message" aria-live="polite"></p>
            <div id="book-results" class="book-results"></div>
        </section>

        <form action="insert.php" method="post">
            <fieldset>
                <legend>本の情報を入力する</legend>

                <div class="selected-cover" id="selected-cover" hidden>
                    <img id="selected-cover-image" src="" alt="選択した本の表紙">
                    <p>この表紙画像も一緒に保存します。</p>
                </div>
                <input type="hidden" id="cover_image" name="cover_image" value="">

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


