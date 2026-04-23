<?php
// Forgot password page
// ------------------------------------------------------------
// This page accepts an email address, creates a secure reset
// token, stores it in the database and then will email the reset link
// However, this has failed, does not send email to user, perhas due to it being locally hosted

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirects logged-in users away from auth page
redirectIfLoggedIn();

$success = '';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get submitted email
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Checks whether user exists
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Show same success message even if email doesn't exist
        // This avoids exposing which emails are registered
        $genericSuccess = 'If that email exists in our system, a password reset link has been sent.';

        if ($user) {
            // Create secure token and expiry
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Removes any old unused reset tokens for this user
            $deleteOld = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $deleteOld->execute([$user['id']]);

            // Stores new reset token
            $insert = $pdo->prepare('INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)');
            $insert->execute([$user['id'], $user['email'], $token, $expiresAt]);

            // Build reset link
            require_once __DIR__ . '/includes/config.php';
            $resetLink = rtrim(APP_URL,'http://localhost/ReflectnRise') . '/reset-password.php?token=' . urlencode($token);

            // Email details
            $to = $user['email'];
            $subject = 'Reflect & Rise - Reset Your Password';
            $message = "Hello,\n\n"
                . "We received a request to reset your password for Reflect & Rise.\n\n"
                . "Click the link below to reset your password:\n"
                . $resetLink . "\n\n"
                . "This link will expire in 1 hour.\n\n"
                . "If you did not request this, you can ignore this email.\n\n"
                . "Regards,\nReflect & Rise";
            $headers = "From: Reflect & Rise <" . MAIL_FROM_EMAIL . ">\r\n";
            $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($to, $subject, $message, $headers);
        }

        $success = $genericSuccess;
        $email = '';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <a href="login.php" class="auth-back"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        <h1>Reset your password</h1>
        <p class="auth-subtext">Enter your email and we’ll send you a link to reset your password</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="mt-4">
            <div class="mb-4">
                <label class="form-label">Email</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-control mindful-input" placeholder="you@example.com" value="<?= e($email); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-dark-blue w-100">Send reset link</button>
        </form>
    </div>
</div>

</body>
</html>
