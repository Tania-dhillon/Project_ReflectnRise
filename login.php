<?php
// User login page
// ------------------------------------------------------------
// This page validates user credentials and creates the login session if the user email/password is correct

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, send user to dashboard
redirectIfLoggedIn();

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get submitted values
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        // Find the user by email
        $stmt = $pdo->prepare('SELECT id, first_name, email, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify password hash
        if ($user && password_verify($password, $user['password'])) {
            // Save user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = !empty($user['first_name']) ? $user['first_name'] : preg_replace('/@.*/', '', $user['email']);

            // Redirects to dashboard after successful login
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card login-card">
        <div class="auth-brand auth-brand-centered mb-4">
            <div class="sidebar-logo auth-brand-logo"><i class="bi bi-book"></i></div>
            <div>
                <div class="sidebar-brand-title">Reflect &amp; Rise</div>
                <div class="sidebar-brand-subtitle">STUDENT WELLBEING</div>
            </div>
        </div>

        <p class="auth-subtext auth-subtext-only">Sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="mt-4">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-control mindful-input" placeholder="you@example.com" value="<?= e($email); ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" class="form-control mindful-input" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-dark-blue w-100">Sign in</button>
        </form>

        <div class="d-flex justify-content-between align-items-center mt-4 auth-links small">
            <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
            <span>Need an account? <a href="signup.php" class="fw-semibold text-decoration-none">Sign up</a></span>
        </div>
    </div>
</div>

</body>
</html>

<script>// auth-logo-link-fix
document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll(".auth-logo").forEach(function(el){el.style.cursor="pointer";el.addEventListener("click",function(){window.location="index.php";});});});</script>
