<?php
require '_base.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided in URL
if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    
    // Get order details from database
    $stmt = $pdo->prepare("
        SELECT o.*, sa.first_name, sa.last_name, sa.email
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Location: cart.php');
        exit;
    }
    
    // Update the order status to Paid if it's not already paid
    if ($order['status'] !== 'Paid' && isset($_SESSION['update_order_on_success'])) {
        try {
            $pdo->beginTransaction();
            
            // Generate a transaction ID if not provided by PayPal
            $txnId = 'PP_' . time() . '_' . $orderId;
            
            // Update order status
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET status = 'Paid', 
                    payment_id = ?, 
                    payment_method = 'paypal',
                    payment_date = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$txnId, $orderId, $_SESSION['user_id']]);
            
            // Log the payment in payment_logs table
            $paymentData = json_encode([
                'order_id' => $orderId,
                'payment_method' => 'paypal',
                'success_page' => true
            ]);
            
            $stmt = $pdo->prepare("
                INSERT INTO payment_logs 
                (txn_id, order_reference, payment_amount, payment_currency, 
                payer_email, payment_status, ipn_data) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $txnId,
                $order['order_reference'],
                $order['total_amount'],
                'MYR',
                $order['email'] ?? $_SESSION['email'] ?? 'unknown',
                'Completed',
                $paymentData
            ]);
            
            // Clear the user's cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE UserID = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Clear the session flags
            unset($_SESSION['update_order_on_success']);
            unset($_SESSION['pending_order']);
            
            $pdo->commit();
            
            // Redirect to order details
            header("Location: order_details.php?id=" . $orderId);
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $orderError = "There was a problem processing your order: " . $e->getMessage();
        }
    } else if ($order['status'] === 'Paid') {
        // Order is already paid, redirect to order details
        header("Location: order_details.php?id=" . $orderId);
        exit;
    }
} else {
    header('Location: cart.php');
    exit;
}

$_title = 'Payment Successful';
include '_head.php';
?>

<div class="order-success-container">
    <?php if (isset($orderError)): ?>
        <div class="order-error">
            <h1>Order Processing Error</h1>
            <p><?php echo $orderError; ?></p>
            <a href="cart.php" class="back-to-cart-btn">Back to Cart</a>
        </div>
    <?php else: ?>
        <div class="order-success">
            <img src="image/order-success.png" alt="Order Success">
            <h1>Processing Your Order...</h1>
            <p>Please wait while we finalize your payment.</p>
            <p>You will be redirected shortly.</p>
            <script>
                // Redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = "order_details.php?id=<?php echo $orderId; ?>";
                }, 3000);
            </script>
        </div>
    <?php endif; ?>
</div>

<?php include '_foot.php'; ?>