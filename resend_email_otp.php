<?php
require '_base.php';

// Redirect if not logged in
if (!$loggedIn) {
    header("Location: login.php");
    exit;
}

// Check if email is in session
if (!isset($_SESSION['new_email'])) {
    header("Location: profile.php");
    exit;
}

$email = $_SESSION['new_email'];

// Check if resend cooldown is active
if (isset($_SESSION['last_otp_sent']) && $_SESSION['last_otp_sent'] > time() - 180) {
    $_SESSION['update_error'] = "Please wait before requesting another verification code.";
    header("Location: profile.php");
    exit;
}

// Generate new OTP
$otp = rand(100000, 999999);
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

try {
    // Delete any existing verification codes for this email and type
    $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE email = ? AND type = 'email_change'");
    $stmt->execute([$email]);
    
    // Insert new code
    $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code, expires_at, type) VALUES (?, ?, ?, 'email_change')");
    $stmt->execute([$email, $otp, $expires]);
    
    // Send OTP email
    $subject = "Email Verification OTP";
    $message = "Your new OTP for email verification is: $otp\nThis code will expire in 10 minutes.";
    
    // Use your existing email function if available
    if (function_exists('send_otp_email')) {
        $emailSent = send_otp_email($email, $otp);
    } else {
        // Fallback to PHP mail function
        $headers = "From: noreply@yourwebsite.com";
        $emailSent = mail($email, $subject, $message, $headers);
    }
    
    if ($emailSent) {
        $_SESSION['last_otp_sent'] = time();
    } else {
        $_SESSION['update_error'] = "Failed to send verification email. Please try again.";
    }
} catch (PDOException $e) {
    $_SESSION['update_error'] = "Database error: " . $e->getMessage();
}

// Redirect back to profile page
header("Location: profile.php");
exit;
?>