<?php
// ★このページはログインしないと見られない
require_once __DIR__ . '/functions.php';
loginCheck(); // ← ログインしていない人はここで止める（門番）
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - トップ</title>
    <link rel="stylesheet" href="style.css?v=20260705-2110">
    <script src="books-config.js" defer></script>
    <script src="books-search.js?v=20260705-2110" defer></script>
</head>

<body>
    <header>
        <h1>Book Bank</h1>
        <nav>
            <a href="index.php" class="active" aria-current="page">トップ</a>
            <a href="select.php">本棚</a>
            <a href="analytics.php">ダッシュボード</a>
            <a href="log.php">読書レビュー</a>
            <a href="logout.php">ログアウト</a>
        </nav>
    </header>

    <main class="main-wrapper">
        <section class="book-flow" aria-label="Book Bank の流れ">
            <span>選ぶ</span>
            <span>買う・借りる</span>
            <span>資産に入れる</span>
            <span>読む</span>
            <span>レビュー</span>
            <span>売る・残す</span>
        </section>

        <section class="book-search-panel" aria-labelledby="book-search-title">
            <h2 id="book-search-title">知識資産にする本を探す</h2>
            <div class="book-search-row">
                <label class="sr-only" for="book-keyword">本のタイトルや著者で検索</label>
                <input type="search" id="book-keyword" placeholder="例：7つの習慣、村上春樹">
                <button type="button" id="book-search-button" class="secondary-button">検索</button>
            </div>
            <p id="book-search-message" class="search-message" aria-live="polite"></p>
            <div id="book-results" class="book-results"></div>
        </section>

        <section class="acquisition-panel" id="acquisition-panel" hidden aria-labelledby="acquisition-title">
            <div class="section-heading compact-heading">
                <h2 id="acquisition-title">入口を比べる</h2>
            </div>
            <div class="acquisition-grid">
                <article class="acquisition-card">
                    <h3>Amazon</h3>
                    <p>紙の本で買う。読後に売れる。</p>
                    <a id="amazon-link" href="#" target="_blank" rel="noopener">Amazonで探す</a>
                    <button type="button" class="method-button" data-method="Amazon">Amazonで登録</button>
                </article>
                <article class="acquisition-card">
                    <h3>Kindle</h3>
                    <p>電子書籍で買う</p>
                    <a id="kindle-link" href="#" target="_blank" rel="noopener">Kindleで探す</a>
                    <button type="button" class="method-button" data-method="Kindle">Kindleで登録</button>
                </article>
                <article class="acquisition-card">
                    <h3>図書館</h3>
                    <p>0円で借りる</p>
                    <a id="library-link" href="#" target="_blank" rel="noopener">図書館で探す</a>
                    <button type="button" class="method-button" data-method="図書館" data-price="0">図書館で登録</button>
                </article>
                <article class="acquisition-card">
                    <h3>メルカリ</h3>
                    <p>中古で安く探す。読後に売れる。</p>
                    <a id="mercari-link" href="#" target="_blank" rel="noopener">メルカリで探す</a>
                    <button type="button" class="method-button" data-method="メルカリ">メルカリで登録</button>
                </article>
            </div>
        </section>

        <form action="insert.php" method="post">
            <fieldset>
                <legend>知識資産として登録する</legend>

                <input type="hidden" id="cover_image" name="cover_image" value="">

                <label for="title">本のタイトル</label>
                <input type="text" id="title" name="title" required placeholder="例：7つの習慣">

                <label for="author">著者</label>
                <input type="text" id="author" name="author" required placeholder="例：スティーブン・R・コヴィー">

                <label for="acquisition_method">入手方法</label>
                <select id="acquisition_method" name="acquisition_method">
                    <option value="Amazon">Amazon</option>
                    <option value="Kindle">Kindle</option>
                    <option value="図書館">図書館</option>
                    <option value="メルカリ">メルカリ</option>
                    <option value="書店">書店</option>
                    <option value="レンタル">レンタル</option>
                    <option value="もらった">もらった</option>
                    <option value="その他" selected>その他</option>
                </select>
                <label for="theme">テーマ（Google Booksから自動入力）</label>
                <input type="text" id="theme" name="theme" readonly placeholder="本を選ぶと自動で入ります">

                <label for="price">読書投資額（価格情報があれば自動入力）</label>
                <input type="number" id="price" name="price" min="0" step="1" placeholder="価格が取れない場合は入力">

                <label for="page_count">ページ数（Google Booksから自動入力）</label>
                <input type="number" id="page_count" name="page_count" min="0" step="1" placeholder="ページ数が取れない場合は入力">

                <label for="status">読書状況</label>
                <select id="status" name="status">
                    <option value="未読">未読</option>
                    <option value="読書中">読書中</option>
                    <option value="読了">読了</option>
                </select>

                <label for="exit_action">読後の出口</label>
                <select id="exit_action" name="exit_action">
                    <option value="未定" selected>未定</option>
                    <option value="本棚に残す">本棚に残す</option>
                    <option value="古本屋で売る">古本屋で売る</option>
                    <option value="メルカリで売る">メルカリで売る</option>
                    <option value="図書館に返す">図書館に返す</option>
                    <option value="デジタルで保管">デジタルで保管</option>
                </select>

                <label for="recovery_amount">回収額</label>
                <input type="number" id="recovery_amount" name="recovery_amount" min="0" step="1" placeholder="売った金額・戻る金額">

                <label for="return_due_date">返却日</label>
                <input type="date" id="return_due_date" name="return_due_date">

                <label for="learning_axis">学びの方向</label>
                <select id="learning_axis" name="learning_axis">
                    <option value="深める" selected>深める</option>
                    <option value="広げる">広げる</option>
                    <option value="両方">両方</option>
                </select>

                <label for="value_score">レビュー</label>
                <select id="value_score" name="value_score">
                    <option value="1">★☆☆☆☆</option>
                    <option value="2">★★☆☆☆</option>
                    <option value="3" selected>★★★☆☆</option>
                    <option value="4">★★★★☆</option>
                    <option value="5">★★★★★</option>
                </select>

                <label for="learning_note">読書レビュー</label>
                <textarea id="learning_note" name="learning_note" rows="4" maxlength="1000" placeholder="知識資産として残したいことを1つだけ書く"></textarea>
                <p class="field-help">1000文字まで</p>

                <button type="submit">Book Bankに入れる</button>
            </fieldset>
        </form>
    </main>
</body>

</html>
