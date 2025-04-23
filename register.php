<?php
require '_base.php';
$_title = 'Register';
include '_head.php';
?>

<div class="register-container">
    <h2>Create an Account</h2>
    <p class="welcome-text">Join our community today!</p>
    
    <!-- Add this at the beginning of your form (after the heading) -->
    <?php
    $error_messages = [
        'empty_fields' => 'Please fill in all required fields.',
        'invalid_email' => 'Please enter a valid email address.',
        'passwords_dont_match' => 'Passwords do not match.',
        'terms_not_agreed' => 'You must agree to the Terms of Service and Privacy Policy.',
        'user_exists' => 'Username or email already exists. Please try another.',
        'invalid_otp' => 'Invalid or expired OTP code. Please request a new one.',
        'database_error' => 'A database error occurred. Please try again later.'
    ];
    
    if (isset($_GET['error']) && array_key_exists($_GET['error'], $error_messages)) {
        echo '<div class="error-message">' . $error_messages[$_GET['error']] . '</div>';
    }
    ?>
    
    <!-- Add this CSS for error messages -->
    <style>
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        border: 1px solid #f5c6cb;
    }
    </style>
    
    <form id="registerForm" action="process_register.php" method="POST">
        <div class="form-row">
            <div class="form-group half">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
            </div>
            <div class="form-group half">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Choose a username" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
        </div>
        
        <!-- Make sure this code is in your form, after the email input field -->
        <div class="form-group">
            <label for="verificationCode">OTP Code</label>
            <div class="otp-input-group">
                <input type="text" id="verificationCode" name="verificationCode" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" required>
                <button type="button" id="sendVerificationBtn" class="send-otp-btn">Send OTP</button>
            </div>
            <small id="verificationStatus" class="form-text"></small>
            
            <?php
            // For testing only - display the OTP
            if (isset($_SESSION['last_otp']) && isset($_SESSION['otp_email'])) {
                echo "<small class='form-text text-muted'>Test mode: OTP for " . $_SESSION['otp_email'] . " is " . $_SESSION['last_otp'] . "</small>";
            }
            ?>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-input-container">
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                <button type="button" class="toggle-password">👁️</button>
            </div>
        </div>
        
        <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <div class="password-input-container">
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                <button type="button" class="toggle-password">👁️</button>
            </div>
        </div>
        
        <div class="form-group terms">
            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
            <label for="agreeTerms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
        </div>
        
        <div class="form-group">
            <button type="submit" class="register-btn">Create Account</button>
        </div>
        
        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<!-- Add this after the OTP input field for testing purposes -->
<div class="test-otp-display" style="margin-top: 5px; font-size: 12px; color: #999;">
    <?php
    // For testing only - display the OTP
    if (isset($_SESSION['last_otp']) && isset($_SESSION['otp_email'])) {
        echo "Test mode: OTP for " . $_SESSION['otp_email'] . " is " . $_SESSION['last_otp'];
    }
    ?>
</div>

