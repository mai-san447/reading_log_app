<?php
require_once __DIR__ . '/functions.php';
$pdo = connectDb();

$saved = isset($_GET['saved']) && $_GET['saved'] === '1';

$stmt = $pdo->prepare('SELECT * FROM gs_reading_log ORDER BY created_at DESC');
$status = $stmt->execute();

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('ErrorQuery:' . $error[2]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - 本棚</title>
    <link rel="stylesheet" href="style.css?v=20260705-2110">
</head>

<body>
    <header>
        <h1>Book Bank</h1>
        <nav>
            <a href="index.php">トップ</a>
            <a href="select.php" class="active" aria-current="page">本棚</a>
            <a href="analytics.php">ダッシュボード</a>
            <a href="log.php">読書レビュー</a>
        </nav>
    </header>

    <main class="bookshelf-page bookshelf-only-page">
        <?php if ($saved): ?>
            <p class="toast">本棚に保存しました。</p>
        <?php endif; ?>

        <?php if (count($rows) === 0): ?>
            <p class="empty">まだ本棚に本がありません。まずはトップで本を探してください。</p>
        <?php else: ?>
            <section class="view-section" aria-labelledby="bookshelf-title">
                <div class="section-heading compact-heading">
                    <h2 id="bookshelf-title">本棚</h2>
                </div>

                <div class="shelf-grid">
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $readingNote = cleanReadingNote($row['learning_note'] ?? '');
                        if ($readingNote === '') {
                            $readingNote = cleanReadingNote($row['memo'] ?? '');
                        }
                        ?>
                        <a class="shelf-book-card" href="edit.php?id=<?php echo h($row['id']); ?>" title="読書レビューを書く・編集する：<?php echo h($row['title']); ?>">
                            <span class="shelf-book <?php echo h(statusClass($row['status'])); ?>">
                                <?php if (!empty($row['cover_image'])): ?>
                                    <img src="<?php echo h($row['cover_image']); ?>" alt="<?php echo h($row['title']); ?>の表紙">
                                <?php else: ?>
                                    <span aria-label="表紙画像なし"></span>
                                <?php endif; ?>
                            </span>
                            <span class="shelf-stars" aria-label="レビュー <?php echo h((string)(int)($row['value_score'] ?? 3)); ?> 点"><?php echo h(renderStars($row['value_score'] ?? 3)); ?></span>
                            <span class="shelf-review-mark<?php echo $readingNote === '' ? ' is-empty' : ''; ?>" <?php echo $readingNote === '' ? 'aria-hidden="true"' : ''; ?>>レビューあり</span>
                            <span class="shelf-exit"><?php echo h($row['exit_action'] ?? '未定'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>
