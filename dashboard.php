<?php
// ------------------------------------------------------------
// Dashboard page
// ------------------------------------------------------------
// This page shows the main logged-in dashboard layout with
// summary cards, mood chart, recent activity, and goals area.
// Current data is a mix of live totals and starter demo content.
// ------------------------------------------------------------

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/dashboard_header.php';

// Force dashboard greeting to use first_name from database
$welcomeName = $_SESSION['user_name'] ?? '';
$nameStmt = $pdo->prepare('SELECT first_name, email FROM users WHERE id = ? LIMIT 1');
$nameStmt->execute([$_SESSION['user_id']]);
$nameRow = $nameStmt->fetch();

if ($nameRow) {
    $welcomeName = !empty($nameRow['first_name']) ? $nameRow['first_name'] : preg_replace('/@.*/', '', (string)$nameRow['email']);
    $_SESSION['user_name'] = $welcomeName;
} elseif ($welcomeName === '') {
    $welcomeName = preg_replace('/@.*/', '', ($_SESSION['user_email'] ?? 'User'));
}


// Count total check-ins for current user
$totalCheckinsStmt = $pdo->prepare('SELECT COUNT(*) AS total_checkins FROM daily_checkins WHERE user_id = ?');
$totalCheckinsStmt->execute([$_SESSION['user_id']]);
$totalCheckins = (int)($totalCheckinsStmt->fetch()['total_checkins'] ?? 0);

// Average mood for current user
$avgMoodStmt = $pdo->prepare('SELECT AVG(mood_rating) AS avg_mood FROM daily_checkins WHERE user_id = ?');
$avgMoodStmt->execute([$_SESSION['user_id']]);
$avgMood = $avgMoodStmt->fetch()['avg_mood'] ?? null;
$avgMoodDisplay = $avgMood ? number_format((float)$avgMood, 1) : '0.0';

// This week total
$thisWeekStmt = $pdo->prepare('SELECT COUNT(*) AS total_week FROM daily_checkins WHERE user_id = ? AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)');
$thisWeekStmt->execute([$_SESSION['user_id']]);
$thisWeek = (int)($thisWeekStmt->fetch()['total_week'] ?? 0);

// Simple streak starter logic based on distinct checkin dates
$streakStmt = $pdo->prepare('SELECT DATE(created_at) AS d FROM daily_checkins WHERE user_id = ? GROUP BY DATE(created_at) ORDER BY d DESC');
$streakStmt->execute([$_SESSION['user_id']]);
$dates = $streakStmt->fetchAll(PDO::FETCH_COLUMN);
$streak = 0;
$expected = new DateTime('today');
foreach ($dates as $d) {
    if ($d === $expected->format('Y-m-d')) {
        $streak++;
        $expected->modify('-1 day');
    } elseif ($d === $expected->modify('-1 day')->format('Y-m-d') && $streak === 0) {
        // allow yesterday to start streak if user did not check in yet today
        $streak++;
        $expected->modify('-1 day');
    } else {
        break;
    }
}

