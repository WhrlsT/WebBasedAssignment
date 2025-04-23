<?php
require_once '_base.php';

// Check if email is in session
if (!isset($_SESSION['temp_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['temp_email'];
$type = isset($_GET['type']) ? $_GET['type'] : 'registration';

// Only allow registration and login types
if ($type !== 'registration' && $type !== 'login') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$canResend = true;
$resendTime = 0;

// Check if resend cooldown is active
if (isset($_SESSION['last_otp_sent']) && $_SESSION['last_otp_sent'] > time() - 180) {
    $canResend = false;
    $resendTime = 180 - (time() - $_SESSION['last_otp_sent']);
}

// Handle OTP verification
if (is_post() && isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'] ?? '';
    
    if (empty($otp)) {
        $error = "Please enter the verification code.";
    } else {
        try {
            // Check if OTP is valid and not expired
            $stmt = $pdo->prepare("SELECT * FROM verification_codes 
                                  WHERE email = ? AND code = ? AND type = ? AND expires_at > NOW() 
                                  ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email, $otp, $type]);
            
            if ($stmt->rowCount() > 0) {
                // OTP is valid
                if ($type == 'registration') {
                    // Mark user as verified
                    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE Email = ?");
                    $stmt->execute([$email]);
                    
                    // Clear verification codes
                    $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE email = ? AND type = ?");
                    $stmt->execute([$email, $type]);
                    
                    // Clear session data
                    unset($_SESSION['temp_email']);
                    unset($_SESSION['last_otp_sent']);
                    
                    // Redirect to login page
                    header("Location: login.php?verified=true");
                    exit;
                } elseif ($type == 'login') {
                    // Clear verification codes
                    $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE email = ? AND type = ?");
                    $stmt->execute([$email, $type]);
                    
                    // Set session data from temp data
                    if (isset($_SESSION['temp_user_id']) && isset($_SESSION['temp_user_data'])) {
                        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
                        $_SESSION['first_name'] = $_SESSION['temp_user_data']['first_name'];
                        $_SESSION['last_name'] = $_SESSION['temp_user_data']['last_name'];
                        $_SESSION['username'] = $_SESSION['temp_user_data']['username'];
                        $_SESSION['email'] = $_SESSION['temp_user_data']['email'];
                        $_SESSION['user_type'] = $_SESSION['temp_user_data']['user_type'];
                        $_SESSION['profile_picture'] = $_SESSION['temp_user_data']['profile_picture'];
                        
                        // Handle remember me if set
                        if (isset($_SESSION['remember_me']) && $_SESSION['remember_me']) {
                            $token = bin2hex(random_bytes(32));
                            $expires = time() + (86400 * 30); // 30 days
                            
                            // Store token in database
                            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE UserID = ?");
                            $stmt->execute([$token, $_SESSION['user_id']]);
                            
                            // Set cookie
                            setcookie('remember_token', $token, $expires, '/');
                        }
                        
                        // Clear temp data
                        unset($_SESSION['temp_email']);
                        unset($_SESSION['temp_user_id']);
                        unset($_SESSION['temp_user_data']);
                        unset($_SESSION['remember_me']);
                        unset($_SESSION['last_otp_sent']);
                        
                        // Redirect to admin dashboard or home page
                        header("Location: admin/index.php");
                        exit;
                    } else {
                        $error = "Session data is missing. Please try logging in again.";
                    }
                }
            } else {
                // Check if code exists but expired
                $stmt = $pdo->prepare("SELECT * FROM verification_codes WHERE email = ? AND code = ? AND type = ? AND expires_at <= NOW()");
                $stmt->execute([$email, $otp, $type]);
                
                if ($stmt->rowCount() > 0) {
                    $error = "Verification code has expired. Please request a new one.";
                } else {
                    $error = "Invalid verification code. Please try again.";
                }
            }
        } catch (PDOException $e) {
            error_log("Verification error: " . $e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle resend OTP
if (is_post() && isset($_POST['resend_otp'])) {
    if (!$canResend) {
        $error = "Please wait " . $resendTime . " seconds before requesting a new code.";
    } else {
        try {
            // Generate new OTP
            $otp = generate_otp();
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Store OTP in database
            $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code, expires_at, type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $otp, $expires, $type]);
            
            // Send OTP email based on type
            if ($type == 'login' && isset($_GET['admin_verify']) && $_GET['admin_verify'] === 'true') {
                send_admin_otp_email($email, $otp);
            } else {
                send_otp_email($email, $otp);
            }
            
            // Set resend cooldown
            $_SESSION['last_otp_sent'] = time();
            
            $success = "A new verification code has been sent to your email.";
            $canResend = false;
            $resendTime = 180;
            
            // Refresh page to update UI
            header("refresh:1");
            exit;
        } catch (PDOException $e) {
            error_log("Resend OTP error: " . $e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
}

$_title = 'Verify ' . ($type == 'login' && isset($_GET['admin_verify']) ? 'Admin Login' : 'Email');
include '_head.php';
?>

<div class="verify-container">
    <h2><?php echo $type == 'admin_login' ? 'Admin Login Verification' : 'Email Verification'; ?></h2>
    <p class="welcome-text">Please enter the verification code sent to <strong><?php echo htmlspecialchars($email); ?></strong></p>
    
    <?php if($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form id="verifyForm" action="" method="POST">
        <div class="form-group">
            <label for="otp">Verification Code</label>
            <div class="otp-input-container">
                <input type="text" id="otp" name="otp" placeholder="Enter 6-digit code" maxlength="6" required>
                <button type="submit" name="resend_otp" class="resend-button" <?php echo !$canResend ? 'disabled' : ''; ?>>
                    <?php echo $canResend ? 'Resend Code' : 'Resend in ' . $resendTime . 's'; ?>
                </button>
            </div>
            <small>The code will expire in 10 minutes</small>
        </div>
        
        <div class="form-group">
            <button type="submit" name="verify_otp" class="verify-btn">Verify</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer for resend button
    const resendButton = document.querySelector('.resend-button');
    let timeLeft = <?php echo $resendTime; ?>;
    
    if (timeLeft > 0) {
        const countdownInterval = setInterval(function() {
            timeLeft--;
            resendButton.textContent = `Resend in ${timeLeft}s`;
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                resendButton.textContent = 'Resend Code';
                resendButton.disabled = false;
            }
        }, 1000);
    }
    
    // OTP input formatting
    const otpInput = document.getElementById('otp');
    otpInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>

<style>
.verify-container {
    max-width: 450px;
    margin: 40px auto;
    padding: 30px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.verify-container h2 {
    text-align: center;
    margin-bottom: 5px;
    color: #333;
    font-size: 24px;
}

.otp-input-container {
    display: flex;
    gap: 10px;
}

.otp-input-container input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    letter-spacing: 2px;
    text-align: center;
}

.resend-button {
    padding: 0 15px;
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    white-space: nowrap;
}

.resend-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.verify-btn {
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
    margin-top: 10px;
}

.verify-btn:hover {
    background-color: #e6b800;
}

small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}
</style>

<?php
include '_foot.php';
?>