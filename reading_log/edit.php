<?php
require_once __DIR__ . '/functions.php';
$pdo = connectDb();

// URLから id をもらう（?id=1 みたいな形で来る）
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    exit('IDが指定されていません。');
}

// DBから、この id の本の情報を探してくる
$stmt = $pdo->prepare('SELECT * FROM gs_reading_log WHERE id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('ErrorQuery:' . $error[2]);
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    exit('このIDのデータが見つかりません。');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>読書ログ編集</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>読書ログ編集</h1>
        <nav>
            <a href="select.php">一覧に戻る</a>
        </nav>
    </header>

    <main class="main-wrapper">
        <section class="page-header">
            <h2>本の情報を編集します</h2>
        </section>

        <!-- form は update.php に送る -->
        <form action="update.php" method="post">
            <!-- IDを隠れたデータとして送っておく（update.php で使うから） -->
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

            <fieldset>
                <legend>本の情報を修正する</legend>

                <label for="title">本のタイトル</label>
                <input type="text" id="title" name="title" required placeholder="例：7つの習慣" value="<?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="author">著者</label>
                <input type="text" id="author" name="author" required placeholder="例：スティーブン・R・コヴィー" value="<?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="status">読書状況</label>
                <select id="status" name="status">
                    <option value="未読" <?php echo $row['status'] === '未読' ? 'selected' : ''; ?>>未読</option>
                    <option value="読書中" <?php echo $row['status'] === '読書中' ? 'selected' : ''; ?>>読書中</option>
                    <option value="読了" <?php echo $row['status'] === '読了' ? 'selected' : ''; ?>>読了</option>
                </select>

                <label for="memo">ひとことメモ</label>
                <textarea id="memo" name="memo" rows="5" placeholder="感想や読みどころを記録"><?php echo htmlspecialchars($row['memo'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                <button type="submit">修正内容を保存する</button>
            </fieldset>
        </form>
    </main>
</body>

</html>

