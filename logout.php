<?php
require '_base.php';

// Clear remember token in database
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE UserID = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Log error but continue
        error_log("Logout error: " . $e->getMessage());
    }
}

// Clear remember token cookie
setcookie('remember_token', '', time() - 3600, '/');

// Clear session
session_unset();
session_destroy();

// Redirect to home page
header("Location: index.php");
exit;
?>