<?php

// User signup page
// ------------------------------------------------------------
// This page creates a new user account using first name, email + password. 
// Passwords are stored securely.


require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// If user is already logged in, redirect to dashboard
redirectIfLoggedIn();

$errors = [];
$firstName = '';
$email = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get submitted form values
    $firstName = trim($_POST['first_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validates first name
    if ($firstName === '') {
        $errors[] = 'Please enter your first name.';
    }

    // Validate email
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Validates password length
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    // Confirm both passwords match
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Check if account already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            // Securely hash the password before saving
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Inserts new user into database
            $insert = $pdo->prepare('INSERT INTO users (first_name, email, password) VALUES (?, ?, ?)');
            $insert->execute([$firstName, $email, $hashedPassword]);

            $success = 'Account created successfully. You can now log in';
            $firstName = '';
            $email = '';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <a href="login.php" class="auth-back auth-back-block"><i class="bi bi-arrow-left"></i> Back to sign in</a>

        <div class="auth-brand auth-brand-centered auth-brand-signup mb-4">
            <div class="sidebar-logo auth-brand-logo"><i class="bi bi-book"></i></div>
            <div>
                <div class="sidebar-brand-title">Reflect &amp; Rise</div>
                <div class="sidebar-brand-subtitle">STUDENT WELLBEING</div>
            </div>
        </div>

        <h1>Create your account</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="mt-4">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-person"></i>
                    <input type="text" name="first_name" class="form-control mindful-input" placeholder="Your first name" value="<?= e($firstName); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-control mindful-input" placeholder="you@example.com" value="<?= e($email); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" class="form-control mindful-input" placeholder="Min. 8 characters" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="confirm_password" class="form-control mindful-input" placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-dark-blue w-100">Create account</button>
        </form>
    </div>
</div>

</body>
</html>

<script>
document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll(".auth-logo").forEach(function(el){el.style.cursor="pointer";el.addEventListener("click",function(){window.location="index.php";});});});</script>
