<?php
require '_base.php'; 

require_once 'email_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

$email = $_POST['email'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: forgot_password.php?error=invalid_email');
    exit;
}

try {
    // Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT UserID FROM users WHERE Email = ? AND (is_active = 1 OR is_active IS NULL)"); // Check active users
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a unique, secure token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

        // Store the token and expiry date in the database
        $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE UserID = ?");
        if (!$updateStmt->execute([$token, $expires, $user['UserID']])) {
             throw new Exception("Failed to update reset token in database.");
        }

        // Send the password reset email
        if (!function_exists('send_password_reset_email')) {
             error_log("Error: send_password_reset_email function does not exist.");
             header('Location: forgot_password.php?error=send_error');
             exit;
        }

        if (!send_password_reset_email($email, $token)) {
            error_log("Failed to send password reset email to: " . $email);

        }
    } else {
        // Email not found, but don't reveal this to prevent user enumeration
        // Log this attempt internally if needed
        error_log("Password reset requested for non-existent or inactive email: " . $email);
    }

    // Always redirect to success page to prevent email enumeration
    header('Location: forgot_password.php?success=reset_sent');
    exit;

} catch (PDOException $e) {
    error_log("Database error in process_forgot_password.php: " . $e->getMessage());
    header('Location: forgot_password.php?error=db_error');
    exit;
} catch (Exception $e) {
    error_log("General error in process_forgot_password.php: " . $e->getMessage());
    header('Location: forgot_password.php?error=send_error'); // Use a generic error
    exit;
}
?>