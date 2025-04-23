<?php
require '_base.php';
require 'config_stripe.php';
// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$orderId = null;
$order = null;
$orderItems = [];
$shippingAddress = null;
$subtotal = 0;
$shippingCost = 10.00; // Default shipping cost
$total = 0;

// Check if order ID is provided (for returning to an existing order)
if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    
    // Get order details
    $stmt = $pdo->prepare("
        SELECT * FROM orders
        WHERE id = ? AND user_id = ? AND status = 'Pending'
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Redirect if order not found, doesn't belong to user, or is not pending
    if (!$order) {
        header('Location: cart.php');
        exit;
    }
    
    // Get order items with product details
    $stmt = $pdo->prepare("
        SELECT oi.*, p.ProductName, p.ProductPrice,
        (SELECT picturePath FROM productpictures WHERE ProductID = p.ProductID AND isCover = 1 LIMIT 1) as ProductImage
        FROM order_items oi
        JOIN products p ON oi.product_id = p.ProductID
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    foreach ($orderItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $total = $order['total_amount'];
    $shippingCost = $total - $subtotal;
    
} else {
    // No order ID provided, get cart items
    $stmt = $pdo->prepare("
        SELECT c.*, p.ProductName, p.ProductPrice, 
        (SELECT picturePath FROM productpictures WHERE ProductID = p.ProductID AND isCover = 1 LIMIT 1) as ProductImage
        FROM cart c
        JOIN products p ON c.ProductID = p.ProductID
        WHERE c.UserID = ?
        ORDER BY c.DateAdded DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orderItems)) {
        // Cart is empty, redirect to cart page
        header('Location: cart.php?error=empty_cart');
        exit;
    }
    
    // Calculate totals
    foreach ($orderItems as $item) {
        $subtotal += $item['ProductPrice'] * $item['Quantity'];
    }
    $total = $subtotal + $shippingCost;
}

// Handle form submission to create order and process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Get form data
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zipCode = $_POST['zip_code'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    // Validate form data
    $errors = [];
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($state)) $errors[] = "State is required";
    if (empty($zipCode)) $errors[] = "ZIP code is required";
    if (empty($paymentMethod)) $errors[] = "Payment method is required";
    
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            if (!$orderId) {
                // Generate order reference
                $orderReference = 'ORD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                
                // Create new order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, shipping_cost, status, order_date, 
                    payment_method, order_reference)
                    VALUES (?, ?, ?, 'Pending', NOW(), ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'], 
                    $total,
                    $shippingCost,
                    $paymentMethod,
                    $orderReference
                ]);
                $orderId = $pdo->lastInsertId();
                
                // Add shipping address
                $stmt = $pdo->prepare("
                    INSERT INTO shipping_addresses (user_id, order_id, first_name, last_name, 
                    email, phone, address, city, state, zip_code)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $orderId,
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $address,
                    $city,
                    $state,
                    $zipCode
                ]);
                
                // Add order items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($orderItems as $item) {
                    $productId = $item['ProductID'] ?? $item['product_id'];
                    $quantity = $item['Quantity'] ?? $item['quantity'];
                    $price = $item['ProductPrice'] ?? $item['price'];
                    
                    $stmt->execute([
                        $orderId,
                        $productId,
                        $quantity,
                        $price
                    ]);
                }
                
                // Clear cart
                $stmt = $pdo->prepare("DELETE FROM cart WHERE UserID = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                // Update existing order
                $stmt = $pdo->prepare("
                    UPDATE orders SET 
                    payment_method = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $paymentMethod,
                    $orderId,
                    $_SESSION['user_id']
                ]);
                
                // Update shipping address
                $stmt = $pdo->prepare("
                    UPDATE shipping_addresses SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    address = ?, 
                    city = ?, 
                    state = ?, 
                    zip_code = ?
                    WHERE order_id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $address,
                    $city,
                    $state,
                    $zipCode,
                    $orderId,
                    $_SESSION['user_id']
                ]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Process payment based on payment method
            if ($paymentMethod === 'stripe') {
                // Store order ID in session for better error handling
                $_SESSION['pending_order_id'] = $orderId;
                // Redirect to Stripe checkout
                header("Location: process_stripe.php?order_id=$orderId");
                exit;
            } else if ($paymentMethod === 'paypal') {
                // Store order ID in session for better error handling
                $_SESSION['pending_order_id'] = $orderId;
                // Redirect to PayPal checkout
                header("Location: process_paypal.php?order_id=$orderId");
                exit;
            } 
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $errorMessage = $e->getMessage();
            error_log("Error processing order: " . $errorMessage);
            // For debugging only - comment this out in production
            // $errors[] = "An error occurred while processing your order. Please try again. (Error: " . $errorMessage . ")";
            $errors[] = "An error occurred while processing your order. Please try again.";
        }
    }
}

