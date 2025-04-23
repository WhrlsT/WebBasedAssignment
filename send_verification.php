<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    // Generate a random verification code
    $verificationCode = substr(md5(uniqid(rand(), true)), 0, 6);
    
    // Store the verification code in a temporary table
    $stmt = $conn->prepare("INSERT INTO temp_users (email, verification_code) VALUES (?, ?) ON DUPLICATE KEY UPDATE verification_code = ?");
    $stmt->bind_param("sss", $email, $verificationCode, $verificationCode);
    
    if ($stmt->execute()) {
        // Send email with verification code
        $to = $email;
        $subject = "Email Verification Code";
        $message = "Your verification code is: " . $verificationCode;
        $headers = "From: noreply@yourwebsite.com";
        
        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    
    $stmt->close();
    $conn->close();
}
?>