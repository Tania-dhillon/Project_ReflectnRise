<?php

// Goals page
// ------------------------------------------------------------
// Lets users create, edit, delete and view wellbeing goals.
// Goals can have category, status, target value, current value,
// unit and due date. 
//Search bar added


require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/dashboard_header.php';

$userId = (int)$_SESSION['user_id'];
$success = '';
$error = '';

$goalCategories = [
    'Mental Health' => '🧠',
    'Sleep' => '😴',
    'Exercise' => '🏃',
    'Social' => '👥',
    'Academics' => '📚',
    'Nutrition' => '🥗',
    'Mindfulness' => '🌿',
    'Other' => '⭐',
];

$goalStatuses = ['Active', 'Paused', 'Completed'];

// ------------------------------------------------------------
// Search / filter inputs
// ------------------------------------------------------------
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? 'All Categories');

// Create goal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'create_goal') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'Mental Health');
    $status = trim($_POST['status'] ?? 'Active');
    $targetValue = (int)($_POST['target_value'] ?? 0);
    $currentValue = (int)($_POST['current_value'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($title === '') {
        $error = 'Please enter a goal title.';
    } elseif ($targetValue < 1) {
        $error = 'Please enter a valid target value.';
    } elseif (!isset($goalCategories[$category])) {
        $error = 'Please choose a valid category.';
    } elseif (!in_array($status, $goalStatuses, true)) {
        $error = 'Please choose a valid status.';
    } else {
        $insert = $pdo->prepare('
            INSERT INTO goals (user_id, title, description, category, status, target_value, current_value, unit, due_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $insert->execute([
            $userId,
            $title,
            $description !== '' ? $description : null,
            $category,
            $status,
            $targetValue,
            max(0, min($currentValue, $targetValue)),
            $unit !== '' ? $unit : 'days',
            $dueDate !== '' ? $dueDate : null
        ]);
        $success = 'Goal created successfully.';
    }
}

// Edit goal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'edit_goal') {
    $goalId = (int)($_POST['goal_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'Mental Health');
    $status = trim($_POST['status'] ?? 'Active');
    $targetValue = (int)($_POST['target_value'] ?? 0);
    $currentValue = (int)($_POST['current_value'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($goalId < 1) {
        $error = 'Invalid goal selected.';
    } elseif ($title === '') {
        $error = 'Please enter a goal title.';
    } elseif ($targetValue < 1) {
        $error = 'Please enter a valid target value.';
    } else {
        $update = $pdo->prepare('
            UPDATE goals
            SET title = ?, description = ?, category = ?, status = ?, target_value = ?, current_value = ?, unit = ?, due_date = ?
            WHERE id = ? AND user_id = ?
        ');
        $update->execute([
            $title,
            $description !== '' ? $description : null,
            $category,
            $status,
            $targetValue,
            max(0, min($currentValue, $targetValue)),
            $unit !== '' ? $unit : 'days',
            $dueDate !== '' ? $dueDate : null,
            $goalId,
            $userId
        ]);
        $success = 'Goal updated successfully.';
    }
}

// Delete goal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'delete_goal') {
    $goalId = (int)($_POST['goal_id'] ?? 0);
    if ($goalId > 0) {
        $delete = $pdo->prepare('DELETE FROM goals WHERE id = ? AND user_id = ?');
        $delete->execute([$goalId, $userId]);
        $success = 'Goal deleted successfully.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM goals WHERE user_id = ? ORDER BY status = "Completed", created_at DESC');
$stmt->execute([$userId]);
$allGoals = $stmt->fetchAll();

// ------------------------------------------------------------
// Apply search + category filter
// ------------------------------------------------------------
$filteredGoals = array_values(array_filter($allGoals, function ($goal) use ($search, $categoryFilter) {
    $matchesCategory = $categoryFilter === 'All Categories' || strcasecmp($goal['category'], $categoryFilter) === 0;

    if ($search === '') {
        return $matchesCategory;
    }

    $needle = mb_strtolower($search);
    $haystack = mb_strtolower(
        ($goal['title'] ?? '') . ' ' .
        ($goal['description'] ?? '') . ' ' .
        ($goal['category'] ?? '') . ' ' .
        ($goal['status'] ?? '') . ' ' .
        ($goal['unit'] ?? '')
    );

    return $matchesCategory && str_contains($haystack, $needle);
}));

$activeGoals = array_values(array_filter($filteredGoals, fn($g) => $g['status'] !== 'Completed'));
$completedGoals = array_values(array_filter($filteredGoals, fn($g) => $g['status'] === 'Completed'));
?>

<div class="goals-wrap">
    <div class="goals-header-row">
        <div>
            <h1>Wellbeing Goals</h1>
            <p>Set intentions and track your long-term growth.</p>
        </div>

        <button class="btn btn-mindful goals-new-btn" type="button" data-bs-toggle="modal" data-bs-target="#newGoalModal">
            <i class="bi bi-plus-lg"></i> New Goal
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error); ?></div>
    <?php endif; ?>

    <!-- Search + category filter -->
    <form method="GET" action="" class="journal-filter-row mb-4">
        <div class="journal-search-wrap">
            <i class="bi bi-search"></i>
            <input
                type="text"
                name="search"
                class="form-control journal-search-input"
                placeholder="Search goals..."
                value="<?= e($search); ?>"
            >
        </div>

        <select name="category" class="form-select journal-category-select" onchange="this.form.submit()">
            <option value="All Categories" <?= $categoryFilter === 'All Categories' ? 'selected' : ''; ?>>All Categories</option>
            <?php foreach ($goalCategories as $cat => $emoji): ?>
                <option value="<?= e($cat); ?>" <?= $categoryFilter === $cat ? 'selected' : ''; ?>>
                    <?= e($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($activeGoals): ?>
        <div class="goals-section-label">ACTIVE</div>
        <div class="goals-list">
            <?php foreach ($activeGoals as $goal): ?>
                <?php
                $progress = $goal['target_value'] > 0 ? min(100, round(($goal['current_value'] / $goal['target_value']) * 100)) : 0;
                $icon = $goalCategories[$goal['category']] ?? '⭐';
                $goalModalId = 'editGoalModal' . (int)$goal['id'];
                ?>
                <div class="goal-card">
                    <div class="goal-card-top">
                        <div class="goal-card-left">
                            <div class="goal-icon"><?= e($icon); ?></div>
                            <div>
                                <div class="goal-title-row">
                                    <h3><?= e($goal['title']); ?></h3>
                                    <?php if ($goal['status'] === 'Completed'): ?>
                                        <span class="goal-complete-check"><i class="bi bi-check-circle"></i></span>
                                    <?php endif; ?>
                                </div>
                                <div class="goal-meta-row">
                                    <span class="goal-category-pill"><?= e($icon . ' ' . $goal['category']); ?></span>
                                    <?php if (!empty($goal['due_date'])): ?>
                                        <span>Due <?= date('M j, Y', strtotime($goal['due_date'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="goal-card-actions">
                            <button type="button" class="goal-action-btn" data-bs-toggle="modal" data-bs-target="#<?= e($goalModalId); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="" onsubmit="return confirm('Delete this goal?');">
                                <input type="hidden" name="form_type" value="delete_goal">
                                <input type="hidden" name="goal_id" value="<?= (int)$goal['id']; ?>">
                                <button type="submit" class="goal-action-btn"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($goal['description'])): ?>
                        <div class="goal-description"><?= e($goal['description']); ?></div>
                    <?php endif; ?>

                    <div class="goal-progress-label-row">
                        <span>Progress</span>
                        <span><?= (int)$goal['current_value']; ?> <?= e($goal['unit']); ?> / <?= (int)$goal['target_value']; ?> <?= e($goal['unit']); ?></span>
                    </div>

                    <div class="goal-progress-bar">
                        <div class="goal-progress-fill" style="width: <?= $progress; ?>%;"></div>
                    </div>
                    <div class="goal-progress-note"><?= $progress; ?>% complete</div>
                </div>

                <div class="modal fade goals-modal" id="<?= e($goalModalId); ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content goals-modal-content">
                            <div class="goals-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></div>
                            <h2>Edit Goal</h2>

                            <form method="POST" action="">
                                <input type="hidden" name="form_type" value="edit_goal">
                                <input type="hidden" name="goal_id" value="<?= (int)$goal['id']; ?>">

                                <div class="mb-3">
                                    <input type="text" name="title" class="form-control goals-input" value="<?= e($goal['title']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <textarea name="description" class="form-control goals-textarea"><?= e($goal['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="goals-grid-two mb-3">
                                    <select name="category" class="form-select goals-input">
                                        <?php foreach ($goalCategories as $cat => $emoji): ?>
                                            <option value="<?= e($cat); ?>" <?= $goal['category'] === $cat ? 'selected' : ''; ?>>
                                                <?= e($emoji . ' ' . $cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="status" class="form-select goals-input">
                                        <?php foreach ($goalStatuses as $status): ?>
                                            <option value="<?= e($status); ?>" <?= $goal['status'] === $status ? 'selected' : ''; ?>>
                                                <?= e($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="goals-grid-three mb-3">
                                    <input type="number" name="target_value" class="form-control goals-input" value="<?= (int)$goal['target_value']; ?>" min="1" placeholder="Target *" required>
                                    <input type="number" name="current_value" class="form-control goals-input" value="<?= (int)$goal['current_value']; ?>" min="0" placeholder="Current">
                                    <input type="text" name="unit" class="form-control goals-input" value="<?= e($goal['unit']); ?>" placeholder="Unit (e.g. days)">
                                </div>

                                <div class="mb-4">
                                    <input type="date" name="due_date" class="form-control goals-input" value="<?= !empty($goal['due_date']) ? date('Y-m-d', strtotime($goal['due_date'])) : ''; ?>">
                                </div>

                                <div class="goals-modal-actions">
                                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-mindful">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="goals-section-label">COMPLETED 🎉</div>
    <div class="goals-list">
        <?php if ($completedGoals): ?>
            <?php foreach ($completedGoals as $goal): ?>
                <?php
                $progress = $goal['target_value'] > 0 ? min(100, round(($goal['current_value'] / $goal['target_value']) * 100)) : 100;
                $icon = $goalCategories[$goal['category']] ?? '⭐';
                $goalModalId = 'editGoalModalCompleted' . (int)$goal['id'];
                ?>
                <div class="goal-card">
                    <div class="goal-card-top">
                        <div class="goal-card-left">
                            <div class="goal-icon"><?= e($icon); ?></div>
                            <div>
                                <div class="goal-title-row">
                                    <h3><?= e($goal['title']); ?></h3>
                                    <span class="goal-complete-check"><i class="bi bi-check-circle"></i></span>
                                </div>
                                <div class="goal-meta-row">
                                    <span class="goal-category-pill"><?= e($icon . ' ' . $goal['category']); ?></span>
                                    <?php if (!empty($goal['due_date'])): ?>
                                        <span>Due <?= date('M j, Y', strtotime($goal['due_date'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="goal-card-actions">
                            <button type="button" class="goal-action-btn" data-bs-toggle="modal" data-bs-target="#<?= e($goalModalId); ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="" onsubmit="return confirm('Delete this goal?');">
                                <input type="hidden" name="form_type" value="delete_goal">
                                <input type="hidden" name="goal_id" value="<?= (int)$goal['id']; ?>">
                                <button type="submit" class="goal-action-btn"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <?php if (!empty($goal['description'])): ?>
                        <div class="goal-description"><?= e($goal['description']); ?></div>
                    <?php endif; ?>

                    <div class="goal-progress-label-row">
                        <span>Progress</span>
                        <span><?= (int)$goal['current_value']; ?> <?= e($goal['unit']); ?> / <?= (int)$goal['target_value']; ?> <?= e($goal['unit']); ?></span>
                    </div>

                    <div class="goal-progress-bar">
                        <div class="goal-progress-fill" style="width: <?= $progress; ?>%;"></div>
                    </div>
                    <div class="goal-progress-note"><?= $progress; ?>% complete</div>
                </div>

                <div class="modal fade goals-modal" id="<?= e($goalModalId); ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content goals-modal-content">
                            <div class="goals-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></div>
                            <h2>Edit Goal</h2>

                            <form method="POST" action="">
                                <input type="hidden" name="form_type" value="edit_goal">
                                <input type="hidden" name="goal_id" value="<?= (int)$goal['id']; ?>">

                                <div class="mb-3">
                                    <input type="text" name="title" class="form-control goals-input" value="<?= e($goal['title']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <textarea name="description" class="form-control goals-textarea"><?= e($goal['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="goals-grid-two mb-3">
                                    <select name="category" class="form-select goals-input">
                                        <?php foreach ($goalCategories as $cat => $emoji): ?>
                                            <option value="<?= e($cat); ?>" <?= $goal['category'] === $cat ? 'selected' : ''; ?>>
                                                <?= e($emoji . ' ' . $cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="status" class="form-select goals-input">
                                        <?php foreach ($goalStatuses as $status): ?>
                                            <option value="<?= e($status); ?>" <?= $goal['status'] === $status ? 'selected' : ''; ?>>
                                                <?= e($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="goals-grid-three mb-3">
                                    <input type="number" name="target_value" class="form-control goals-input" value="<?= (int)$goal['target_value']; ?>" min="1" placeholder="Target *" required>
                                    <input type="number" name="current_value" class="form-control goals-input" value="<?= (int)$goal['current_value']; ?>" min="0" placeholder="Current">
                                    <input type="text" name="unit" class="form-control goals-input" value="<?= e($goal['unit']); ?>" placeholder="Unit (e.g. days)">
                                </div>

                                <div class="mb-4">
                                    <input type="date" name="due_date" class="form-control goals-input" value="<?= !empty($goal['due_date']) ? date('Y-m-d', strtotime($goal['due_date'])) : ''; ?>">
                                </div>

                                <div class="goals-modal-actions">
                                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-mindful">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="goal-empty-card">No completed goals yet.</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade goals-modal" id="newGoalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content goals-modal-content">
            <div class="goals-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></div>
            <h2>New Wellbeing Goal</h2>

            <form method="POST" action="">
                <input type="hidden" name="form_type" value="create_goal">

                <div class="mb-3">
                    <input type="text" name="title" class="form-control goals-input" placeholder="Goal title *" required>
                </div>

                <div class="mb-3">
                    <textarea name="description" class="form-control goals-textarea" placeholder="Description (optional)"></textarea>
                </div>

                <div class="goals-grid-two mb-3">
                    <select name="category" class="form-select goals-input">
                        <?php foreach ($goalCategories as $cat => $emoji): ?>
                            <option value="<?= e($cat); ?>"><?= e($emoji . ' ' . $cat); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status" class="form-select goals-input">
                        <?php foreach ($goalStatuses as $status): ?>
                            <option value="<?= e($status); ?>" <?= $status === 'Active' ? 'selected' : ''; ?>><?= e($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="goals-grid-three mb-3">
                    <input type="number" name="target_value" class="form-control goals-input" placeholder="Target *" min="1" required>
                    <input type="number" name="current_value" class="form-control goals-input" value="0" min="0">
                    <input type="text" name="unit" class="form-control goals-input" placeholder="Unit (e.g. days)">
                </div>

                <div class="mb-4">
                    <input type="date" name="due_date" class="form-control goals-input">
                </div>

                <div class="goals-modal-actions">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-mindful">Create Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/dashboard_footer.php'; ?>