<?php
// Prevent direct access to this file
if (!defined('INCLUDED_FROM_PROFILE')) {
    header('Location: ../profile.php');
    exit;
}
?>

<div id="profile-info" class="profile-section">
    <h2>Profile Information</h2>
    
    <?php if ($updateSuccess): ?>
        <div class="success-message">Profile updated successfully!</div>
    <?php endif; ?>
    
    <?php if ($updateError): ?>
        <div class="error-message"><?php echo $updateError; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['verify_email']) && $_SESSION['verify_email']): ?>
        <div class="otp-verification">
            <h3>Verify Your Email</h3>
            <p>We've sent a verification code to <?php echo htmlspecialchars($_SESSION['new_email']); ?>.</p>
            <form action="" method="POST" class="otp-form">
                <div class="form-group">
                    <label for="otp">Enter Verification Code</label>
                    <input type="text" id="otp" name="otp" required>
                </div>
                <button type="submit" name="verify_otp" class="upload-button">Verify Email</button>
            </form>
            
            <?php
            $canResend = true;
            $timeLeft = 0;
            
            if (isset($_SESSION['last_otp_sent'])) {
                $timeSince = time() - $_SESSION['last_otp_sent'];
                if ($timeSince < 180) { // 3 minutes cooldown
                    $canResend = false;
                    $timeLeft = 180 - $timeSince;
                }
            }
            ?>
            
            <?php if ($canResend): ?>
                <form action="resend_email_otp.php" method="POST" class="resend-form">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['new_email']); ?>">
                    <button type="submit" name="resend_otp" class="resend-button">Resend Verification Code</button>
                </form>
            <?php else: ?>
                <p class="resend-timer">You can request a new code in <span id="countdown"><?php echo $timeLeft; ?></span> seconds</p>
                <script>
                    // Countdown timer for resend cooldown
                    let seconds = <?php echo $timeLeft; ?>;
                    const countdownElement = document.getElementById('countdown');
                    
                    const countdownTimer = setInterval(function() {
                        seconds--;
                        countdownElement.textContent = seconds;
                        
                        if (seconds <= 0) {
                            clearInterval(countdownTimer);
                            location.reload();
                        }
                    }, 1000);
                </script>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form action="" method="POST" class="profile-form" id="profile-form">
            <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['FirstName'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['LastName'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
                <small>Username cannot be changed</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="currentPassword">Current Password</label>
                <input type="password" id="currentPassword" name="currentPassword" required>
                <small>Enter your current password to confirm changes</small>
            </div>
            
            <button type="button" id="confirm-update" class="upload-button">Save Changes</button>
            <input type="hidden" name="update_profile" value="1">
        </form>
    <?php endif; ?>
</div>