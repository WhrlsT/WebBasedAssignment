<?php
require_once '_base.php';

// Check if order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: checkout.php');
    exit;
}

$orderId = (int)$_GET['order_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, sa.first_name, sa.last_name, sa.email
    FROM orders o
    LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order not found or doesn't belong to user
if (!$order) {
    header('Location: checkout.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.ProductName
    FROM order_items oi
    JOIN products p ON oi.product_id = p.ProductID
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PayPal settings
$paypal_email = 'sb-zqwlg39357989@business.example.com'; 

// Store order information in session for retrieval after payment
$_SESSION['pending_order'] = [
    'id' => $order['id'],
    'reference' => $order['order_reference'],
    'total' => $order['total_amount'],
    'items' => $orderItems
];

// Simplify URL construction for localhost:8000
$baseUrl = 'http://localhost:8000';

// Create direct URLs without leading slashes
$return_url = $baseUrl . '/paypal_success.php?order_id=' . $orderId;
$cancel_url = $baseUrl . '/checkout.php?cancel=1';
$notify_url = $baseUrl . '/paypal_ipn.php';

// For testing, we'll also update the order status on success page
$_SESSION['update_order_on_success'] = true;

// Prepare PayPal form data
$paypal_data = array(
    'cmd' => '_cart',
    'upload' => '1',
    'business' => $paypal_email,
    'return' => $return_url,
    'cancel_return' => $cancel_url,
    'notify_url' => $notify_url,
    'currency_code' => 'MYR',
    'custom' => $order['order_reference'],
    'invoice' => $order['order_reference'],
    'charset' => 'utf-8',
    'no_shipping' => '1', // We already collected shipping info
    'no_note' => '1'
);

// Add order items to PayPal data
$i = 1;
foreach ($orderItems as $item) {
    $paypal_data['item_name_' . $i] = htmlspecialchars($item['ProductName'], ENT_QUOTES, 'UTF-8');
    $paypal_data['amount_' . $i] = number_format($item['price'], 2, '.', '');
    $paypal_data['quantity_' . $i] = $item['quantity'];
    $i++;
}

// Add shipping cost
if ($order['shipping_cost'] > 0) {
    $paypal_data['handling_cart'] = number_format($order['shipping_cost'], 2, '.', '');
}

// Build PayPal URL
$paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // Use 'https://www.paypal.com/cgi-bin/webscr' for production
$query_string = http_build_query($paypal_data);

try {
    // Check if payment_method column exists in the orders table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE 'payment_method'");
    $stmt->execute();
    $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($columnExists) {
        // Update order with payment method if the column exists
        $stmt = $pdo->prepare("UPDATE orders SET payment_method = 'paypal' WHERE id = ?");
        $stmt->execute([$orderId]);
    } else {
        // Log that the column doesn't exist
        error_log("Warning: payment_method column does not exist in orders table");
    }
    
    // Redirect to PayPal
    header('Location: ' . $paypal_url . '?' . $query_string);
    exit;
} catch (PDOException $e) {
    // Log the error
    error_log("PayPal processing error: " . $e->getMessage());
    
    // Redirect to checkout with error
    header('Location: checkout.php?error=payment_processing_error');
    exit;
}