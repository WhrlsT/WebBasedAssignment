<?php
require '_base.php';

// Debug logging
error_log("sendReceipt.php accessed for order: " . ($_GET['order_id'] ?? 'null') . " via method: " . ($_GET['method'] ?? 'unknown'));

// Validate inputs
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    error_log("Invalid order ID in sendReceipt.php");
    header('Location: cart.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$paymentMethod = $_GET['method'] ?? 'unknown';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: login.php');
    exit;
}

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, sa.*, u.FirstName, u.LastName, u.Email
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        LEFT JOIN users u ON o.user_id = u.UserID
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: my_orders.php?error=not_found');
        exit;
    }

    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.ProductName 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.ProductID
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare email content
    $emailSubject = "Your Order Receipt (#" . $order['order_reference'] . ")";
    $emailBody = "<p>Dear " . htmlspecialchars($order['FirstName'] . ' ' . $order['LastName']) . ",</p>";
    $emailBody .= "<p>Thank you for your order! Your payment via " . htmlspecialchars($paymentMethod) . " was successful.</p>";
    
    // Add shipping info if available
    if (isset($_GET['shipping_address'])) {
        $emailBody .= "<p>Shipping to:<br>";
        $emailBody .= htmlspecialchars($_GET['shipping_first_name'] . ' ' . $_GET['shipping_last_name']) . "<br>";
        $emailBody .= htmlspecialchars($_GET['shipping_address']) . "<br>";
        $emailBody .= htmlspecialchars($_GET['shipping_city'] . ', ' . $_GET['shipping_state'] . ' ' . $_GET['shipping_zip']) . "</p>";
    }
    
    $emailBody .= "<p>Your order reference is: <strong>" . htmlspecialchars($order['order_reference']) . "</strong></p>";
    $emailBody .= "<p>Please find your receipt attached.</p>";
    $emailBody .= "<p>You can view your order details here: <a href='http://localhost:8000/order_details.php?id=" . $orderId . "'>View Order</a></p>";
    $emailBody .= "<p>Thank you for shopping with us!</p>";

    // Generate PDF receipt
    $invoiceDir = __DIR__ . '\\invoices\\';
    if (!is_dir($invoiceDir)) {
        mkdir($invoiceDir, 0755, true);
    }
    $pdfFilename = 'receipt_order_' . $order['order_reference'] . '.pdf';
    $pdfFilePath = $invoiceDir . $pdfFilename;

    // Generate PDF content - add shipping info as 3rd argument
    $pdfContent = generate_order_pdf_content($order, $items, $shipping);
    if ($pdfContent) {
        file_put_contents($pdfFilePath, $pdfContent);
    }

    // Send email with attachment
    $emailSent = send_email_with_attachment(
        $order['Email'],
        $order['FirstName'] . ' ' . $order['LastName'],
        $emailSubject,
        $emailBody,
        $pdfFilePath,
        $pdfFilename
    );

    // Clean up temporary PDF
    if (file_exists($pdfFilePath)) {
        unlink($pdfFilePath);
    }

    if (!$emailSent) {
        error_log("Failed to send email to: " . $order['Email']);
    }

} catch (Exception $e) {
    error_log("Receipt processing error: " . $e->getMessage());
}

// Redirect to order details
header("Location: order_details.php?id=" . $orderId . "&success=payment");
exit;