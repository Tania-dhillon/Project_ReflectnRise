<?php
// ------------------------------------------------------------
// Insights page
// ------------------------------------------------------------
// Shows wellbeing analytics using data from daily check-ins,
// reflection entries and journal entries.
// Includes 7 / 14 / 30 day filters.
// ------------------------------------------------------------

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/dashboard_header.php';

$userId = (int)$_SESSION['user_id'];
$range = (int)($_GET['range'] ?? 14);
if (!in_array($range, [7, 14, 30], true)) {
    $range = 14;
}

$startDate = date('Y-m-d 00:00:00', strtotime('-' . ($range - 1) . ' days'));
$endDate = date('Y-m-d 23:59:59');

// Daily check-ins for selected range
$checkinStmt = $pdo->prepare('
    SELECT mood_label, mood_rating, energy_level, stress_level, sleep_quality, influences, created_at
    FROM daily_checkins
    WHERE user_id = ? AND created_at BETWEEN ? AND ?
    ORDER BY created_at ASC
');
$checkinStmt->execute([$userId, $startDate, $endDate]);
$checkins = $checkinStmt->fetchAll();

// Journal + reflections counts in selected period
$journalCountStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM journal_entries WHERE user_id = ? AND created_at BETWEEN ? AND ?');
$journalCountStmt->execute([$userId, $startDate, $endDate]);
$journalCount = (int)($journalCountStmt->fetch()['total'] ?? 0);

$reflectionCountStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM reflection_entries WHERE user_id = ? AND created_at BETWEEN ? AND ?');
$reflectionCountStmt->execute([$userId, $startDate, $endDate]);
$reflectionCount = (int)($reflectionCountStmt->fetch()['total'] ?? 0);

// Build date labels
$dateLabels = [];
$moodSeries = [];
$energySeries = [];
for ($i = $range - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dateLabels[] = date('M j', strtotime($date));
    $moodSeries[$date] = null;
    $energySeries[$date] = null;
}

$moodCounts = ['Awful' => 0, 'Bad' => 0, 'Okay' => 0, 'Good' => 0, 'Great' => 0];
$totalMood = 0;
$totalEnergy = 0;
$totalStress = 0;
$totalSleep = 0;
$bestDayDate = null;
$bestDayScore = -1;
$influenceCounts = [];

foreach ($checkins as $row) {
    $d = date('Y-m-d', strtotime($row['created_at']));
    $moodSeries[$d] = (int)$row['mood_rating'];
    $energySeries[$d] = (int)$row['energy_level'];

    if (isset($moodCounts[$row['mood_label']])) {
        $moodCounts[$row['mood_label']]++;
    }

    $totalMood += (int)$row['mood_rating'];
    $totalEnergy += (int)$row['energy_level'];
    $totalStress += (int)$row['stress_level'];
    $totalSleep += (int)$row['sleep_quality'];

    $dayScore = (int)$row['mood_rating'] + (int)$row['energy_level'] + (int)$row['sleep_quality'] - (int)$row['stress_level'];
    if ($dayScore > $bestDayScore) {
        $bestDayScore = $dayScore;
        $bestDayDate = $d;
    }

    if (!empty($row['influences'])) {
        $decoded = json_decode($row['influences'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $inf) {
                $inf = trim((string)$inf);
                if ($inf === '') continue;
                $influenceCounts[$inf] = ($influenceCounts[$inf] ?? 0) + 1;
            }
        }
    }
}

// Fill forward previous known value for charts, fallback to neutral values
$lastMood = 3;
$lastEnergy = 3;
$moodChartValues = [];
$energyChartValues = [];
foreach (array_keys($moodSeries) as $dateKey) {
    if ($moodSeries[$dateKey] !== null) {
        $lastMood = $moodSeries[$dateKey];
    }
    if ($energySeries[$dateKey] !== null) {
        $lastEnergy = $energySeries[$dateKey];
    }
    $moodChartValues[] = $lastMood;
    $energyChartValues[] = $lastEnergy;
}

$totalCheckins = count($checkins);
$avgMood = $totalCheckins ? round($totalMood / $totalCheckins, 1) : 0;
$avgEnergy = $totalCheckins ? round($totalEnergy / $totalCheckins, 1) : 0;
$avgStress = $totalCheckins ? round($totalStress / $totalCheckins, 1) : 0;
$avgSleep = $totalCheckins ? round($totalSleep / $totalCheckins, 1) : 0;
$lowStressScore = $totalCheckins ? round(6 - ($totalStress / $totalCheckins), 1) : 0;

arsort($influenceCounts);
$topInfluences = array_slice($influenceCounts, 0, 6, true);
if (empty($topInfluences)) {
    $topInfluences = ['Academics' => 0, 'Exercise' => 0, 'Social' => 0, 'Work' => 0, 'Finances' => 0, 'Family' => 0];
}

$moodPieLabels = [];
$moodPieValues = [];
foreach ($moodCounts as $label => $count) {
    if ($count > 0) {
        $moodPieLabels[] = $label;
        $moodPieValues[] = $count;
    }
}
if (empty($moodPieLabels)) {
    $moodPieLabels = ['Okay'];
    $moodPieValues = [1];
}

$bestDayDisplay = $bestDayDate ? date('M j', strtotime($bestDayDate)) : '-';
?>

<div class="insights-wrap">
    <div class="insights-header-row">
        <div>
            <h1>Insights</h1>
            <p>Patterns and trends from your wellbeing data.</p>
        </div>

        <div class="insights-range-tabs">
            <a href="insights.php?range=7" class="insights-range-tab <?= $range === 7 ? 'active' : ''; ?>">7d</a>
            <a href="insights.php?range=14" class="insights-range-tab <?= $range === 14 ? 'active' : ''; ?>">14d</a>
            <a href="insights.php?range=30" class="insights-range-tab <?= $range === 30 ? 'active' : ''; ?>">30d</a>
        </div>
    </div>

    <div class="insights-card insights-top-card mb-4">
        <div class="insights-card-title"><i class="bi bi-graph-up-arrow"></i> Mood &amp; Energy Over Time</div>
        <div class="insights-chart-box insights-line-box">
            <canvas id="insightsLineChart"></canvas>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="insights-card insights-small-card">
                <div class="insights-card-title">Wellbeing Overview</div>
                <div class="insights-chart-box">
                    <canvas id="insightsRadarChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="insights-card insights-small-card">
                <div class="insights-card-title">Mood Distribution</div>
                <div class="insights-chart-box">
                    <canvas id="insightsPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="insights-card insights-small-card">
                <div class="insights-card-title">What's Influencing You</div>
                <div class="insights-chart-box">
                    <canvas id="insightsBarChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="insights-card insights-small-card">
                <div class="insights-card-title"><i class="bi bi-calendar2-check"></i> Summary</div>
                <div class="insights-summary-grid">
                    <div><span>Check-ins this period</span><strong><?= $totalCheckins; ?></strong></div>
                    <div><span>Journal entries</span><strong><?= $journalCount + $reflectionCount; ?></strong></div>
                    <div><span>Average mood</span><strong><?= number_format($avgMood, 1); ?>/5</strong></div>
                    <div><span>Average stress</span><strong><?= number_format($avgStress, 1); ?>/5</strong></div>
                    <div><span>Best day this period</span><strong><?= e($bestDayDisplay); ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.insightsLineLabels = <?= json_encode($dateLabels); ?>;
window.insightsMoodValues = <?= json_encode($moodChartValues); ?>;
window.insightsEnergyValues = <?= json_encode($energyChartValues); ?>;
window.insightsRadarValues = <?= json_encode([$avgMood, $avgEnergy, $avgSleep, $lowStressScore]); ?>;
window.insightsPieLabels = <?= json_encode($moodPieLabels); ?>;
window.insightsPieValues = <?= json_encode($moodPieValues); ?>;
window.insightsBarLabels = <?= json_encode(array_keys($topInfluences)); ?>;
window.insightsBarValues = <?= json_encode(array_values($topInfluences)); ?>;
</script>

<?php require_once __DIR__ . '/includes/dashboard_footer.php'; ?>
