<?php
require '_base.php';
require 'config_stripe.php';

// Check if payment intent ID is in the URL
if (!isset($_GET['payment_intent']) || empty($_GET['payment_intent'])) {
    header('Location: profile.php?tab=orders');
    exit;
}

$paymentIntentId = $_GET['payment_intent'];
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

try {
    // Retrieve the payment intent from Stripe
    $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
    
    if ($paymentIntent->status === 'succeeded') {
        // Payment was successful
        
        // Get the order ID from the metadata if not provided in URL
        if (!$orderId && isset($paymentIntent->metadata->order_id)) {
            $orderId = (int)$paymentIntent->metadata->order_id;
        }
        
        if ($orderId) {
            // Update order status in database
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET status = 'Paid', 
                    payment_id = ?, 
                    payment_method = 'stripe',
                    payment_date = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$paymentIntentId, $orderId]);
            
            // Log the payment
            $stmt = $pdo->prepare("
                INSERT INTO payment_logs (txn_id, order_reference, payment_amount, payment_currency, 
                                         payer_email, payment_status, ipn_data)
                SELECT ?, order_reference, total_amount, 'myr', ?, 'succeeded', ?
                FROM orders WHERE id = ?
            ");
            $stmt->execute([
                $paymentIntentId, 
                $_SESSION['email'] ?? 'unknown', 
                json_encode(['payment_intent' => $paymentIntentId, 'status' => 'succeeded']),
                $orderId
            ]);
            
            // Clear the cart if payment was successful
            if (isset($_SESSION['user_id'])) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE UserID = ?");
                $stmt->execute([$_SESSION['user_id']]);
            }
            
            // Set success message
            $_SESSION['payment_success'] = true;
            
            // Debug log
            error_log("Stripe payment successful for order: $orderId");
            
            // Redirect to send receipt handler
            header("Location: sendReceipt.php?order_id=" . $orderId . "&method=stripe");
            exit;
        }
    } else {
        // Payment failed or is still processing
        $_SESSION['payment_error'] = "Payment status: " . $paymentIntent->status;
    }
} catch (Exception $e) {
    $_SESSION['payment_error'] = "Error processing payment: " . $e->getMessage();
}

// If we get here, something went wrong
if ($orderId) {
    header("Location: order_details.php?id=$orderId");
} else {
    header('Location: profile.php?tab=orders');
}
exit;
?>