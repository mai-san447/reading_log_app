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
                        <h2><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="meta-row">
                            <p class="meta">著者: <?php echo htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <span class="status-badge"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <?php if (trim($row['memo']) !== ''): ?>
                            <p class="memo"><?php echo nl2br(htmlspecialchars($row['memo'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <?php endif; ?>
                        <p class="date"><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
