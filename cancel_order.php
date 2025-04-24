<?php
require '_base.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if the request method is POST and order_id is set
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    // Redirect back to profile or orders list if invalid request
    header('Location: profile.php?tab=orders&error=invalid_request');
    exit;
}

$orderId = (int)$_POST['order_id'];
$userId = $_SESSION['user_id'];

try {
    // Fetch the current order status and verify ownership
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        // Order not found or doesn't belong to the user
        header("Location: profile.php?tab=orders&error=not_found");
        exit;
    }

    // Define statuses eligible for cancellation
    $cancellableStatuses = ['paid', 'processing']; // Add other statuses if needed

    // Check if the current status allows cancellation
    if (in_array(strtolower($order['status']), $cancellableStatuses)) {
        // Update the order status to 'Cancelled'
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
        $updateStmt->execute([$orderId]);

        // Redirect back to the order details page with a success message (optional)
        header("Location: order_details.php?id=$orderId&status_updated=cancelled");
        exit;
    } else {
        // Status does not allow cancellation, redirect back with an error
        header("Location: order_details.php?id=$orderId&error=cannot_cancel");
        exit;
    }

} catch (PDOException $e) {
    // Handle database errors - log the error and redirect with a generic message
    error_log("Database error in cancel_order.php: " . $e->getMessage());
    header("Location: order_details.php?id=$orderId&error=db_error");
    exit;
}

// Fallback redirect in case something unexpected happens
header("Location: order_details.php?id=$orderId");
exit;
?>