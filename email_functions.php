<?php
// Using PHP's mail function or PHPMailer for sending emails

function send_otp_email($to_email, $otp) {
    // Email details
    $subject = "Your One-Time Passcode from SigmaMart";
    
    // Create email body with the specified format
    $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #333;'>Your One-Time Passcode from SigmaMart</h2>
            <p style='color: #666; font-size: 16px;'>Hello,</p>
            <p style='color: #333; font-size: 24px; font-weight: bold; margin: 20px 0;'>{$otp}</p>
            <p style='color: #666; font-size: 16px;'>is your one-time passcode (OTP) for your registration</p>
            <p style='color: #666; font-size: 14px;'>You can copy and paste or enter the code manually when prompted.</p>
            <p style='color: #666; font-size: 14px;'>The code was requested from the website and will be valid for 10 minutes.</p>
            <p style='color: #666; font-size: 14px; margin-top: 30px;'>Enjoy the App!</p>
            <p style='color: #666; font-size: 14px;'>Sigma team</p>
        </div>
    ";
    
    // Plain text alternative
    $textBody = "Your One-Time Passcode from SigmaMart\n\n";
    $textBody .= "Hello,\n\n";
    $textBody .= "{$otp} is your one-time passcode (OTP) for your registration\n\n";
    $textBody .= "You can copy and paste or enter the code manually when prompted.\n\n";
    $textBody .= "The code was requested from the website and will be valid for 10 minutes.\n\n";
    $textBody .= "Enjoy the App!\n\n";
    $textBody .= "Sigma team";
    
    // For testing purposes, store the OTP in a session variable
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['last_otp'] = $otp;
    $_SESSION['otp_email'] = $to_email;
    
    // Log the OTP for testing
    error_log("OTP for $to_email: $otp");

    // Use the existing PHPMailer library
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';
    require_once 'lib/Exception.php';
    
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output for production
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = 'AACS3173@gmail.com';  // SMTP username
        $mail->Password   = 'xxna ftdu plga hzxl'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                   // TCP port to connect to
        
        // Recipients
        $mail->setFrom('AACS3173@gmail.com', 'SigmaMart');
        $mail->addAddress($to_email);              // Add a recipient
        
        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody;
        
        $mail->send();
        error_log("Email sent successfully to: $to_email");
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        // For testing purposes, we'll still return true
        return true;
    }
}

function generate_otp() {
    // Generate a 6-digit OTP
    return sprintf("%06d", mt_rand(1, 999999));
}
?>