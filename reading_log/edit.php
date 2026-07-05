<?php
require_once __DIR__ . '/functions.php';
$pdo = connectDb();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    exit('IDが指定されていません。');
}

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

$readingNote = cleanReadingNote($row['learning_note'] ?? '');
if ($readingNote === '') {
    $readingNote = cleanReadingNote($row['memo'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - 読書レビュー</title>
    <link rel="stylesheet" href="style.css?v=20260705-2110">
</head>

<body>
    <header>
        <h1>Book Bank</h1>
        <nav>
            <a href="index.php">トップ</a>
            <a href="select.php">本棚</a>
            <a href="analytics.php">ダッシュボード</a>
            <a href="log.php" class="active" aria-current="page">読書レビュー</a>
        </nav>
    </header>

    <main class="main-wrapper">
        <section class="page-header">
            <h2>読書レビュー / 知識資産</h2>
        </section>

        <form action="update.php" method="post">
            <input type="hidden" name="id" value="<?php echo h($row['id']); ?>">

            <fieldset>
                <legend>知識資産の内容を修正する</legend>

                <label for="title">本のタイトル</label>
                <input type="text" id="title" name="title" required placeholder="例：7つの習慣" value="<?php echo h($row['title']); ?>">

                <label for="author">著者</label>
                <input type="text" id="author" name="author" required placeholder="例：スティーブン・R・コヴィー" value="<?php echo h($row['author']); ?>">

                <label for="acquisition_method">入手方法</label>
                <select id="acquisition_method" name="acquisition_method">
                    <?php foreach (['Amazon', 'Kindle', '図書館', 'メルカリ', '書店', 'レンタル', 'もらった', 'その他'] as $method): ?>
                        <option value="<?php echo h($method); ?>" <?php echo ($row['acquisition_method'] ?? 'その他') === $method ? 'selected' : ''; ?>><?php echo h($method); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="theme">テーマ</label>
                <input type="text" id="theme" name="theme" placeholder="例：自己啓発、仕事、小説、デザイン" value="<?php echo h($row['theme'] ?? ''); ?>">

                <label for="price">読書投資額</label>
                <input type="number" id="price" name="price" min="0" step="1" placeholder="例：1500" value="<?php echo h($row['price'] ?? 0); ?>">

                <label for="recovery_amount">回収額</label>
                <input type="number" id="recovery_amount" name="recovery_amount" min="0" step="1" placeholder="売った金額・戻る金額" value="<?php echo h($row['recovery_amount'] ?? 0); ?>">

                <label for="page_count">ページ数</label>
                <input type="number" id="page_count" name="page_count" min="0" step="1" placeholder="例：240" value="<?php echo h($row['page_count'] ?? 0); ?>">

                <label for="status">読書状況</label>
                <select id="status" name="status">
                    <option value="未読" <?php echo $row['status'] === '未読' ? 'selected' : ''; ?>>未読</option>
                    <option value="読書中" <?php echo $row['status'] === '読書中' ? 'selected' : ''; ?>>読書中</option>
                    <option value="読了" <?php echo $row['status'] === '読了' ? 'selected' : ''; ?>>読了</option>
                </select>

                <label for="exit_action">読後の出口</label>
                <select id="exit_action" name="exit_action">
                    <?php foreach (['未定', '本棚に残す', '古本屋で売る', 'メルカリで売る', '図書館に返す', '返却済み', 'デジタルで保管'] as $action): ?>
                        <option value="<?php echo h($action); ?>" <?php echo ($row['exit_action'] ?? '未定') === $action ? 'selected' : ''; ?>><?php echo h($action); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="return_due_date">返却日</label>
                <input type="date" id="return_due_date" name="return_due_date" value="<?php echo h($row['return_due_date'] ?? ''); ?>">

                <label for="learning_axis">学びの方向</label>
                <select id="learning_axis" name="learning_axis">
                    <?php foreach (['深める', '広げる', '両方'] as $axis): ?>
                        <option value="<?php echo h($axis); ?>" <?php echo ($row['learning_axis'] ?? '深める') === $axis ? 'selected' : ''; ?>><?php echo h($axis); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="value_score">レビュー</label>
                <select id="value_score" name="value_score">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (int)($row['value_score'] ?? 3) === $i ? 'selected' : ''; ?>><?php echo h(renderStars($i)); ?></option>
                    <?php endfor; ?>
                </select>

                <label for="learning_note">読書レビュー</label>
                <textarea id="learning_note" name="learning_note" rows="4" maxlength="1000" placeholder="知識資産として残したいことを1つだけ書く"><?php echo h($readingNote); ?></textarea>
                <p class="field-help">1000文字まで</p>

                <button type="submit">知識資産を保存する</button>
            </fieldset>
        </form>
    </main>
</body>

</html>
