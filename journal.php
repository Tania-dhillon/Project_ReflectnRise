<?php
// Journal page
// ------------------------------------------------------------
// Shows journal cards with search + category filter.



require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/dashboard_header.php';

$userId = (int)$_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? 'All Categories');
$success = '';
$error = '';

// ----------------------------------------------------
// new journal entry submission
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'new_journal_entry') {
    $title = trim($_POST['title'] ?? '');
    $entryCategory = trim($_POST['entry_category'] ?? 'Free Write');
    $content = trim($_POST['content'] ?? '');

    if ($content === '') {
        $error = 'Please write something before saving your journal entry.';
    } else {
        $insert = $pdo->prepare('
            INSERT INTO journal_entries (user_id, title, category, content)
            VALUES (?, ?, ?, ?)
        ');
        $insert->execute([
            $userId,
            $title !== '' ? $title : null,
            $entryCategory !== '' ? $entryCategory : 'Free Write',
            $content
        ]);
        $success = 'Your journal entry has been saved.';
    }
}

// -------------------------------------
// delete action for entry
// -------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'delete_journal_entry') {
    $entryId = (int)($_POST['entry_id'] ?? 0);
    $entrySource = trim($_POST['entry_source'] ?? '');

    if ($entryId > 0) {
        if ($entrySource === 'journal') {
            $delete = $pdo->prepare('DELETE FROM journal_entries WHERE id = ? AND user_id = ?');
            $delete->execute([$entryId, $userId]);
            $success = 'Journal entry deleted successfully.';
        } elseif ($entrySource === 'reflection') {
            $delete = $pdo->prepare('DELETE FROM reflection_entries WHERE id = ? AND user_id = ?');
            $delete->execute([$entryId, $userId]);
            $success = 'Reflection entry deleted successfully.';
        }
    }
}

