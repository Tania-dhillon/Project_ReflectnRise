<?php
// Authentication 
// ------------------------------------------------------------
// This is for the user authentication - session start, login status checks,
// redirect helpers, and output escaping helper

// Start session once so user login data is available on all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Checks whether the user is currently logged in or not
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/** This will redirect the user if they are already logged inn
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

/** This protects a page so only users that are logged in can access it
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

/** This will safely escape output in HTML
 *
 * @param string|null $value
 * @return string
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
