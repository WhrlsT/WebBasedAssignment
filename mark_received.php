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

    // Only allow marking as received if the status is 'Shipped'
    if (strtolower($order['status']) === 'shipped') {
        // Update the order status to 'Delivered'
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'Delivered' WHERE id = ?");
        $updateStmt->execute([$orderId]);

        // Redirect back to the order details page with a success message (optional)
        header("Location: order_details.php?id=$orderId&status_updated=received");
        exit;
    } else {
        // Status is not 'Shipped', redirect back with an error
        header("Location: order_details.php?id=$orderId&error=cannot_mark_received");
        exit;
    }

} catch (PDOException $e) {
    // Handle database errors - log the error and redirect with a generic message
    error_log("Database error in mark_received.php: " . $e->getMessage());
    header("Location: order_details.php?id=$orderId&error=db_error");
    exit;
}

// Fallback redirect in case something unexpected happens
header("Location: order_details.php?id=$orderId");
exit;
?>