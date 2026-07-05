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
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>読書ログ一覧</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>読書ログ一覧</h1>
        <nav>
            <a href="index.php">新しいログを追加</a>
        </nav>
    </header>

    <main>
        <div class="log-list">
            <?php if ($stmt->rowCount() === 0): ?>
                <p class="empty">まだ読書ログがありません。まずは本を追加してください。</p>
            <?php else: ?>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <article class="log-card">
                        <div class="log-card-layout">
                            <div class="log-cover">
                                <?php if (!empty($row['cover_image'])): ?>
                                    <img src="<?php echo h($row['cover_image']); ?>" alt="<?php echo h($row['title']); ?>の表紙">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </div>
                            <div class="log-content">
                                <h2><?php echo h($row['title']); ?></h2>
                                <div class="meta-row">
                                    <p class="meta">著者: <?php echo h($row['author']); ?></p>
                                    <span class="status-badge"><?php echo h($row['status']); ?></span>
                                </div>
                                <?php if (trim($row['memo']) !== ''): ?>
                                    <p class="memo"><?php echo nl2br(h($row['memo'])); ?></p>
                                <?php endif; ?>
                                <p class="date"><?php echo h($row['created_at']); ?></p>
                                <div class="action-buttons">
                                    <a href="edit.php?id=<?php echo h($row['id']); ?>" class="btn-edit">編集</a>
                                    <a href="delete.php?id=<?php echo h($row['id']); ?>" class="btn-delete" onclick="return confirm('削除しますか？');">削除</a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
