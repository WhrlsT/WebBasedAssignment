<?php
require '_base.php'; // Includes DB, session
require 'config_stripe.php'; // Includes Stripe PHP library and sets API key

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Get Stripe session ID from URL
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    // Redirect if session ID is missing
    header('Location: cart.php?error=stripe_session_missing');
    exit;
}

$orderId = null;
$stripePaymentIntentId = null;

try {
    // Retrieve the Stripe Checkout Session to verify payment and get metadata
    $checkout_session = \Stripe\Checkout\Session::retrieve($session_id, [
        'expand' => ['payment_intent', 'line_items.data.price.product']
    ]);

    // Check if payment was successful
    if ($checkout_session->payment_status !== 'paid') {
        header('Location: checkout.php?error=stripe_payment_failed');
        exit;
    }

    // Get Order ID from metadata
    if (!isset($checkout_session->metadata['order_id']) || !is_numeric($checkout_session->metadata['order_id'])) {
         throw new Exception("Order ID missing from Stripe session metadata.");
    }
    $orderId = (int)$checkout_session->metadata['order_id'];
    $stripePaymentIntentId = $checkout_session->payment_intent->id ?? null;

    // --- Start Database Transaction ---
    $pdo->beginTransaction();

    // --- Fetch Order, Shipping, and User Details ---
    $stmt = $pdo->prepare("
        SELECT
            o.*,
            sa.*,
            u.FirstName as UserFirstName,
            u.LastName as UserLastName,
            u.Email as UserEmail
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
        header('Location: my_orders.php?error=not_found');
        exit;
    }

    // --- Update Order Status (Only if not already Paid) ---
    $paymentProcessed = false;
    if ($order['status'] !== 'Paid') {
        $updateStmt = $pdo->prepare("
            UPDATE orders
            SET status = 'Paid',
                payment_id = ?,
                payment_method = 'stripe',
                payment_date = NOW()
            WHERE id = ? AND user_id = ? AND status != 'Paid'
        ");
        $paymentIdToStore = $stripePaymentIntentId ?? $session_id;
        $updateStmt->execute([$paymentIdToStore, $orderId, $userId]);

        if ($updateStmt->rowCount() > 0) {
             $paymentProcessed = true;
             // Clear cart
             $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE UserID = ?");
             $clearCartStmt->execute([$userId]);
        }
    }

    // --- Commit Transaction ---
    $pdo->commit();

    // Verify session is still active
    session_write_close(); // Ensure session is saved
    session_start(); // Restart session for redirect

    // Debug log the redirect
    error_log("Redirecting to sendReceipt.php for order: $orderId");

    // Ensure no output has been sent before header()
    if (headers_sent()) {
        die("Redirect failed. Please click this link: <a href='sendReceipt.php?order_id=$orderId&method=stripe'>Continue</a>");
    }

    // Redirect to send receipt handler
    header("Location: sendReceipt.php?order_id=" . $orderId . "&method=stripe");
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: checkout.php?error=stripe_api_error');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: checkout.php?error=db_error');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: checkout.php?error=processing_error');
    exit;
}