// Fetch recent activity
$recentStmt = $pdo->prepare('SELECT mood_label, note, created_at FROM daily_checkins WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$recentStmt->execute([$_SESSION['user_id']]);
$recentActivities = $recentStmt->fetchAll();

// Fetch latest goal for preview panel
$latestGoalStmt = $pdo->prepare('
    SELECT id, title, category, status, target_value, current_value, unit
    FROM goals
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
    LIMIT 1
');
$latestGoalStmt->execute([$_SESSION['user_id']]);
$latestGoal = $latestGoalStmt->fetch();

$completedGoalsCountStmt = $pdo->prepare('SELECT COUNT(*) AS total_completed FROM goals WHERE user_id = ? AND status = "Completed"');
$completedGoalsCountStmt->execute([$_SESSION['user_id']]);
$completedGoalsCount = (int)($completedGoalsCountStmt->fetch()['total_completed'] ?? 0);


// Mood trend data for last 10 entries
$trendStmt = $pdo->prepare('SELECT DATE_FORMAT(created_at, "%b %e") AS label, mood_rating FROM daily_checkins WHERE user_id = ? ORDER BY created_at ASC LIMIT 10');
$trendStmt->execute([$_SESSION['user_id']]);
$trendRows = $trendStmt->fetchAll();

$trendLabels = [];
$trendValues = [];
foreach ($trendRows as $row) {
    $trendLabels[] = $row['label'];
    $trendValues[] = (int)$row['mood_rating'];
}
if (!$trendLabels) {
    $trendLabels = ['Mar 25','Mar 26','Mar 27','Mar 28','Mar 29','Mar 30','Apr 1'];
    $trendValues = [5,3,4,2,5,3,4];
}
?>

<?php
$currentHour = (int) date('G'); // 0–23 server time

if ($currentHour >= 4 && $currentHour < 12) {
    $greetingText = 'Good morning';
    $greetingIcon = 'bi-sunrise';
} elseif ($currentHour >= 12 && $currentHour < 20) {
    $greetingText = 'Good day';
    $greetingIcon = 'bi-sun';
} else {
    $greetingText = 'Good night';
    $greetingIcon = 'bi-moon-stars';
}
?>
<div class="dashboard-wrap">
    <section class="welcome-card">
        <div class="welcome-icon-shape"></div>
        <div class="small greeting-line">
            <i class="bi <?= $greetingIcon ?>"></i> <?= $greetingText ?>
        </div>
        <h1 class="welcome-title"><?= e(ucfirst($welcomeName)); ?></h1>
        <p class="welcome-text">How are you feeling today? Take a moment to check in with yourself.</p>
    </section>

    <div class="mb-4">
        <a href="checkin.php" class="btn btn-mindful btn-lg dashboard-checkin-btn">
            <i class="bi bi-heart me-1"></i> Daily Check-in <i class="bi bi-arrow-right-short"></i>
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-fire"></i></div>
                <div>
                    <div class="stat-value"><?= $streak; ?></div>
                    <div class="stat-label">Day Streak</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon pink"><i class="bi bi-heart"></i></div>
                <div>
                    <div class="stat-value"><?= e($avgMoodDisplay); ?></div>
                    <div class="stat-label">Avg Mood</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-calendar"></i></div>
                <div>
                    <div class="stat-value"><?= $totalCheckins; ?></div>
                    <div class="stat-label">Total Check-ins</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="stat-value"><?= $thisWeek; ?></div>
                    <div class="stat-label">This Week</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="panel-card dashboard-panel">
                <h3 class="panel-title">Mood Trend</h3>
                <div class="chart-box"><canvas id="moodTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card dashboard-panel">
                <h3 class="panel-title">Recent Activity</h3>

                <?php if ($recentActivities): ?>
                    <div class="activity-list">
                        <?php foreach ($recentActivities as $item): ?>
                            <div class="activity-item">
                                <div class="activity-emoji"><?= match($item['mood_label']) {
                                    'Awful' => '😨',
                                    'Bad' => '😔',
                                    'Okay' => '😐',
                                    'Good' => '😊',
                                    'Great' => '🤩',
                                    default => '🙂'
                                }; ?></div>
                                <div class="activity-content">
                                    <div class="activity-meta">
                                        <span class="activity-badge">CHECK-IN</span>
                                        <span><?= date('M j, g:i A', strtotime($item['created_at'])); ?></span>
                                    </div>
                                    <div class="activity-text">Feeling <?= strtolower(e($item['mood_label'])); ?><?= $item['note'] ? ' — ' . e($item['note']) : ''; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state-text">No check-ins yet. Start your first daily check-in to see activity here.</div>
                <?php endif; ?>

                <div class="text-end mt-3">
                    <a href="checkin.php" class="text-success text-decoration-none fw-medium">Start a check-in <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card goals-preview">
        <h3 class="panel-title">My Goals</h3>

        <?php if ($latestGoal): ?>
            <?php $goalProgress = $latestGoal['target_value'] > 0 ? min(100, round(($latestGoal['current_value'] / $latestGoal['target_value']) * 100)) : 0; ?>
            <div class="dashboard-goal-card">
                <div class="dashboard-goal-header">
                    <div>
                        <div class="dashboard-goal-title"><?= e($latestGoal['title']); ?></div>
                        <div class="dashboard-goal-meta">
                            <span class="dashboard-goal-pill"><?= e($latestGoal['category']); ?></span>
                            <span><?= e($latestGoal['status']); ?></span>
                        </div>
                    </div>
                    <div class="dashboard-goal-values"><?= (int)$latestGoal['current_value']; ?> / <?= (int)$latestGoal['target_value']; ?> <?= e($latestGoal['unit']); ?></div>
                </div>

                <div class="dashboard-goal-progress">
                    <div class="dashboard-goal-progress-fill" style="width: <?= $goalProgress; ?>%;"></div>
                </div>

                <div class="dashboard-goal-footer">
                    <span><?= $goalProgress; ?>% complete</span>
                    <span><?= $completedGoalsCount; ?> goal completed<?= $completedGoalsCount === 1 ? '' : 's'; ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="goal-summary-line"><i class="bi bi-check-circle"></i> No goals yet. Create your first goal to see it here.</div>
        <?php endif; ?>

        <div class="text-end mt-3">
            <a href="goals.php" class="text-success text-decoration-none fw-medium">View all goals <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<script>
window.moodTrendLabels = <?= json_encode($trendLabels); ?>;
window.moodTrendValues = <?= json_encode($trendValues); ?>;
</script>

<?php require_once __DIR__ . '/includes/dashboard_footer.php'; ?>
