<?php
require '_base.php';

$_title = 'Login';
include '_head.php';
?>

<div class="login-container">
    <h2>Login</h2>
    <p class="welcome-text">Hi, Welcome back 👋</p>
    
    <?php if(isset($_GET['registered']) && $_GET['registered'] === 'true'): ?>
        <div class="success-message">Registration successful! Please login.</div>
    <?php endif; ?>
    
    <?php if(isset($_GET['admin_verify']) && $_GET['admin_verify'] === 'true'): ?>
        <div class="info-message">Admin login requires additional verification. Please check your email.</div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
        <div class="error-message">
            <?php 
                $error = $_GET['error'];
                if($error == 'invalid_credentials') {
                    echo "Invalid username/email or password.";
                } elseif($error == 'empty_fields') {
                    echo "Please fill in all required fields.";
                } elseif($error == 'not_verified') {
                    echo "Your email is not verified. Please check your email for verification code.";
                } else {
                    echo "An error occurred. Please try again.";
                }
            ?>
        </div>
    <?php endif; ?>
    
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
            <a href="#" class="forgot-password">Forgot Password?</a>
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