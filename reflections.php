<?php
// Guided reflections page
// ------------------------------------------------------------
// Users can choose a category, generate a random prompt, write
// a response, and save it into the database

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/dashboard_header.php';

$success = '';
$error = '';
$selectedCategory = trim($_GET['category'] ?? 'All Topics');

// Fetches prompts from database
$promptStmt = $pdo->query('SELECT id, category, prompt_text, difficulty FROM reflection_prompts ORDER BY category, id');
$allPrompts = $promptStmt->fetchAll();

$categories = ['All Topics'];
foreach ($allPrompts as $prompt) {
    if (!in_array($prompt['category'], $categories, true)) {
        $categories[] = $prompt['category'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promptId = (int)($_POST['prompt_id'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $promptText = trim($_POST['prompt_text'] ?? '');
    $responseText = trim($_POST['response_text'] ?? '');

    if ($promptId < 1 || $promptText === '') {
        $error = 'Please generate a prompt first.';
    } elseif ($responseText === '') {
        $error = 'Please write your reflection before saving.';
    } else {
        $save = $pdo->prepare('
            INSERT INTO reflection_entries (user_id, prompt_id, category, prompt_text, response_text)
            VALUES (?, ?, ?, ?, ?)
        ');
        $save->execute([
            $_SESSION['user_id'],
            $promptId,
            $category,
            $promptText,
            $responseText
        ]);
        $success = 'Your reflection has been saved successfully. Find all reflections in your Journal.';
    }
}
?>

<div class="reflections-wrap">
    <div class="reflections-header">
        <h1>Guided Reflections</h1>
        <p>Take a moment to reflect with a thought-provoking prompt.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error); ?></div>
    <?php endif; ?>

    <div class="reflection-category-row">
        <?php foreach ($categories as $category): ?>
            <a href="reflections.php?category=<?= urlencode($category); ?>" class="reflection-chip <?= $selectedCategory === $category ? 'active' : ''; ?>">
                <?= e($category); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="POST" action="" id="reflectionForm" class="reflection-form">
        <input type="hidden" name="prompt_id" id="promptIdInput" value="">
        <input type="hidden" name="category" id="promptCategoryInput" value="<?= e($selectedCategory === 'All Topics' ? '' : $selectedCategory); ?>">
        <input type="hidden" name="prompt_text" id="promptTextInput" value="">

        <div class="reflection-prompt-card" id="reflectionPromptCard">
            <div class="reflection-empty-state" id="reflectionEmptyState">
                <div class="reflection-empty-icon"><i class="bi bi-stars"></i></div>
                <h2>Ready to reflect?</h2>
                <p>Get a thoughtful prompt to guide your self-reflection.<br>There's no right or wrong answer.</p>
                <button type="button" class="btn btn-mindful reflection-action-btn" id="getPromptBtn">
                    <i class="bi bi-magic"></i> Get a Prompt
                </button>
            </div>

            <div class="reflection-prompt-content d-none" id="reflectionPromptContent">
                <div class="reflection-pill-row">
                    <span class="reflection-label-pill" id="promptCategoryBadge">STRESS</span>
                    <span class="reflection-difficulty-pill" id="promptDifficultyBadge">MEDIUM</span>
                </div>
                <div class="reflection-question" id="promptQuestionText"></div>
            </div>
        </div>

        <div class="reflection-response-card d-none" id="reflectionResponseCard">
            <textarea class="form-control reflection-textarea" name="response_text" id="responseText" placeholder="Take your time... write whatever comes to mind."></textarea>
        </div>

        <div class="reflection-footer-actions d-none" id="reflectionFooterActions">
            <button type="button" class="btn btn-link text-decoration-none reflection-new-prompt" id="newPromptBtn">
                <i class="bi bi-arrow-repeat"></i> New Prompt
            </button>
            <button type="submit" class="btn btn-mindful reflection-save-btn">
                <i class="bi bi-send"></i> Save Reflection
            </button>
        </div>
    </form>
</div>

<script>
window.reflectionSelectedCategory = <?= json_encode($selectedCategory); ?>;
window.reflectionPrompts = <?= json_encode($allPrompts, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
</script>

<?php require_once __DIR__ . '/includes/dashboard_footer.php'; ?>
