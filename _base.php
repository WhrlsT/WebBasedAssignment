<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// Include email functions
require_once 'email_functions.php';

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

// Define send_email function if it doesn't exist
if (!function_exists('send_email')) {
    function send_email($to, $subject, $message) {
        // Email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: SigmaMart <noreply@sigmamart.com>" . "\r\n";
        
        // Send email
        if (mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            error_log("Failed to send email to: $to");
            return false;
        }
    }
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

// Generate OTP function
if (!function_exists('generate_otp')) {
    function generate_otp($length = 6) {
        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}

// Send OTP email function
if (!function_exists('send_otp_email')) {
    function send_otp_email($email, $otp, $subject = "Email Verification Code") {
        // Get email configuration
        global $email_config;
        
        // Create email content
        $message = "
        <html>
        <head>
            <title>{$subject}</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>{$subject}</h2>
                <p>Your verification code is: <strong style='font-size: 18px; letter-spacing: 2px;'>{$otp}</strong></p>
                <p>This code will expire in 10 minutes.</p>
                <p>If you did not request this code, please ignore this email.</p>
            </div>
        </body>
        </html>
        ";
        
        // Send email
        send_email($email, $subject, $message);
    }

    // Function to send admin OTP email
    function send_admin_otp_email($email, $otp) {
        $subject = "Admin Login Verification Code";
        $message = "
        <html>
        <head>
            <title>{$subject}</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <h2 style='color: #333;'>{$subject}</h2>
                <p>Your admin login verification code is: <strong style='font-size: 18px; letter-spacing: 2px;'>{$otp}</strong></p>
                <p>This code will expire in 10 minutes.</p>
                <p>If you did not request this code, please ignore this email.</p>
                <p style='color: #d9534f;'><strong>Security Notice:</strong> This is an admin account login attempt. If you did not initiate this login, please secure your account immediately.</p>
            </div>
        </body>
        </html>
        ";
        
        // Send email
        send_email($email, $subject, $message);
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

