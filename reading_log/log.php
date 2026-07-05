<?php
require_once __DIR__ . '/functions.php';
$pdo = connectDb();

$order = (isset($_GET['order']) && $_GET['order'] === 'asc') ? 'ASC' : 'DESC';
$currentOrder = strtolower($order);
$stmt = $pdo->prepare('SELECT * FROM gs_reading_log ORDER BY created_at ' . $order . ', id ' . $order);
$status = $stmt->execute();

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('ErrorQuery:' . $error[2]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function reviewExcerpt($text, $length = 120)
{
    $value = (string)$text;

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length, 'UTF-8');
    }

    return substr($value, 0, $length);
}

function isLongReview($text, $length = 120)
{
    $value = (string)$text;

    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8') > $length;
    }

    return strlen($value) > $length;
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

    <main class="bookshelf-page">
        <section class="view-section" aria-labelledby="log-table-title">
            <div class="section-heading log-heading">
                <h2 id="log-table-title">読書レビュー</h2>
                <div class="sort-controls" aria-label="登録日の並び替え">
                    <a class="<?php echo $currentOrder === 'desc' ? 'active' : ''; ?>" href="log.php?order=desc">新しい順</a>
                    <a class="<?php echo $currentOrder === 'asc' ? 'active' : ''; ?>" href="log.php?order=asc">古い順</a>
                </div>
            </div>
            <?php if (!$rows): ?>
                <p class="empty">まだ本棚に本がありません。まずはトップで本を探してください。</p>
            <?php else: ?>
                <div class="review-list">
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $readingNote = cleanReadingNote($row['learning_note'] ?? '');
                        if ($readingNote === '') {
                            $readingNote = cleanReadingNote($row['memo'] ?? '');
                        }
                        $registeredAt = '';
                        if (!empty($row['created_at'])) {
                            $registeredAt = date('Y/m/d H:i', strtotime($row['created_at']));
                        }
                        $netInvestment = calculateNetInvestment($row['price'] ?? 0, $row['recovery_amount'] ?? 0);
                        ?>
                        <article class="review-card">
                            <div class="review-cover">
                                <?php if (!empty($row['cover_image'])): ?>
                                    <img src="<?php echo h($row['cover_image']); ?>" alt="<?php echo h($row['title']); ?>の表紙">
                                <?php else: ?>
                                    <span>No Image</span>
                                <?php endif; ?>
                            </div>
                            <div class="review-body">
                                <div class="review-main">
                                    <h3><?php echo h($row['title']); ?></h3>
                                    <p class="review-author"><?php echo h($row['author']); ?></p>
                                    <?php if ($readingNote === ''): ?>
                                        <p class="review-note"><span class="empty-review">未入力</span></p>
                                    <?php elseif (isLongReview($readingNote)): ?>
                                        <details class="review-detail">
                                            <summary><span><?php echo h(reviewExcerpt($readingNote)); ?>...</span><em>全文を表示</em></summary>
                                            <p class="review-note-full"><?php echo nl2br(h($readingNote)); ?></p>
                                        </details>
                                    <?php else: ?>
                                        <p class="review-note"><?php echo nl2br(h($readingNote)); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="review-side">
                                    <div class="star-rating"><?php echo h(renderStars($row['value_score'] ?? 3)); ?></div>
                                    <span class="status-badge <?php echo h(statusClass($row['status'])); ?>"><?php echo h($row['status']); ?></span>
                                    <span class="review-money">実質 <?php echo h(number_format($netInvestment)); ?>円</span>
                                    <span class="review-exit"><?php echo h($row['exit_action'] ?? '未定'); ?></span>
                                    <time><?php echo h($registeredAt); ?></time>
                                    <div class="review-actions">
                                        <a href="edit.php?id=<?php echo h($row['id']); ?>" class="btn-edit">編集</a>
                                        <a href="delete.php?id=<?php echo h($row['id']); ?>" class="btn-delete" onclick="return confirm('削除しますか？');">削除</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
