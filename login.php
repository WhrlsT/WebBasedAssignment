<?php
require '_base.php';

$_title = 'Login';
include '_head.php';
?>

<div class="login-container">
    <h2>Welcome Back!</h2>
    <p class="welcome-text">Please log in to continue.</p>

    <?php
    // Display messages
    if (isset($_GET['error'])) {
        $errorMsg = '';
        switch ($_GET['error']) {
            case 'empty_fields': $errorMsg = 'Please fill in both username/email and password.'; break;
            case 'invalid_credentials': $errorMsg = 'Invalid username, email, or password.'; break;
            case 'database_error': $errorMsg = 'An error occurred. Please try again later.'; break;
            case 'account_deactivated': $errorMsg = 'Your account has been deactivated. Please contact support.'; break; // New message
            case 'not_verified': $errorMsg = 'Your email is not verified. Please check your email for the verification code.'; break;
            default: $errorMsg = 'An unknown error occurred.'; break;
        }
        echo '<div class="error-message">' . htmlspecialchars($errorMsg) . '</div>';
    }
    if (isset($_GET['success'])) {
         echo '<div class="success-message">Password reset successfully. You can now log in.</div>';
    }
     if (isset($_GET['info']) && $_GET['info'] === 'verification_sent') {
        echo '<div class="info-message">A new verification code has been sent to your email.</div>';
    }
    ?>

    <form id="loginForm" action="process_login.php" method="POST">
        <div class="form-group">
            <label for="loginIdentifier">Username or Email</label>
            <input type="text" id="loginIdentifier" name="loginIdentifier" placeholder="Enter your username or email" required>
        </div>
        <div class="form-group">
            <label for="loginPassword">Password</label>
            <div class="password-input-container">
                <input type="password" id="loginPassword" name="loginPassword" placeholder="Enter your password" required>
                <button type="button" class="toggle-password">👁️</button>
            </div>
        </div>
        <div class="form-options">
            <div class="remember-me">
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <label for="rememberMe">Remember Me</label>
            </div>
            <!-- Update the href attribute -->
            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
        </div>
        <div class="form-group">
            <button type="submit" class="login-btn">Login</button>
        </div>
        <p class="register-link">Not registered yet? <a href="register.php">Create an account</a></p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.querySelector('#loginPassword');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    });
});
</script>

<style>
.login-container {
    max-width: 450px;
    margin: 40px auto;
    padding: 30px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.login-container h2 {
    text-align: center;
    margin-bottom: 5px;
    color: #333;
    font-size: 24px;
}

.welcome-text {
    text-align: center;
    margin-bottom: 20px;
    color: #666;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #333;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.password-input-container {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.remember-me {
    display: flex;
    align-items: center;
}

.remember-me input {
    margin-right: 5px;
}

.forgot-password {
    color: #4a90e2;
    text-decoration: none;
}

.login-btn {
    width: 100%;
    padding: 12px;
    background-color: #ffcb05;
    color: #333;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
}

.login-btn:hover {
    background-color: #e6b800;
}

.register-link {
    text-align: center;
    margin-top: 20px;
    color: #666;
}

.register-link a {
    color: #4a90e2;
    text-decoration: none;
}

.success-message {
    background-color: #d4edda;
    border-left: 4px solid #28a745;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.error-message {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.info-message {
    background-color: #e7f3fe;
    border-left: 4px solid #2196F3;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 4px;
}
</style>

<?php
include '_foot.php';