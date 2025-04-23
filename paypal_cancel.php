<?php
require '_base.php';

$_title = 'Payment Canceled';
include '_head.php';

// Clear the pending order from session if it exists
if (isset($_SESSION['pending_order'])) {
    unset($_SESSION['pending_order']);
}
?>

<div class="order-cancel-container">
    <div class="order-cancel">
        <img src="image/order-cancel.png" alt="Order Canceled">
        <h1>Payment Canceled</h1>
        <p>Your order has been canceled and no payment has been processed.</p>
        <div class="order-actions">
            <a href="cart.php" class="back-to-cart-btn">Back to Cart</a>
            <a href="product.php" class="continue-shopping-btn">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>