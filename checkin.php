<?php
// Daily check-in page
// ------------------------------------------------------------
// This page contains the multi-step daily check-in forms
// Step 1: mood selection
// Step 2: energy/stress/sleep sliders
// Step 3: influence tags
// Step 4: free note
// On submit the check-in is stored in MySQL.

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/dashboard_header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $moodLabel = trim($_POST['mood_label'] ?? '');
    $moodRating = (int)($_POST['mood_rating'] ?? 0);
    $energyLevel = (int)($_POST['energy_level'] ?? 3);
    $stressLevel = (int)($_POST['stress_level'] ?? 3);
    $sleepQuality = (int)($_POST['sleep_quality'] ?? 3);
    $influences = $_POST['influences'] ?? [];
    $note = trim($_POST['note'] ?? '');

    if ($moodLabel === '' || $moodRating < 1 || $moodRating > 5) {
        $error = 'Please select how you are feeling before submitting.';
    } else {
        $insert = $pdo->prepare('
            INSERT INTO daily_checkins
            (user_id, mood_label, mood_rating, energy_level, stress_level, sleep_quality, influences, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $insert->execute([
            $_SESSION['user_id'],
            $moodLabel,
            $moodRating,
            $energyLevel,
            $stressLevel,
            $sleepQuality,
            json_encode(array_values($influences)),
            $note
        ]);

        $success = 'Your daily check-in has been saved successfully. Insights have been updated.';
    }
}
?>

<div class="checkin-wrap">
    <div class="checkin-shell">
        <div class="checkin-header">
            <h1>Daily Check-in</h1>
            <p>Take a moment to notice how you're feeling right now.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="checkinForm">
            <input type="hidden" name="mood_label" id="moodLabelInput">
            <input type="hidden" name="mood_rating" id="moodRatingInput" value="0">

            <div class="checkin-progress mb-4">
                <div class="progress-segment active" data-step="1"></div>
                <div class="progress-segment" data-step="2"></div>
                <div class="progress-segment" data-step="3"></div>
                <div class="progress-segment" data-step="4"></div>
            </div>

            <div class="checkin-step active" data-step="1">
                <h2 class="step-title text-center">How are you feeling?</h2>
                <div class="mood-select-grid">
                    <button class="mood-select-card" type="button" data-label="Awful" data-rating="1">😨<span>Awful</span></button>
                    <button class="mood-select-card" type="button" data-label="Bad" data-rating="2">😔<span>Bad</span></button>
                    <button class="mood-select-card" type="button" data-label="Okay" data-rating="3">😐<span>Okay</span></button>
                    <button class="mood-select-card" type="button" data-label="Good" data-rating="4">😊<span>Good</span></button>
                    <button class="mood-select-card" type="button" data-label="Great" data-rating="5">🤩<span>Great</span></button>
                </div>
            </div>

            <div class="checkin-step" data-step="2">
                <div class="step-card">
                    <h2 class="step-title mb-4">A bit more detail...</h2>

                    <div class="slider-row">
                        <div class="slider-label"><i class="bi bi-lightning-charge-fill text-warning"></i> Energy Level</div>
                        <div class="slider-value" id="energyLevelText">Moderate</div>
                    </div>
                    <input type="range" min="1" max="5" value="3" name="energy_level" id="energyLevel" class="form-range custom-range">

                    <div class="slider-row">
                        <div class="slider-label"><i class="bi bi-bandaid text-danger"></i> Stress Level</div>
                        <div class="slider-value" id="stressLevelText">Moderate</div>
                    </div>
                    <input type="range" min="1" max="5" value="3" name="stress_level" id="stressLevel" class="form-range custom-range">

                    <div class="slider-row">
                        <div class="slider-label"><i class="bi bi-moon text-primary"></i> Sleep Quality</div>
                        <div class="slider-value" id="sleepQualityText">Moderate</div>
                    </div>
                    <input type="range" min="1" max="5" value="3" name="sleep_quality" id="sleepQuality" class="form-range custom-range">
                </div>
            </div>

            <div class="checkin-step" data-step="3">
                <h2 class="step-title">What's influencing your mood?</h2>
                <p class="step-subtitle">Select all that apply</p>

                <div class="influence-tags">
                    <?php
                    $tags = ['Academics','Social','Exercise','Sleep','Work','Family','Finances','Health','Hobbies','Relationship'];
                    foreach ($tags as $tag):
                    ?>
                        <label class="tag-pill">
                            <input type="checkbox" name="influences[]" value="<?= e($tag); ?>">
                            <span><?= e($tag); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="checkin-step" data-step="4">
                <h2 class="step-title">Anything else on your mind?</h2>
                <textarea class="form-control checkin-note" name="note" rows="5" placeholder="Write a short note..."></textarea>
            </div>

            <div class="checkin-actions">
                <button type="button" class="btn btn-link text-decoration-none checkin-back" id="prevStepBtn"><i class="bi bi-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-mindful px-4" id="nextStepBtn">Next <i class="bi bi-arrow-right-short"></i></button>
                <button type="submit" class="btn btn-mindful px-4 d-none" id="submitStepBtn">Submit</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/dashboard_footer.php'; ?>
