<?php
require_once 'functions.php';
$pdo = connectDb();

$stmt = $pdo->prepare('SELECT * FROM gs_bookmark_table ORDER BY created_at DESC');
$status = $stmt->execute();

if ($status === false) {
  $error = $stmt->errorInfo();
  exit('ErrorQuery:' . $error[2]);
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ブックマーク一覧</title>
  <link href="css/style.css" rel="stylesheet">
</head>

<body>
  <header>
    <nav>
      <a href="index.php">ブックマークを追加</a>
    </nav>
  </header>

  <main>
    <div class="container">
      <h1>保存済みブックマーク</h1>

      <?php if ($stmt->rowCount() === 0): ?>
        <p class="empty-message">まだ保存されたブックマークはありません。新しいブックマークを追加してください。</p>
      <?php else: ?>
        <div class="bookmark-list">
          <?php while ($bookmark = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <article class="bookmark-card">
              <div class="bookmark-header">
                <h2><?php echo h($bookmark['title']); ?></h2>
                <span class="bookmark-category"><?php echo h($bookmark['category']); ?></span>
              </div>

              <p class="bookmark-url">
                <a href="<?php echo h($bookmark['url']); ?>" target="_blank" rel="noopener noreferrer">
                  <?php echo h($bookmark['url']); ?>
                </a>
              </p>

              <?php if (trim($bookmark['memo']) !== ''): ?>
                <p class="bookmark-memo"><?php echo nl2br(h($bookmark['memo'])); ?></p>
              <?php endif; ?>

              <p class="bookmark-date"><?php echo h($bookmark['created_at']); ?></p>
            </article>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

</body>

</html>