// ------------------------------------------------------
// Fetches journal and reflection entries
// ------------------------------------------------------
$journalStmt = $pdo->prepare('
    SELECT
        id,
        "journal" AS source_type,
        COALESCE(title, "Untitled Entry") AS title,
        category,
        content AS body_text,
        NULL AS prompt_text,
        created_at
    FROM journal_entries
    WHERE user_id = ?
');
$journalStmt->execute([$userId]);
$journalEntries = $journalStmt->fetchAll();

$reflectionStmt = $pdo->prepare('
    SELECT
        id,
        "reflection" AS source_type,
        CASE
            WHEN CHAR_LENGTH(prompt_text) > 65 THEN CONCAT(LEFT(prompt_text, 65), "...")
            ELSE prompt_text
        END AS title,
        category,
        response_text AS body_text,
        prompt_text,
        created_at
    FROM reflection_entries
    WHERE user_id = ?
');
$reflectionStmt->execute([$userId]);
$reflectionEntries = $reflectionStmt->fetchAll();

$entries = array_merge($journalEntries, $reflectionEntries);


$filteredEntries = array_values(array_filter($entries, function ($entry) use ($search, $categoryFilter) {
    $matchesCategory = $categoryFilter === 'All Categories' || strcasecmp($entry['category'], $categoryFilter) === 0;

    if ($search === '') {
        return $matchesCategory;
    }

    $needle = mb_strtolower($search);
    $haystack = mb_strtolower(
        ($entry['title'] ?? '') . ' ' .
        ($entry['body_text'] ?? '') . ' ' .
        ($entry['prompt_text'] ?? '') . ' ' .
        ($entry['category'] ?? '')
    );

    return $matchesCategory && str_contains($haystack, $needle);
}));

// Sorts newest first
usort($filteredEntries, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

// Builds category list 
$categoryOptions = ['All Categories', 'Free Write', 'Reflection', 'Gratitude', 'Goals', 'Stress Relief', 'Self Care', 'Motivation', 'Relationships', 'Work', 'Academics', 'Sleep', 'Health', 'Personal Growth'];
foreach ($entries as $entry) {
    if (!in_array($entry['category'], $categoryOptions, true)) {
        $categoryOptions[] = $entry['category'];
    }
}
sort($categoryOptions);
if (($idx = array_search('All Categories', $categoryOptions, true)) !== false) {
    unset($categoryOptions[$idx]);
    array_unshift($categoryOptions, 'All Categories');
}
if (($idx = array_search('Free Write', $categoryOptions, true)) !== false) {
    unset($categoryOptions[$idx]);
    array_splice($categoryOptions, 1, 0, ['Free Write']);
}
?>

<div class="journal-wrap">
    <div class="journal-header-row">
        <div>
            <h1>Journal</h1>
            <p>Your personal space for thoughts and reflections.</p>
        </div>

        <button class="btn btn-mindful journal-new-btn" type="button" data-bs-toggle="modal" data-bs-target="#newEntryModal">
            <i class="bi bi-plus-lg"></i> New Entry
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error); ?></div>
    <?php endif; ?>

    <form method="GET" action="" class="journal-filter-row">
        <div class="journal-search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="form-control journal-search-input" placeholder="Search entries..." value="<?= e($search); ?>">
        </div>

        <select name="category" class="form-select journal-category-select" onchange="this.form.submit()">
            <?php foreach ($categoryOptions as $option): ?>
                <option value="<?= e($option); ?>" <?= $categoryFilter === $option ? 'selected' : ''; ?>>
                    <?= e($option); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($search !== '' || $categoryFilter !== 'All Categories'): ?>
            <noscript><button type="submit" class="btn btn-soft">Filter</button></noscript>
        <?php endif; ?>
    </form>

    <div class="row g-4">
        <?php if ($filteredEntries): ?>
            <?php foreach ($filteredEntries as $entry): ?>
                <?php
                $modalId = 'entryModal' . $entry['source_type'] . $entry['id'];
                $pillLabel = $entry['source_type'] === 'reflection' ? 'REFLECTION' : strtoupper($entry['category']);
                ?>
                <div class="col-lg-6">
                    <div class="journal-card" data-bs-toggle="modal" data-bs-target="#<?= e($modalId); ?>">
                        <div class="journal-card-top">
                            <span class="journal-card-pill"><?= e($pillLabel); ?></span>
                            <span class="journal-card-date"><?= date('M j, Y', strtotime($entry['created_at'])); ?></span>
                        </div>

                        <h3 class="journal-card-title"><?= e($entry['title']); ?></h3>
                        <p class="journal-card-body">
                            <?= e(mb_strlen($entry['body_text']) > 140 ? mb_substr($entry['body_text'], 0, 140) . '...' : $entry['body_text']); ?>
                        </p>

                        <?php if (!empty($entry['prompt_text'])): ?>
                            <div class="journal-card-prompt">
                                Prompt: "<?= e(mb_strlen($entry['prompt_text']) > 72 ? mb_substr($entry['prompt_text'], 0, 72) . '...' : $entry['prompt_text']); ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Entry View -->
                <div class="modal fade journal-modal" id="<?= e($modalId); ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content journal-modal-content">
                            <div class="journal-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></div>

                            <div class="journal-modal-top">
                                <span class="journal-card-pill"><?= e($pillLabel); ?></span>
                                <span class="journal-card-date"><?= date('F j, Y - g:i A', strtotime($entry['created_at'])); ?></span>
                            </div>

                            <h2 class="journal-modal-title"><?= e($entry['source_type'] === 'reflection' && !empty($entry['prompt_text']) ? $entry['prompt_text'] : $entry['title']); ?></h2>

                            <?php if (!empty($entry['prompt_text'])): ?>
                                <div class="journal-modal-prompt">"<?= e($entry['prompt_text']); ?>"</div>
                            <?php endif; ?>

                            <div class="journal-modal-bodytext"><?= nl2br(e($entry['body_text'])); ?></div>

                            <form method="POST" action="" class="text-end mt-4">
                                <input type="hidden" name="form_type" value="delete_journal_entry">
                                <input type="hidden" name="entry_id" value="<?= (int)$entry['id']; ?>">
                                <input type="hidden" name="entry_source" value="<?= e($entry['source_type']); ?>">
                                <button type="submit" class="btn btn-link journal-delete-btn" onclick="return confirm('Are you sure you want to delete this entry?');">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="journal-empty-card">
                    No journal entries found. Create a new entry or save a reflection to see it here.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Journal Entry -->
<div class="modal fade journal-modal" id="newEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content journal-modal-content">
            <div class="journal-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></div>
            <h2 class="journal-modal-heading">New Journal Entry</h2>

            <form method="POST" action="">
                <input type="hidden" name="form_type" value="new_journal_entry">

                <div class="mb-3">
                    <input type="text" name="title" class="form-control journal-modal-input" placeholder="Title (optional)">
                </div>

                <div class="mb-3">
                    <select name="entry_category" class="form-select journal-modal-input">
                        <option value="Free Write">Free Write</option>
                        <option value="Reflection">Reflection</option>
                        <option value="Gratitude">Gratitude</option>
                        <option value="Goals">Goals</option>
                        <option value="Stress Relief">Stress Relief</option>
                        <option value="Self Care">Self Care</option>
                        <option value="Motivation">Motivation</option>
                        <option value="Relationships">Relationships</option>
                        <option value="Work">Work</option>
                        <option value="Academics">Academics</option>
                        <option value="Sleep">Sleep</option>
                        <option value="Health">Health</option>
                        <option value="Personal Growth">Personal Growth</option>
                    </select>
                </div>

                <div class="mb-3">
                    <textarea name="content" class="form-control journal-modal-textarea" placeholder="Write your thoughts..." required></textarea>
                </div>

                <div class="journal-modal-actions">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-mindful">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/dashboard_footer.php'; ?>
