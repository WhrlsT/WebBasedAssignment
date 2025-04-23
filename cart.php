<?php
require '_base.php';

$_title = 'Shopping Cart';
include '_head.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php?redirect=cart');
    exit;
}

// Process cart updates if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        // Update quantities
        foreach ($_POST['quantity'] as $cartId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET Quantity = ? WHERE CartID = ? AND UserID = ?");
                $stmt->execute([$quantity, $cartId, $_SESSION['user_id']]);
            } else {
                // Remove item if quantity is 0
                $stmt = $pdo->prepare("DELETE FROM cart WHERE CartID = ? AND UserID = ?");
                $stmt->execute([$cartId, $_SESSION['user_id']]);
            }
        }
        
        // Redirect to prevent form resubmission
        header('Location: cart.php?updated=1');
        exit;
    } elseif (isset($_POST['remove_item']) && isset($_POST['cart_id'])) {
        // Remove specific item
        $cartId = (int)$_POST['cart_id'];
        $stmt = $pdo->prepare("DELETE FROM cart WHERE CartID = ? AND UserID = ?");
        $stmt->execute([$cartId, $_SESSION['user_id']]);
        
        // Redirect to prevent form resubmission
        header('Location: cart.php?removed=1');
        exit;
    }
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.ProductName, p.ProductPrice, 
    (SELECT picturePath FROM productpictures WHERE ProductID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath
    FROM cart c
    JOIN products p ON c.ProductID = p.ProductID
    WHERE c.UserID = ?
    ORDER BY c.DateAdded DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$itemCount = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['ProductPrice'] * $item['Quantity'];
    $itemCount += $item['Quantity'];
}

// Set shipping cost and calculate total
$shippingCost = $subtotal > 0 ? 10.00 : 0;
$total = $subtotal + $shippingCost;
?>

<div class="cart-container">
    <h1 class="page-title">Shopping Cart</h1>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Your cart has been updated successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['removed'])): ?>
        <div class="alert alert-success">Item has been removed from your cart.</div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <a href="product.php" class="continue-shopping">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th class="product-col">Product</th>
                        <th class="price-col">Price</th>
                        <th class="quantity-col">Quantity</th>
                        <th class="subtotal-col">Subtotal</th>
                        <th class="actions-col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="cart-product-col">
                                <div class="cart-product-info">
                                    <div class="cart-product-image">
                                        <?php if (!empty($item['ImagePath'])): ?>
                                            <img src="<?php echo $item['ImagePath']; ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                                        <?php else: ?>
                                            <img src="image/product-placeholder.jpg" alt="No Image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-product-details">
                                        <h3><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                                    </div>
                                </div>
                            </td>
                            <td class="cart-price-col">
                                RM<?php echo number_format($item['ProductPrice'], 2); ?>
                            </td>
                            <td class="cart-quantity-col">
                                <input type="number" 
                                       name="quantity" 
                                       data-cart-id="<?php echo $item['CartID']; ?>" 
                                       value="<?php echo $item['Quantity']; ?>" 
                                       min="0" 
                                       class="quantity-input">
                            </td>
                            <td class="subtotal-col item-subtotal" data-price="<?php echo $item['ProductPrice']; ?>">
                                RM<?php echo number_format($item['ProductPrice'] * $item['Quantity'], 2); ?>
                            </td>
                            <td class="actions-col">
                                <button type="button" class="remove-button" data-cart-id="<?php echo $item['CartID']; ?>">×</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="cart-actions">
            <div class="cart-buttons">
                <a href="product.php" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
        
        <div class="cart-summary">
            <h2>Cart Summary</h2>
            <div class="summary-row">
                <span class="summary-label">Subtotal (<span id="item-count"><?php echo $itemCount; ?></span> items):</span>
                <span class="summary-value" id="cart-subtotal">RM<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Shipping:</span>
                <span class="summary-value" id="shipping-cost">RM<?php echo number_format($shippingCost, 2); ?></span>
            </div>
            <div class="summary-row total">
                <span class="summary-label">Total:</span>
                <span class="summary-value" id="cart-total">RM<?php echo number_format($total, 2); ?></span>
            </div>
            <!-- Update the checkout button to go directly to checkout.php -->
            <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<!-- Add the cart popup HTML structure -->
<div id="cart-popup-overlay" class="cart-popup-overlay" style="display: none;"></div>
<div id="cart-popup" class="cart-popup" style="display: none;">
    <div class="cart-popup-header">
        <h3>Added to Cart</h3>
        <button class="cart-popup-close" onclick="closeCartPopup()">&times;</button>
    </div>
    <div class="cart-popup-content">
        <div class="cart-popup-item">
            <div class="cart-popup-item-row">
                <span class="cart-popup-label">Item Name:</span>
                <span class="cart-popup-value" id="popup-product-name"></span>
            </div>
            <div class="cart-popup-item-row">
                <span class="cart-popup-label">Quantity:</span>
                <span class="cart-popup-value" id="popup-quantity"></span>
            </div>
            <div class="cart-popup-item-row">
                <span class="cart-popup-label">Price:</span>
                <span class="cart-popup-value" id="popup-price"></span>
            </div>
        </div>
    </div>
    <div class="cart-popup-actions">
        <a href="javascript:void(0)" class="cart-popup-btn cart-popup-continue" onclick="closeCartPopup()">Continue Shopping</a>
        <a href="cart.php" class="cart-popup-btn cart-popup-cart">Go to Cart</a>
    </div>
</div>

<!-- Include the cart.js file which contains the popup functions -->
<script src="js/cart.js"></script>

<?php include '_foot.php'; ?>