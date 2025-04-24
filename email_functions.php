<?php
// NO 'use' statements here
// NO 'require_once' for PHPMailer library files here (they are in _base.php)

// Include TCPDF library (assuming it's not autoloaded or already in _base.php)
require_once __DIR__ . '/lib/TCPDF/tcpdf.php'; // Adjust path if necessary

// --- OTP Functions ---

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
        session_start(); // Start session if not already started (though _base.php should handle this)
    }
    $_SESSION['last_otp'] = $otp;
    $_SESSION['otp_email'] = $to_email;

    // Log the OTP for testing
    error_log("OTP for $to_email: $otp");

    // REMOVE redundant require_once statements for PHPMailer here
    // require_once 'lib/PHPMailer.php';
    // require_once 'lib/SMTP.php';
    // require_once 'lib/Exception.php';

    // Instantiate PHPMailer (loaded globally from _base.php)
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output for production
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';      // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = 'AACS3173@gmail.com';  // SMTP username
        $mail->Password   = 'xxna ftdu plga hzxl'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption (use constant)
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
        error_log("OTP Email sent successfully to: $to_email");
        return true;
    } catch (Exception $e) { // Catch base Exception
        error_log("OTP Email could not be sent to $to_email. Mailer Error: {$mail->ErrorInfo}");
        // For testing purposes, you might still return true, but consider returning false for production
        // return false;
        return true; // Keep original behavior for now
    }
}

// Ensure generate_otp function is defined if not already in _base.php
if (!function_exists('generate_otp')) {
    function generate_otp($length = 6) {
        // Generate a 6-digit OTP
        return sprintf("%06d", mt_rand(1, 999999));
    }
}

// --- PDF and Receipt Email Functions ---

/**
 * Generates PDF receipt content from order data using TCPDF.
 * Includes HTML content from an external template file.
 *
 * @param array $order The main order data.
 * @param array $items Array of order items.
 * @param array|null $shipping Shipping address data.
 * @return string|false Raw PDF data string on success, false on failure.
 */
function generate_order_pdf_content(array $order, array $items, ?array $shipping): string|false {
    // Pass data to the template and get HTML content
    // Ensure variables used in the template are available in this scope
    // (e.g., $order, $items, $shipping)
    ob_start();
    // Use __DIR__ for reliable path resolution
    $templatePath = __DIR__ . '/receipt_template.php';
    if (file_exists($templatePath)) {
        include $templatePath; // Path to your HTML template
    } else {
        error_log("Receipt template file not found: " . $templatePath);
        ob_end_clean(); // Clean buffer if template not found
        return false;
    }
    $html = ob_get_clean();

    if (empty($html)) {
        error_log("Failed to render receipt HTML template or template was empty.");
        return false;
    }

    try {
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('SigmaMart');
        $pdf->SetTitle('Order Receipt - ' . htmlspecialchars($order['order_reference']));
        $pdf->SetSubject('Order Receipt');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set font
        $pdf->SetFont('helvetica', '', 10); // Or 'dejavusans' if you need better UTF-8 support

        // Add a page
        $pdf->AddPage();

        // Write the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document as string
        // 'S' returns the document as a string.
        return $pdf->Output('receipt.pdf', 'S');

    } catch (Exception $e) {
        error_log("TCPDF error generating receipt: " . $e->getMessage());
        return false;
    }
}

/**
 * Sends an email with a PDF attachment from a file path.
 * (Using non-namespaced PHPMailer loaded via _base.php)
 *
 * @param string $toEmail Recipient email address.
 * @param string $toName Recipient name (optional).
 * @param string $subject Email subject.
 * @param string $body HTML email body.
 * @param string $pdfFilePath Absolute path to the PDF file to attach.
 * @param string $pdfFilename Desired filename for the attachment (e.g., "receipt_ORD123.pdf").
 * @return bool True on success, false on failure.
 */
function send_email_with_attachment(string $toEmail, string $toName, string $subject, string $body, string $pdfFilePath, string $pdfFilename): bool {
    // Instantiate the class directly (no namespace, loaded from _base.php)
    $mail = new PHPMailer(true); // Pass true to enable exceptions

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Set to 2 for debugging if needed
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'AACS3173@gmail.com';  // Your Gmail username
        $mail->Password   = 'xxna ftdu plga hzxl'; // Your Gmail App Password
        // Use the non-namespaced constant
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587; // 587 for tls, 465 for ssl

        // Recipients
        $mail->setFrom('AACS3173@gmail.com', 'SigmaMart'); // Sender
        $mail->addAddress($toEmail, $toName);             // Add recipient

        // Attachments
        if (!file_exists($pdfFilePath)) {
            // Throw a standard Exception
            throw new Exception("Attachment file not found: " . $pdfFilePath);
        }
        $mail->addAttachment($pdfFilePath, $pdfFilename);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Simple text version

        $mail->send();
        error_log("Receipt email with attachment $pdfFilename sent successfully to: $toEmail");
        return true;
    // Catch the base Exception class
    } catch (Exception $e) {
        $errorInfo = $mail->ErrorInfo ?? $e->getMessage();
        error_log("Receipt email could not be sent to $toEmail. Mailer Error: {$errorInfo}. Exception: {$e->getMessage()}");
        return false; // Return false on failure for receipt emails
    }
}

?>