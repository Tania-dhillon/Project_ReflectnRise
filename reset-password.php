<?php
// ------------------------------------------------------------
// Reset password page
// ------------------------------------------------------------
// This page validates the reset token and lets the user set
// a new password. Once used, the token is marked as used.
// ------------------------------------------------------------

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = '';
$validToken = false;

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL LIMIT 1');
    $stmt->execute([$token]);
    $resetRow = $stmt->fetch();

    if ($resetRow && strtotime($resetRow['expires_at']) > time()) {
        $validToken = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update user password
        $updateUser = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $updateUser->execute([$hashedPassword, $resetRow['user_id']]);

        // Mark token as used
        $markUsed = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
        $markUsed->execute([$resetRow['id']]);

        $success = 'Your password has been reset successfully. You can now log in.';
        $validToken = false;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <a href="login.php" class="auth-back"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        <h1>Create a new password</h1>
        <p class="auth-subtext">Choose a new password for your Reflect &amp; Rise account</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= e($success); ?>
                <div class="mt-2"><a href="login.php" class="fw-semibold text-decoration-none">Go to login</a></div>
            </div>
        <?php elseif (!$validToken): ?>
            <div class="alert alert-danger">This reset link is invalid or has expired.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <form method="POST" action="" class="mt-4">
                <input type="hidden" name="token" value="<?= e($token); ?>">

                <div class="mb-3">
                    <label class="form-label">New Password</label>
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

                <button type="submit" class="btn btn-dark-blue w-100">Reset password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
