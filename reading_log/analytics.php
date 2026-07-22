<?php
require_once __DIR__ . '/functions.php';
loginCheck(); // ← ログインしていない人はここで止める（門番）
$pdo = connectDb();

$stmt = $pdo->prepare('SELECT * FROM gs_reading_log ORDER BY created_at DESC');
$status = $stmt->execute();

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('ErrorQuery:' . $error[2]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$authorCount = [];
$themeCount = [];
$methodCount = [];
$axisCount = [];
$exitActionCount = [];
$monthlySpend = [];
$monthlyRoi = [];
$yearlyRoi = [];
$dailyReading = [];
$totalSpend = 0;
$totalRecovery = 0;
$totalNetInvestment = 0;
$totalPages = 0;
$roiTotal = 0;
$roiCount = 0;
$topRoiBooks = [];
$returnReminders = [];
$inventoryCandidates = [];
$today = new DateTimeImmutable(date('Y-m-d'));

foreach ($rows as $row) {
    $author = trim($row['author'] ?? '');
    if ($author !== '') {
        $authorCount[$author] = ($authorCount[$author] ?? 0) + 1;
    }

    $theme = trim($row['theme'] ?? '');
    if ($theme === '') { $theme = '未設定'; }
    $themeCount[$theme] = ($themeCount[$theme] ?? 0) + 1;

    $method = trim($row['acquisition_method'] ?? '');
    if ($method === '') { $method = 'その他'; }
    $methodCount[$method] = ($methodCount[$method] ?? 0) + 1;

    $axis = trim($row['learning_axis'] ?? '');
    if ($axis === '') { $axis = '深める'; }
    $axisCount[$axis] = ($axisCount[$axis] ?? 0) + 1;

    $exitAction = trim($row['exit_action'] ?? '');
    if ($exitAction === '') { $exitAction = '未定'; }
    $exitActionCount[$exitAction] = ($exitActionCount[$exitAction] ?? 0) + 1;

    $price = (int)($row['price'] ?? 0);
    $recoveryAmount = normalizeRecoveryAmount($row['recovery_amount'] ?? 0);
    $netInvestment = calculateNetInvestment($price, $recoveryAmount);
    $pageCount = normalizePageCount($row['page_count'] ?? 0);
    $valueScore = normalizeValueScore($row['value_score'] ?? 3);
    $totalSpend += $price;
    $totalRecovery += $recoveryAmount;
    $totalNetInvestment += $netInvestment;
    $totalPages += $pageCount;

    if (in_array($exitAction, ['未定', '古本屋で売る', 'メルカリで売る'], true)) {
        $inventoryCandidates[] = [
            'title' => $row['title'] ?? '',
            'action' => $exitAction,
            'net' => $netInvestment,
        ];
    }

    $returnDueDate = normalizeReturnDueDate($row['return_due_date'] ?? '');
    $isLibraryBook = $method === '図書館' || $exitAction === '図書館に返す';
    if ($returnDueDate !== '' && $isLibraryBook && $exitAction !== '返却済み') {
        $due = new DateTimeImmutable($returnDueDate);
        $daysLeft = (int)$today->diff($due)->format('%r%a');
        if ($daysLeft <= 14) {
            $returnReminders[] = [
                'title' => $row['title'] ?? '',
                'date' => $returnDueDate,
                'days' => $daysLeft,
            ];
        }
    }

    $roiScore = calculateRoiScore($price, $valueScore, $recoveryAmount);
    if ($roiScore !== null) {
        $roiTotal += $roiScore;
        $roiCount++;
        $topRoiBooks[] = ['title' => $row['title'] ?? '', 'roi' => $roiScore];
    } elseif ($valueScore > 0) {
        $topRoiBooks[] = ['title' => $row['title'] ?? '', 'roi' => null];
    }

    $month = substr((string)($row['created_at'] ?? ''), 0, 7);
    if ($month === '') { $month = '日付なし'; }
    $monthlySpend[$month] = ($monthlySpend[$month] ?? 0) + $price;

    $date = substr((string)($row['created_at'] ?? ''), 0, 10);
    if ($date === '') { $date = '日付なし'; }
    if (!isset($dailyReading[$date])) {
        $dailyReading[$date] = ['pages' => 0, 'books' => 0];
    }
    $dailyReading[$date]['pages'] += $pageCount;
    $dailyReading[$date]['books']++;

    if (!isset($monthlyRoi[$month])) {
        $monthlyRoi[$month] = ['spend' => 0, 'recovery' => 0, 'score' => 0, 'count' => 0];
    }
    $monthlyRoi[$month]['spend'] += $price;
    $monthlyRoi[$month]['recovery'] += $recoveryAmount;
    $monthlyRoi[$month]['score'] += $valueScore;
    $monthlyRoi[$month]['count']++;

    $year = substr((string)($row['created_at'] ?? ''), 0, 4);
    if ($year === '') { $year = '日付なし'; }
    if (!isset($yearlyRoi[$year])) {
        $yearlyRoi[$year] = ['spend' => 0, 'recovery' => 0, 'score' => 0, 'count' => 0];
    }
    $yearlyRoi[$year]['spend'] += $price;
    $yearlyRoi[$year]['recovery'] += $recoveryAmount;
    $yearlyRoi[$year]['score'] += $valueScore;
    $yearlyRoi[$year]['count']++;
}

arsort($authorCount);
arsort($themeCount);
arsort($methodCount);
arsort($axisCount);
arsort($exitActionCount);
krsort($monthlySpend);
krsort($monthlyRoi);
krsort($yearlyRoi);
ksort($dailyReading);
usort($topRoiBooks, function ($a, $b) {
    if ($a['roi'] === null && $b['roi'] === null) { return 0; }
    if ($a['roi'] === null) { return -1; }
    if ($b['roi'] === null) { return 1; }
    return $b['roi'] <=> $a['roi'];
});
usort($returnReminders, function ($a, $b) {
    return $a['days'] <=> $b['days'];
});
usort($inventoryCandidates, function ($a, $b) {
    return $b['net'] <=> $a['net'];
});
$authorCount = array_slice($authorCount, 0, 5, true);
$themeCount = array_slice($themeCount, 0, 5, true);
$methodCount = array_slice($methodCount, 0, 5, true);
$axisCount = array_slice($axisCount, 0, 5, true);
$exitActionCount = array_slice($exitActionCount, 0, 5, true);
$monthlySpend = array_slice($monthlySpend, 0, 6, true);
$monthlyRoi = array_slice($monthlyRoi, 0, 6, true);
$yearlyRoi = array_slice($yearlyRoi, 0, 5, true);
if (count($dailyReading) > 14) {
    $dailyReading = array_slice($dailyReading, -14, 14, true);
}
$topRoiBooks = array_slice($topRoiBooks, 0, 5);
$returnReminders = array_slice($returnReminders, 0, 6);
$inventoryCandidates = array_slice($inventoryCandidates, 0, 6);
$averageRoi = $roiCount > 0 ? round($roiTotal / $roiCount, 2) : null;
$currentMonth = date('Y-m');
$currentYear = date('Y');
$currentMonthRoi = $monthlyRoi[$currentMonth] ?? null;
$currentYearRoi = $yearlyRoi[$currentYear] ?? null;

function renderBarChart($items, $unit)
{
    if (!$items) {
        echo '<p class="empty small">まだ集計できるデータがありません。</p>';
        return;
    }

    $max = max($items);
    foreach ($items as $label => $value) {
        $percent = $max > 0 ? max(4, round($value / $max * 100)) : 0;
        echo '<div class="analytics-bar-row">';
        echo '<span class="analytics-label">' . h($label) . '</span>';
        echo '<span class="analytics-track"><span class="analytics-fill" style="width: ' . h($percent) . '%"></span></span>';
        echo '<span class="analytics-value">' . h(number_format($value)) . h($unit) . '</span>';
        echo '</div>';
    }
}

function calculatePeriodRoi($item)
{
    if (!$item || (int)($item['count'] ?? 0) === 0) {
        return null;
    }

    $score = (int)($item['score'] ?? 0);
    $spend = calculateNetInvestment($item['spend'] ?? 0, $item['recovery'] ?? 0);

    if ($spend === 0) {
        return $score > 0 ? INF : null;
    }

    return round($score / $spend * 1000, 2);
}

function formatPeriodRoi($item)
{
    $roi = calculatePeriodRoi($item);
    return formatRoiValue($roi);
}

function renderPeriodRoiRows($items)
{
    if (!$items) {
        echo '<p class="empty small">まだ集計できるデータがありません。</p>';
        return;
    }

    foreach ($items as $label => $item) {
        echo '<div class="roi-row">';
        $netSpend = calculateNetInvestment($item['spend'] ?? 0, $item['recovery'] ?? 0);
        echo '<span>' . h($label) . ' / 実質' . h(number_format($netSpend)) . '円 / ' . h((int)$item['count']) . '冊</span>';
        echo '<strong>' . h(formatPeriodRoi($item)) . '</strong>';
        echo '</div>';
    }
}

function renderDailyReadingGraph($items)
{
    if (!$items) {
        echo '<p class="empty small">まだ集計できるデータがありません。</p>';
        return;
    }

    $pageValues = array_column($items, 'pages');
    $bookValues = array_column($items, 'books');
    $maxPages = max($pageValues);
    $maxBooks = max($bookValues);

    echo '<div class="daily-graph" role="img" aria-label="日付ごとの読書ページ数と冊数">';
    foreach ($items as $date => $item) {
        $pages = (int)$item['pages'];
        $books = (int)$item['books'];
        $pageHeight = $maxPages > 0 ? max(4, round($pages / $maxPages * 100)) : 0;
        $bookHeight = $maxBooks > 0 ? max(4, round($books / $maxBooks * 100)) : 0;
        $label = $date === '日付なし' ? $date : date('n/j', strtotime($date));

        echo '<div class="daily-graph-day">';
        echo '<div class="daily-bars">';
        echo '<span class="daily-bar page-bar" style="height: ' . h($pageHeight) . '%"></span>';
        echo '<span class="daily-bar book-bar" style="height: ' . h($bookHeight) . '%"></span>';
        echo '</div>';
        echo '<span class="daily-date">' . h($label) . '</span>';
        echo '<span class="daily-values">' . h(number_format($pages)) . 'p / ' . h($books) . '冊</span>';
        echo '</div>';
    }
    echo '</div>';
    echo '<div class="graph-legend"><span class="page-dot"></span>読書ページ数 <span class="book-dot"></span>読書冊数</div>';
}

function renderReturnReminders($items)
{
    if (!$items) {
        echo '<p class="empty small">返却日が近い本はありません。</p>';
        return;
    }

    foreach ($items as $item) {
        $days = (int)$item['days'];
        if ($days < 0) {
            $status = abs($days) . '日超過';
            $class = 'is-overdue';
        } elseif ($days === 0) {
            $status = '今日返却';
            $class = 'is-today';
        } else {
            $status = 'あと' . $days . '日';
            $class = $days <= 3 ? 'is-soon' : '';
        }

        echo '<div class="reminder-row ' . h($class) . '">';
        echo '<span>' . h($item['title']) . '</span>';
        echo '<strong>' . h($status) . '</strong>';
        echo '<small>' . h($item['date']) . '</small>';
        echo '</div>';
    }
}

function renderInventoryCandidates($items)
{
    if (!$items) {
        echo '<p class="empty small">棚卸し候補はありません。</p>';
        return;
    }

    foreach ($items as $item) {
        echo '<div class="inventory-row">';
        echo '<span>' . h($item['title']) . '</span>';
        echo '<strong>' . h($item['action']) . '</strong>';
        echo '<small>実質 ' . h(number_format((int)$item['net'])) . '円</small>';
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bank - ダッシュボード</title>
    <link rel="stylesheet" href="style.css?v=20260705-2110">
</head>
<body>
    <header>
        <h1>Book Bank</h1>
        <nav>
            <a href="index.php">トップ</a>
            <a href="select.php">本棚</a>
            <a href="analytics.php" class="active" aria-current="page">ダッシュボード</a>
            <a href="log.php">読書レビュー</a>
            <a href="logout.php">ログアウト</a>
        </nav>
    </header>

    <main class="bookshelf-page">
        <section class="view-section analytics-section" aria-labelledby="analytics-title">
            <div class="section-heading"><h2 id="analytics-title">ダッシュボード</h2></div>

            <div class="analytics-summary">
                <div><span>登録冊数</span><strong><?php echo h(count($rows)); ?>冊</strong></div>
                <div><span>読書ページ数</span><strong><?php echo h(number_format($totalPages)); ?>p</strong></div>
                <div><span>読書投資額</span><strong><?php echo h(number_format($totalSpend)); ?>円</strong></div>
                <div><span>回収額</span><strong><?php echo h(number_format($totalRecovery)); ?>円</strong></div>
                <div><span>実質投資額</span><strong><?php echo h(number_format($totalNetInvestment)); ?>円</strong></div>
                <div><span>高いROI本</span><strong class="dashboard-book-title"><?php echo empty($topRoiBooks) ? '-' : h($topRoiBooks[0]['title']); ?></strong></div>
                <div><span>平均知識ROI</span><strong><?php echo h(formatRoiValue($averageRoi)); ?></strong></div>
                <div><span>今月の知識ROI</span><strong><?php echo h(formatPeriodRoi($currentMonthRoi)); ?></strong></div>
                <div><span>今年の知識ROI</span><strong><?php echo h(formatPeriodRoi($currentYearRoi)); ?></strong></div>
            </div>

            <div class="roi-guide" aria-label="知識ROIの目安">
                <strong>知識ROIの目安</strong>
                <span>4以上: 高い</span>
                <span>2以上: 良い</span>
                <span>1以上: ふつう</span>
                <span>1未満: 低い</span>
            </div>

            <div class="analytics-grid">
                <section class="analytics-card analytics-card-wide"><h3>日付ごとの読書グラフ</h3><?php renderDailyReadingGraph($dailyReading); ?></section>
                <section class="analytics-card"><h3>著者別</h3><?php renderBarChart($authorCount, '冊'); ?></section>
                <section class="analytics-card"><h3>テーマ別</h3><?php renderBarChart($themeCount, '冊'); ?></section>
                <section class="analytics-card"><h3>入口別</h3><?php renderBarChart($methodCount, '冊'); ?></section>
                <section class="analytics-card"><h3>出口別</h3><?php renderBarChart($exitActionCount, '冊'); ?></section>
                <section class="analytics-card"><h3>深める / 広げる</h3><?php renderBarChart($axisCount, '冊'); ?></section>
                <section class="analytics-card"><h3>月別の読書投資額</h3><?php renderBarChart($monthlySpend, '円'); ?></section>
                <section class="analytics-card"><h3>月別の知識ROI</h3><?php renderPeriodRoiRows($monthlyRoi); ?></section>
                <section class="analytics-card"><h3>年別の知識ROI</h3><?php renderPeriodRoiRows($yearlyRoi); ?></section>
                <section class="analytics-card"><h3>返却リマインド</h3><?php renderReturnReminders($returnReminders); ?></section>
                <section class="analytics-card"><h3>年度末の棚卸し候補</h3><?php renderInventoryCandidates($inventoryCandidates); ?></section>
                <section class="analytics-card">
                    <h3>高いROI本</h3>
                    <?php if (!$topRoiBooks): ?>
                        <p class="empty small">まだ集計できるデータがありません。</p>
                    <?php else: ?>
                        <?php foreach ($topRoiBooks as $book): ?>
                            <div class="roi-row"><span><?php echo h($book['title']); ?></span><strong><?php echo h(formatRoiValue($book['roi'] === null ? INF : $book['roi'])); ?></strong></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
