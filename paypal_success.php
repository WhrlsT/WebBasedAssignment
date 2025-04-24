<?php
require '_base.php'; // Includes DB, session, email_functions.php, vendor/autoload.php

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validate Order ID from GET parameter
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: cart.php'); // Or my_orders.php
    exit;
}
$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // --- Fetch Order, Shipping, and User Details ---
    $stmt = $pdo->prepare("
        SELECT
            o.*,
            sa.*,
            u.FirstName as UserFirstName,
            u.LastName as UserLastName,
            u.Email as UserEmail  -- <<< ADD THIS LINE
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id AND sa.user_id = o.user_id
        LEFT JOIN users u ON o.user_id = u.UserID
        WHERE o.id = ? AND o.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        header('Location: my_orders.php?error=not_found'); // Redirect if order not found/belongs to another user
        exit;
    }

    // Separate shipping data if it exists
    $shipping = null;
    if (!empty($order['address'])) { // Check if shipping address fields were fetched
         $shipping = [
            'first_name' => $order['first_name'], // Assuming column names from shipping_addresses
            'last_name' => $order['last_name'],
            'email' => $order['email'],
            'phone' => $order['phone'],
            'address' => $order['address'],
            'city' => $order['city'],
            'state' => $order['state'],
            'zip_code' => $order['zip_code'],
         ];
    }
    // Now $order['UserEmail'] should contain the user's email if found
    $customerName = $order['UserFirstName'] . ' ' . $order['UserLastName'];
    $customerEmail = $order['UserEmail'] ?? $shipping['email'] ?? $_SESSION['email']; // Get email reliably

    // --- Fetch Order Items ---
    $stmtItems = $pdo->prepare("
        SELECT oi.quantity, oi.price, p.ProductName
        FROM order_items oi
        JOIN products p ON oi.product_id = p.ProductID
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // --- Update Order Status (Example for PayPal Success) ---
    $paymentProcessed = false;
    if ($order['status'] !== 'Paid') { // Only update if not already paid
        // Generate a transaction ID if needed (replace with actual PayPal TXN ID if available)
        $txnId = $_GET['txn_id'] ?? 'PP_' . time() . '_' . $orderId; // Example

        $updateStmt = $pdo->prepare("
            UPDATE orders
            SET status = 'Paid',
                payment_id = ?,
                payment_method = 'paypal',
                payment_date = NOW()
            WHERE id = ? AND user_id = ? AND status != 'Paid'
        ");
        $updateStmt->execute([$txnId, $orderId, $userId]);

        if ($updateStmt->rowCount() > 0) {
             $paymentProcessed = true;
             $order['status'] = 'Paid'; // Update local variable for receipt
             $order['payment_id'] = $txnId; // Update local variable
             $order['payment_method'] = 'paypal'; // Update local variable

             // Log payment (optional, might be done in IPN)
             // ... your payment_logs insert code ...

             // Clear cart
             $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE UserID = ?");
             $clearCartStmt->execute([$userId]);
        }
    } else {
         $paymentProcessed = true; // Already paid, still send receipt maybe? Or redirect?
    }

    // --- Commit Transaction ---
    $pdo->commit();

 

    // --- Redirect User ---
    // Redirect to a success page or order details page
    // Redirect to send receipt handler
    header("Location: sendReceipt.php?order_id=" . $orderId . "&method=paypal");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error in paypal_success.php for Order ID $orderId: " . $e->getMessage());
    header('Location: checkout.php?error=db_error'); // Redirect on error
    exit;
} catch (Exception $e) {
    // Catch potential general exceptions (like from PHPMailer or Dompdf if not caught internally)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("General error in paypal_success.php for Order ID $orderId: " . $e->getMessage());
    header('Location: checkout.php?error=processing_error'); // Redirect on error
    exit;
}
?>