// Set page title and include header
$_title = 'Checkout';
include '_head.php';
?>

<!-- Add this style section after the header -->
<style>
    .payment-methods {
        margin: 20px 0;
    }
    
    .payment-method {
        margin-bottom: 15px;
        position: relative;
        cursor: pointer;
    }
    
    .payment-method input[type="radio"] {
        position: absolute;
        opacity: 0;
    }
    
    .payment-label {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
    }
    
    .payment-method:hover .payment-label {
        background-color: #fffbeb;
        border-color: #ffd700;
    }
    
    .payment-method input[type="radio"]:checked + .payment-label {
        background-color: #fffbeb;
        border-color: #ffd700;
        box-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
    }
    
    .payment-label img {
        vertical-align: middle;
        border-radius: 4px;
    }
    
    .payment-label span {
        font-weight: 500;
    }
    
    /* New button styles */
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .back-to-cart-btn {
        padding: 12px 24px;
        background-color: #f8f9fa;
        color: #495057;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .back-to-cart-btn:hover {
        background-color: #e9ecef;
        color: #212529;
    }
    
    .back-to-cart-btn:before {
        content: "←";
        margin-right: 8px;
    }
    
    .place-order-btn {
        padding: 12px 24px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .place-order-btn:hover {
        background-color: #45a049;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .place-order-btn:after {
        content: "→";
        margin-left: 8px;
    }
</style>

<div class="checkout-container">
    <h1 class="page-title">Checkout</h1>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="checkout-content">
        <div class="checkout-form">
            <h2>Shipping Information</h2>
            <form method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" required>
                    </div>
                    <div class="form-group">
                        <label for="zip_code">ZIP Code</label>
                        <input type="text" id="zip_code" name="zip_code" required>
                    </div>
                </div>
                
                <h2>Payment Method</h2>
                <div class="payment-methods">
                    <div class="payment-method" onclick="document.getElementById('stripe').click()">
                        <input type="radio" id="stripe" name="payment_method" value="stripe" checked>
                        <label for="stripe" class="payment-label">
                            <img src="image/stripe_logo.png" alt="Stripe" width="50" height="50">
                            <span>Credit Card (Stripe)</span>
                        </label>
                    </div>
                    <div class="payment-method" onclick="document.getElementById('paypal').click()">
                        <input type="radio" id="paypal" name="payment_method" value="paypal">
                        <label for="paypal" class="payment-label">
                            <img src="image/paypal_logo.png" alt="PayPal" width="50" height="50">
                            <span>PayPal</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="cart.php" class="back-to-cart-btn">Back to Cart</a>
                    <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
                </div>
            </form>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($orderItems as $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <?php if (!empty($item['ProductImage'])): ?>
                            <img src="<?php echo htmlspecialchars($item['ProductImage']); ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                        <?php else: ?>
                            <div class="no-image">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="item-details">
                        <h3 class="item-name"><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                        <div class="item-price">
                            RM<?php echo number_format($item['ProductPrice'] ?? $item['price'], 2); ?> × 
                            <?php echo $item['Quantity'] ?? $item['quantity']; ?>
                        </div>
                    </div>
                    <div class="item-total">
                        RM<?php echo number_format(($item['ProductPrice'] ?? $item['price']) * ($item['Quantity'] ?? $item['quantity']), 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>RM<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Shipping:</span>
                    <span>RM<?php echo number_format($shippingCost, 2); ?></span>
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span>RM<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>