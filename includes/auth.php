<?php
// ------------------------------------------------------------
// Authentication helper functions
// ------------------------------------------------------------
// This file handles session start, login status checks,
// redirect helpers, and output escaping helper.
// ------------------------------------------------------------

// Start session once so user login data is available on all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check whether the user is currently logged in.
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Redirect user if they are already logged in.
 * Useful on login/signup pages.
 *
 * @param string $location
 * @return void
 */
function redirectIfLoggedIn(string $location = 'dashboard.php'): void
{
    if (isLoggedIn()) {
        header("Location: {$location}");
        exit;
    }
}

/**
 * Protect a page so only logged-in users can access it.
 *
 * @param string $location
 * @return void
 */
function requireLogin(string $location = 'login.php'): void
{
    if (!isLoggedIn()) {
        header("Location: {$location}");
        exit;
    }
}

/**
 * Safely escape output in HTML.
 *
 * @param string|null $value
 * @return string
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