<!-- Add this at the end of your file, just before the closing body tag -->
<script>
// Simple debugging script
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
    console.log('Send button exists:', !!document.getElementById('sendVerificationBtn'));
    console.log('Verification code input exists:', !!document.getElementById('verificationCode'));
    console.log('Email input exists:', !!document.getElementById('email'));
    console.log('Register button exists:', !!document.querySelector('.register-btn'));
    
    // Check if the button is visible and clickable
    const sendBtn = document.getElementById('sendVerificationBtn');
    if (sendBtn) {
        console.log('Button styles:', {
            display: getComputedStyle(sendBtn).display,
            visibility: getComputedStyle(sendBtn).visibility,
            position: getComputedStyle(sendBtn).position,
            zIndex: getComputedStyle(sendBtn).zIndex
        });
    }
    
    // Add event listener for the Send OTP button
    const sendVerificationBtn = document.getElementById('sendVerificationBtn');
    const emailInput = document.getElementById('email');
    const verificationStatus = document.getElementById('verificationStatus');
    
    if (sendVerificationBtn && emailInput && verificationStatus) {
        sendVerificationBtn.addEventListener('click', function() {
            // Check if email is empty
            if (!emailInput.value.trim()) {
                verificationStatus.textContent = 'Please enter your email address first';
                verificationStatus.className = 'form-text text-danger';
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                verificationStatus.textContent = 'Please enter a valid email address';
                verificationStatus.className = 'form-text text-danger';
                return;
            }
            
            // Disable button and show loading state
            this.disabled = true;
            this.textContent = 'Sending...';
            verificationStatus.textContent = 'Sending OTP code...';
            verificationStatus.className = 'form-text text-info';
            
            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_register.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                console.log('Response received:', this.responseText); // Debug
                
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            verificationStatus.textContent = 'OTP code sent to your email';
                            verificationStatus.className = 'form-text text-success';
                            
                            // Start countdown for resend button
                            let countdown = 300; // 5 minutes in seconds
                            const sendBtn = document.getElementById('sendVerificationBtn');
                            sendBtn.disabled = true;
                            
                            const timer = setInterval(function() {
                                const minutes = Math.floor(countdown / 60);
                                const seconds = countdown % 60;
                                sendBtn.textContent = `Resend in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                                countdown--;
                                
                                if (countdown < 0) {
                                    clearInterval(timer);
                                    sendBtn.textContent = 'Resend OTP';
                                    sendBtn.disabled = false;
                                }
                            }, 1000);
                        } else {
                            verificationStatus.textContent = response.message || 'Failed to send OTP code';
                            verificationStatus.className = 'form-text text-danger';
                            document.getElementById('sendVerificationBtn').disabled = false;
                            document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e, this.responseText); // Debug
                        verificationStatus.textContent = 'Error processing response';
                        verificationStatus.className = 'form-text text-danger';
                        document.getElementById('sendVerificationBtn').disabled = false;
                        document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
                    }
                } else {
                    verificationStatus.textContent = 'Server error, please try again';
                    verificationStatus.className = 'form-text text-danger';
                    document.getElementById('sendVerificationBtn').disabled = false;
                    document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
                }
            };
            
            xhr.onerror = function() {
                console.error('XHR error'); // Debug
                verificationStatus.textContent = 'Connection error, please try again';
                verificationStatus.className = 'form-text text-danger';
                document.getElementById('sendVerificationBtn').disabled = false;
                document.getElementById('sendVerificationBtn').textContent = 'Send OTP';
            };
            
            const data = 'action=verify_email&email=' + encodeURIComponent(emailInput.value.trim());
            console.log('Sending data:', data); // Debug
            xhr.send(data);
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility for both password fields
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordField = this.previousElementSibling;
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        });
    });
    
    // Add form submission handler
    const registerForm = document.getElementById('registerForm');
    const verificationCodeInput = document.getElementById('verificationCode');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            // Check if OTP is valid before submitting
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'verify_code.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (!response.success) {
                            // Invalid OTP - prevent form submission and clear only OTP field
                            e.preventDefault();
                            verificationCodeInput.value = '';
                            verificationCodeInput.focus();
                            
                            // Show error message
                            const verificationStatus = document.getElementById('verificationStatus');
                            if (verificationStatus) {
                                verificationStatus.textContent = response.message || 'Invalid OTP code. Please try again.';
                                verificationStatus.className = 'form-text text-danger';
                            } else {
                                alert('Invalid OTP code. Please try again.');
                            }
                        }
                        // If OTP is valid, form will submit normally
                    } catch (e) {
                        console.error('JSON parse error:', e);
                    }
                }
            };
            
            const email = document.getElementById('email').value;
            const code = verificationCodeInput.value;
            xhr.send('email=' + encodeURIComponent(email) + '&code=' + encodeURIComponent(code) + '&action=verify');
        });
    }
});
</script>

<!-- Add some CSS for the verification status messages -->
<style>
.text-danger {
    color: #dc3545;
}
.text-info {
    color: #17a2b8;
}
.text-success {
    color: #28a745;
}
.form-text {
    display: block;
    margin-top: 5px;
    font-size: 14px;
}
</style>

<?php
include '_foot.php';

