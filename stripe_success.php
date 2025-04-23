<?php
require '_base.php';
require 'config_stripe.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if session ID is provided
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    header('Location: cart.php');
    exit;
}

$sessionId = $_GET['session_id'];

try {
    // Retrieve the session
    $session = $stripe->checkout->sessions->retrieve($sessionId);
    
    // Check if payment was successful
    if ($session->payment_status === 'paid') {
        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'Paid', 
                payment_id = ?, 
                payment_method = 'stripe',
                payment_date = NOW()
            WHERE payment_session_id = ?
        ");
        $stmt->execute([$session->payment_intent, $sessionId]);
        
        // Get the order ID
        $stmt = $pdo->prepare("
            SELECT id, user_id, order_reference FROM orders WHERE payment_session_id = ?
        ");
        $stmt->execute([$sessionId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['user_id'] == $_SESSION['user_id']) {
            $orderId = $order['id'];
            
            // Log the payment in payment_logs table
            $paymentData = json_encode($session);
            $stmt = $pdo->prepare("
                INSERT INTO payment_logs 
                (txn_id, order_reference, payment_amount, payment_currency, 
                payer_email, payment_status, ipn_data) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $session->payment_intent,
                $order['order_reference'],
                $session->amount_total / 100, // Convert from cents
                strtoupper($session->currency),
                $_SESSION['email'] ?? 'unknown',
                'Completed',
                $paymentData
            ]);
            
            // Clear the user's cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE UserID = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Redirect to order details page
            header("Location: order_details.php?id=" . $orderId);
            exit;
        }
    } else {
        // Payment not completed
        header('Location: checkout.php?error=payment_failed');
        exit;
    }
} catch (Exception $e) {
    // Log error
    error_log('Stripe Error: ' . $e->getMessage());
    
    // Redirect to profile
    header('Location: profile.php?error=payment_verification_failed');
    exit;
}