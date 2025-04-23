<?php
require '_base.php';

// Check if user is logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    header('Location: profile.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if order exists and belongs to the user
if (!$order) {
    header('Location: profile.php');
    exit;
}

$_title = 'Payment Cancelled';
include '_head.php';
?>

<div class="container">
    <div class="payment-result">
        <h1>Payment Cancelled</h1>
        <p>Your payment has been cancelled. No charges were made.</p>
        <p>Order Reference: <?php echo htmlspecialchars($order['order_reference']); ?></p>
        <div class="action-buttons">
            <a href="checkout.php" class="btn-primary">Try Again</a>
            <a href="profile.php" class="btn-secondary">Go to Profile</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>