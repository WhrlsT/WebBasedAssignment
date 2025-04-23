<?php
require '_base.php';
require 'config_stripe.php';
// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: profile.php?tab=orders');
    exit;
}

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
    header('Location: profile.php?tab=orders');
    exit;
}

// Check if product images are stored in productpictures table
try {
    // Get order items with product details
    $stmt = $pdo->prepare("
        SELECT oi.*, p.ProductName
        FROM order_items oi
        JOIN products p ON oi.product_id = p.ProductID
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product images for each item
    foreach ($orderItems as $key => $item) {
        $stmt = $pdo->prepare("
            SELECT picturePath FROM productpictures 
            WHERE productID = ? AND isCover = 1 
            LIMIT 1
        ");
        $stmt->execute([$item['product_id']]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        $orderItems[$key]['ProductImage'] = $image ? $image['picturePath'] : null;
    }
} catch (PDOException $e) {
    // Fallback to just getting order items without product details
    $stmt = $pdo->prepare("
        SELECT * FROM order_items
        WHERE order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get shipping address if available
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM shipping_addresses");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build a dynamic query based on available columns
    $selectFields = "";
    $availableColumns = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code'];
    
    foreach ($availableColumns as $column) {
        if (in_array($column, $columns)) {
            if (!empty($selectFields)) {
                $selectFields .= ", ";
            }
            $selectFields .= $column;
        }
    }
    
    if (!empty($selectFields)) {
        $stmt = $pdo->prepare("
            SELECT $selectFields
            FROM shipping_addresses
            WHERE order_id = ?
        ");
        $stmt->execute([$orderId]);
        $shippingAddress = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $shippingAddress = null;
    }
} catch (PDOException $e) {
    $shippingAddress = null;
}

// Create a payment intent
try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $order['total_amount'] * 100, // Amount in cents
        'currency' => 'myr',
        'metadata' => [
            'order_id' => $order['id'],
            'order_reference' => $order['order_reference']
        ],
    ]);
    
    $clientSecret = $paymentIntent->client_secret;
} catch (Exception $e) {
    $_SESSION['error'] = 'Error creating payment: ' . $e->getMessage();
    header('Location: order_details.php?id=' . $orderId);
    exit;
}

// Remove header/footer includes and create a standalone HTML document
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment</title>
    <link rel="stylesheet" href="css/process_stripe.css">
    <!-- Include any other necessary CSS -->
    <link href='https://fonts.googleapis.com/css?family=Varela Round' rel='stylesheet'>
</head>
<body>
    <div class="stripe-payment-container">
        <div class="payment-header">
            <h1>Complete Your Payment</h1>
            <div class="navigation-buttons">
                <a href="order_details.php?id=<?php echo $orderId; ?>" class="back-to-order">Back to Order</a>
                <a href="index.php" class="back-to-home">Back to Home</a>
            </div>
        </div>
        
        <div class="payment-content">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['order_reference']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value">RM<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Items</h3>
                    <ul class="item-list">
                        <?php foreach ($orderItems as $item): ?>
                        <li class="item">
                            <div class="item-image">
                                <?php if (isset($item['ProductImage']) && $item['ProductImage']): ?>
                                <img src="<?php echo htmlspecialchars($item['ProductImage']); ?>" alt="<?php echo htmlspecialchars($item['ProductName'] ?? 'Product'); ?>">
                                <?php else: ?>
                                <div class="no-image">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['ProductName'] ?? 'Product #' . $item['product_id']); ?></div>
                                <div class="item-price">RM<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-total">
                                RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>RM<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Shipping:</span>
                            <span>RM<?php echo number_format($order['shipping_cost'], 2); ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total:</span>
                            <span>RM<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="payment-form">
                <h2>Payment Details</h2>
                <form id="payment-form">
                    <div id="payment-element"></div>
                    <button id="submit-button" type="submit">
                        <div class="spinner hidden" id="spinner"></div>
                        <span id="button-text">Pay RM<?php echo number_format($order['total_amount'], 2); ?></span>
                    </button>
                    <div id="payment-message" class="hidden"></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('<?php echo $stripePublishableKey; ?>');
        const clientSecret = '<?php echo $clientSecret; ?>';
        
        const appearance = {
            theme: 'stripe',
            variables: {
                colorPrimary: '#6772e5',
            },
        };
        
        const elements = stripe.elements({
            clientSecret,
            appearance,
        });
        
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
        
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('button-text');
        const paymentMessage = document.getElementById('payment-message');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Disable the submit button to prevent repeated clicks
            setLoading(true);
            
            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    // Change the return_url to redirect to order_details.php with the order ID
                    return_url: '<?php echo "http://" . $_SERVER['HTTP_HOST']; ?>/stripe_callback.php?order_id=<?php echo $orderId; ?>',
                },
            });
            
            if (error) {
                showMessage(error.message);
                setLoading(false);
            }
        });
        
        function setLoading(isLoading) {
            if (isLoading) {
                submitButton.disabled = true;
                spinner.classList.remove('hidden');
                buttonText.classList.add('hidden');
            } else {
                submitButton.disabled = false;
                spinner.classList.add('hidden');
                buttonText.classList.remove('hidden');
            }
        }
        
        function showMessage(messageText) {
            paymentMessage.classList.remove('hidden');
            paymentMessage.textContent = messageText;
            
            setTimeout(function () {
                paymentMessage.classList.add('hidden');
                paymentMessage.textContent = '';
            }, 4000);
        }
    </script>
</body>
</html>