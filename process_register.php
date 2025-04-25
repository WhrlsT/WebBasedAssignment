<?php
require_once '_base.php';
require_once 'email_functions.php';

// For debugging
error_log("Process register request: " . json_encode($_POST));

// Set proper headers for AJAX responses
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is a verification request
    if (isset($_POST['action']) && $_POST['action'] == 'verify_email') {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email address is required']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        // Generate OTP
        $otp = generate_otp();
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Store OTP in database
        try {
            // First, delete any existing codes for this email
            $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE email = ? AND type = 'registration'");
            $stmt->execute([$email]);
            
            // Insert new code
            $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code, expires_at, type) VALUES (?, ?, ?, 'registration')");
            $stmt->execute([$email, $otp, $expires]);
            
            // Send OTP email using your custom function
            if (send_otp_email($email, $otp)) {
                echo json_encode(['success' => true, 'message' => 'OTP code sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send OTP code. Please try again.']);
            }
        } catch (PDOException $e) {
            error_log("OTP generation error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
        }
        exit;
    }
    
    // Check if this is a code verification request
    if (isset($_POST['action']) && $_POST['action'] == 'check_code') {
        $email = $_POST['email'] ?? '';
        $code = $_POST['code'] ?? '';
        
        if (empty($email) || empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Email and OTP code are required']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM verification_codes WHERE email = ? AND code = ? AND type = 'registration' AND expires_at > NOW()");
            $stmt->execute([$email, $code]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
            } else {
                // Check if code exists but expired
                $stmt = $pdo->prepare("SELECT * FROM verification_codes WHERE email = ? AND code = ? AND type = 'registration' AND expires_at <= NOW()");
                $stmt->execute([$email, $code]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'OTP code has expired. Please request a new one.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid OTP code. Please check and try again.']);
                }
            }
        } catch (PDOException $e) {
            error_log("Code verification error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
        }
        exit;
    }
    
    // This is the main registration form submission
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $verificationCode = $_POST['verificationCode'] ?? '';
    $agreeTerms = isset($_POST['agreeTerms']) ? true : false;
    
    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || empty($verificationCode)) {
        header("Location: register.php?error=empty_fields");
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=invalid_email");
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        header("Location: register.php?error=passwords_dont_match");
        exit;
    }
    
    // Check if terms are agreed
    if (!$agreeTerms) {
        header("Location: register.php?error=terms_not_agreed");
        exit;
    }
    
    // Verify the OTP code
    try {
        $stmt = $pdo->prepare("SELECT * FROM verification_codes WHERE email = ? AND code = ? AND type = 'registration' AND expires_at > NOW()");
        $stmt->execute([$email, $verificationCode]);
        
        if ($stmt->rowCount() == 0) {
            header("Location: register.php?error=invalid_otp");
            exit;
        }
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT UserID FROM users WHERE UserName = ? OR Email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: register.php?error=user_exists");
            exit;
        }
        
        // Hash the password using SHA1
        $hashedPassword = sha1($password);
        
        // Set default values
        $userType = 'Member';
        $profilePicture = null;
        
        // Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (FirstName, LastName, UserName, Email, Password, UserType, profileimgpath, is_verified) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        
        $result = $stmt->execute([
            $firstName, 
            $lastName, 
            $username, 
            $email, 
            $hashedPassword, 
            $userType, 
            $profilePicture
        ]);
        
        if ($result) {
            // Delete the verification code
            $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE email = ? AND type = 'registration'");
            $stmt->execute([$email]);
            
            // Redirect to profile page
            header("Location: login.php");
            exit;
        } else {
            header("Location: register.php?error=database_error");
            exit;
        }
    } catch (PDOException $e) {
        // Log the error
        error_log("Registration error: " . $e->getMessage());
        header("Location: register.php?error=database_error");
        exit;
    }
}

// If this point is reached for an AJAX request, return an error
if (isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// If not POST request or not an AJAX call, redirect to register page
header("Location: register.php");
exit;
?>