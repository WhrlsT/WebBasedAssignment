<?php
require '_base.php';

$_title = 'Forgot Password';
include '_head.php';
?>

<div class="login-container"> <!-- Reusing login container style -->
    <h2>Forgot Your Password?</h2>
    <p class="welcome-text">Enter your email address below, and we'll send you a link to reset your password.</p>

    <?php
    // Display messages
    if (isset($_GET['error'])) {
        $errorMsg = '';
        switch ($_GET['error']) {
            case 'email_not_found': $errorMsg = 'No account found with that email address.'; break;
            case 'send_error': $errorMsg = 'Could not send reset email. Please try again later.'; break;
            case 'db_error': $errorMsg = 'A database error occurred. Please try again later.'; break;
            default: $errorMsg = 'An unknown error occurred.'; break;
        }
        echo '<div class="error-message">' . htmlspecialchars($errorMsg) . '</div>';
    }
    if (isset($_GET['success']) && $_GET['success'] === 'reset_sent') {
         echo '<div class="success-message">If an account exists for that email, a password reset link has been sent. Please check your inbox (and spam folder).</div>';
    }
    ?>

    <form id="forgotPasswordForm" action="process_forgot_password.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
        </div>
        <div class="form-group">
            <button type="submit" class="login-btn">Send Reset Link</button> <!-- Reusing login button style -->
        </div>
        <p class="register-link"><a href="login.php">Back to Login</a></p> <!-- Reusing register link style -->
    </form>
</div>

<!-- You might want to include the same styles as login.php or link to a shared CSS file -->
<style>
/* Copy relevant styles from login.php or use a shared CSS file */
.login-container { max-width: 450px; margin: 40px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
.login-container h2 { text-align: center; margin-bottom: 5px; color: #333; font-size: 24px; }
.welcome-text { text-align: center; margin-bottom: 20px; color: #666; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; color: #333; font-weight: 500; }
.form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
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