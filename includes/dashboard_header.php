<?php
// dashboard header
// ------------------------------------------------------------
// This loads auth protection, Bootstrap, icons and dashboard style.



require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
requireLogin();

$userEmail = $_SESSION['user_email'] ?? 'User';
$userName = $_SESSION['user_name'] ?? '';

if ($userName === '' && isset($_SESSION['user_id'])) {
    $nameStmt = $pdo->prepare('SELECT first_name, email FROM users WHERE id = ? LIMIT 1');
    $nameStmt->execute([$_SESSION['user_id']]);
    $nameRow = $nameStmt->fetch();

    if ($nameRow) {
        $userName = !empty($nameRow['first_name']) ? $nameRow['first_name'] : preg_replace('/@.*/', '', (string)$nameRow['email']);
        $_SESSION['user_name'] = $userName;
        if (!empty($nameRow['email'])) {
            $_SESSION['user_email'] = $nameRow['email'];
            $userEmail = $nameRow['email'];
        }
    }
}

if ($userName === '') {
    $userName = preg_replace('/@.*/', '', $userEmail);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reflect &amp; Rise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">
<div class="app-layout">
    <aside class="app-sidebar">
        <div>
            <div class="sidebar-brand">
                <div class="sidebar-logo"><i class="bi bi-book"></i></div>
                <div>
                    <div class="sidebar-brand-title">Reflect &amp; Rise</div>
                    <div class="sidebar-brand-subtitle">STUDENT WELLBEING</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-grid"></i> Dashboard
                </a>
                <a href="checkin.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'checkin.php' ? 'active' : ''; ?>">
                    <i class="bi bi-heart"></i> Check In
                </a>
                <a href="reflections.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'reflections.php' ? 'active' : ''; ?>"><i class="bi bi-stars"></i> Reflections</a>
                <a href="journal.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'journal.php' ? 'active' : ''; ?>"><i class="bi bi-pencil"></i> Journal</a>
                <a href="goals.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'goals.php' ? 'active' : ''; ?>"><i class="bi bi-bullseye"></i> Goals</a>
                <a href="insights.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'insights.php' ? 'active' : ''; ?>"><i class="bi bi-bar-chart"></i> Insights</a>
            </nav>
        </div>

        <div class="sidebar-bottom">
            <div class="sidebar-care">Take care of yourself 💚</div>
            <a href="logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i> Log out</a>
        </div>
    </aside>

    <main class="app-main">
