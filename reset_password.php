<?php
require '_base.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$showForm = false;

if (empty($token)) {
    $error = "Invalid or missing password reset token.";
} else {
    try {
        // Validate the token
        $stmt = $pdo->prepare("SELECT UserID FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $showForm = true; // Token is valid, show the form
        } else {
            $error = "Invalid or expired password reset token. Please request a new one.";
        }
    } catch (PDOException $e) {
        error_log("Database error in reset_password.php: " . $e->getMessage());
        $error = "An error occurred. Please try again later.";
    }
}

// Handle messages from process_reset_password.php redirection
if (isset($_GET['error'])) {
     switch ($_GET['error']) {
        case 'mismatch': $error = 'Passwords do not match.'; break;
        case 'short': $error = 'Password must be at least 8 characters long.'; break;
        case 'invalid_token': $error = 'Invalid or expired token. Please request a new reset.'; break;
        case 'update_failed': $error = 'Failed to update password. Please try again.'; break;
        case 'db_error': $error = 'A database error occurred.'; break;
        default: $error = 'An unknown error occurred.'; break;
     }
     // If redirected with error, ensure token is passed again if needed for form action
     $token = $_GET['original_token'] ?? $token;
     $showForm = true; // Still show form on error if token was initially valid
}
if (isset($_GET['success']) && $_GET['success'] === 'password_reset') {
    // This case should ideally redirect to login, but handle if accessed directly
    $success = "Password has been reset successfully. You can now log in.";
    $showForm = false; // Don't show form on success
}


$_title = 'Reset Password';
include '_head.php';
?>

<div class="login-container"> <!-- Reusing login container style -->
    <h2>Reset Your Password</h2>

    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <p class="register-link"><a href="login.php">Proceed to Login</a></p>
    <?php endif; ?>

    <?php if ($showForm): ?>
        <p class="welcome-text">Enter your new password below.</p>
        <form id="resetPasswordForm" action="process_reset_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <div class="password-input-container">
                    <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                    <button type="button" class="toggle-password">👁️</button>
                </div>
                 <small>Must be at least 8 characters long.</small>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password</label>
                 <div class="password-input-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                    <button type="button" class="toggle-password">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="login-btn">Reset Password</button> <!-- Reusing login button style -->
            </div>
        </form>
    <?php elseif (!$success): ?>
         <p class="register-link"><a href="forgot_password.php">Request a new reset link</a></p>
    <?php endif; ?>
</div>

<!-- Include password toggle script and styles -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling; // Assumes button is immediately after input
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    });
});
</script>
<style>
/* Copy relevant styles from login.php or use a shared CSS file */
.login-container { max-width: 450px; margin: 40px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
.login-container h2 { text-align: center; margin-bottom: 5px; color: #333; font-size: 24px; }
.welcome-text { text-align: center; margin-bottom: 20px; color: #666; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; color: #333; font-weight: 500; }
.form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
.password-input-container { position: relative; }
.toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; }
.form-group small { font-size: 0.85em; color: #666; margin-top: 5px; display: block; }
.login-btn { width: 100%; padding: 12px; background-color: #ffcb05; color: #333; border: none; border-radius: 4px; font-size: 16px; font-weight: 500; cursor: pointer; transition: background-color 0.3s; }
.login-btn:hover { background-color: #e6b800; }
.register-link { text-align: center; margin-top: 20px; color: #666; }
.register-link a { color: #4a90e2; text-decoration: none; }
.success-message { background-color: #d4edda; border-left: 4px solid #28a745; padding: 12px; margin-bottom: 15px; border-radius: 4px; }
.error-message { background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 12px; margin-bottom: 15px; border-radius: 4px; }
</style>

<?php
include '_foot.php';
?>