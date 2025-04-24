<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// Include PHPMailer library files globally (non-namespaced version)
// Moved from inside send_password_reset_email function
require_once __DIR__ . '/lib/PHPMailer.php';
require_once __DIR__ . '/lib/SMTP.php';
require_once __DIR__ . '/lib/Exception.php';

// Include email functions (which contains the PHPMailer setup)
require_once 'email_functions.php'; // This should already be here

// Database connection using PDO
try {
    $host = 'localhost';
    $dbname = 'sigmamart';
    $username = 'root';
    $password = '';
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Set global $_db variable
    global $_db;
    $_db = $pdo;
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}



// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);

// Check for remember me cookie
if (!$loggedIn && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    try {
        // Fixed column name from profileimgath to profileimgpath
        $stmt = $pdo->prepare("SELECT UserID, FirstName, LastName, UserName, Email, UserType, profileimgpath FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();

            // Set session variables
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['last_name'] = $user['LastName'];
            $_SESSION['username'] = $user['UserName'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['user_type'] = $user['UserType'];
            $_SESSION['profile_picture'] = $user['profileimgpath'];

            $loggedIn = true;
        }
    } catch (PDOException $e) {
        // Just continue as not logged in
    }
}

// Helper functions
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Only define the function if it doesn't already exist
if (!function_exists('get_current_user')) {
    function get_current_user() {
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'user_type' => $_SESSION['user_type'],
                'profile_picture' => $_SESSION['profile_picture']
            ];
        }
        return null;
    }
}

// --- Update send_password_reset_email to use PHPMailer ---
if (!function_exists('send_password_reset_email')) {
    function send_password_reset_email($email, $token) {

        // REMOVED require_once statements from here as they are now global

        // Instantiate PHPMailer (now globally available)
        $mail = new PHPMailer(true);

        $resetLink = 'http://localhost:8000/reset_password.php?token=' . urlencode($token); // Adjust URL if needed

        $subject = 'Password Reset Request';
        $htmlBody = "
        <html>
        <head><title>{$subject}</title></head>
        <body>
            <p>Hello,</p>
            <p>You requested a password reset for your account.</p>
            <p>Please click the link below to set a new password. This link is valid for 1 hour:</p>
            <p><a href='" . $resetLink . "'>" . $resetLink . "</a></p>
            <p>If you did not request a password reset, please ignore this email.</p>
            <p>Thanks,<br>Your Website Team</p>
        </body>
        </html>
        ";
        $textBody = strip_tags($htmlBody); // Simple text version

        try {
            // Server settings (Copy from email_functions.php)
            $mail->SMTPDebug = 0;                      // Disable debug output for production (set to 2 for testing if needed)
            $mail->isSMTP();                           // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                  // Enable SMTP authentication
            $mail->Username   = 'AACS3173@gmail.com';  // SMTP username (ensure this is correct)
            $mail->Password   = 'xxna ftdu plga hzxl'; // SMTP password (ensure this is correct - use App Password for Gmail)
            // Use the non-namespaced constant
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;                   // TCP port to connect to

            // Recipients
            $mail->setFrom('AACS3173@gmail.com', 'SigmaMart'); // Use your sending email
            $mail->addAddress($email);              // Add recipient

            // Content
            $mail->isHTML(true);                       // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
            error_log("Password reset email sent successfully to: $email");
            return true;
        // Catch the base Exception
        } catch (Exception $e) {
            error_log("Password reset email could not be sent to $email. Mailer Error: {$mail->ErrorInfo}");
            return false; // Return false on error
        }
    }
}
// --- End Update ---


// Generate OTP function
if (!function_exists('generate_otp')) {
    function generate_otp($length = 6) {
        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}


// Create necessary tables if they don't exist
try {
    // Users table remains the same
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        FirstName VARCHAR(50) NOT NULL,
        LastName VARCHAR(50) NOT NULL,
        UserName VARCHAR(50) UNIQUE NOT NULL,
        Email VARCHAR(100) UNIQUE NOT NULL,
        Password VARCHAR(255) NOT NULL,
        UserType ENUM('Admin', 'Member') NOT NULL DEFAULT 'Member',
        profileimgpath VARCHAR(255),
        remember_token VARCHAR(100),
        is_verified TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Verification codes table - ensure it has 'login' type
    $pdo->exec("CREATE TABLE IF NOT EXISTS verification_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        code VARCHAR(10) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        type ENUM('registration', 'login', 'password_reset') NOT NULL
    )");
} catch (PDOException $e) {
    // Log the error but continue
    error_log("Table creation error: " . $e->getMessage());
}

?>