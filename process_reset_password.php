<?php
require '_base.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php'); // Redirect if accessed directly
    exit;
}

$token = $_POST['token'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Basic validation
if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
    // Redirect back with a generic error, though ideally this shouldn't happen with 'required' fields
     header('Location: reset_password.php?token=' . urlencode($token) . '&error=missing_fields&original_token=' . urlencode($token));
    exit;
}

if ($newPassword !== $confirmPassword) {
    header('Location: reset_password.php?token=' . urlencode($token) . '&error=mismatch&original_token=' . urlencode($token));
    exit;
}

if (strlen($newPassword) < 8) { // Enforce minimum password length
    header('Location: reset_password.php?token=' . urlencode($token) . '&error=short&original_token=' . urlencode($token));
    exit;
}

try {
    // Re-validate the token and get user ID
    $stmt = $pdo->prepare("SELECT UserID FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Token is invalid or expired
        header('Location: reset_password.php?error=invalid_token'); 
        exit;
    }

    $hashedPassword = sha1($newPassword); 

    // Update the user's password and clear the reset token
    $updateStmt = $pdo->prepare("UPDATE users SET Password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE UserID = ?");

    if ($updateStmt->execute([$hashedPassword, $user['UserID']])) {
        // Password updated successfully
        header('Location: login.php?success=password_reset');
        exit;
    } else {
        // Database update failed
        header('Location: reset_password.php?token=' . urlencode($token) . '&error=update_failed&original_token=' . urlencode($token));
        exit;
    }

} catch (PDOException $e) {
    error_log("Database error in process_reset_password.php: " . $e->getMessage());
     header('Location: reset_password.php?token=' . urlencode($token) . '&error=db_error&original_token=' . urlencode($token));
    exit;
}
?>