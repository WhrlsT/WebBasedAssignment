<?php
require '../_base.php';

// Check if user is admin
if (!$loggedIn || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int)$_GET['id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.Username, u.Email as UserEmail, sa.first_name, sa.last_name, sa.email, sa.phone, sa.address, sa.city, sa.state, sa.zip_code
    FROM orders o
    JOIN users u ON o.user_id = u.UserID
    LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order not found
if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.ProductName, p.ProductID,
    (SELECT picturePath FROM productpictures WHERE ProductID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath
    FROM order_items oi
    JOIN products p ON oi.product_id = p.ProductID
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment logs
$stmt = $pdo->prepare("
    SELECT * FROM payment_logs 
    WHERE order_reference = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$order['order_reference']]);
$paymentLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_title = 'Admin - Order Details';
include '../_head.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Order Details</h1>
            <div class="admin-actions">
                <a href="orders.php" class="back-btn">Back to Orders</a>
                <a href="update_order.php?id=<?php echo $order['id']; ?>" class="edit-btn">Update Order</a>
            </div>
        </div>
        
        <div class="order-details-grid">
            <div class="order-info-card">
                <h2>Order Information</h2>
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['order_reference']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value"><?php echo date('F j, Y H:i:s', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value status-badge <?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment ID:</span>
                    <span class="info-value"><?php echo $order['payment_id'] ? htmlspecialchars($order['payment_id']) : 'N/A'; ?></span>
                </div>
            </div>
            
            <div class="order-info-card">
                <h2>Customer Information</h2>
                <div class="info-row">
                    <span class="info-label">Username:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['Username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['UserEmail']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer ID:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['user_id']); ?></span>
                </div>
            </div>
            
            <div class="order-info-card">
                <h2>Shipping Address</h2>
                <?php if ($order['first_name']): ?>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['address']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">City:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['city']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">State:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['state']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Zip Code:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['zip_code']); ?></span>
                    </div>
                <?php else: ?>
                    <p>No shipping address information available.</p>
                <?php endif; ?>
            </div>
            
            <div class="order-info-card">
                <h2>Payment Information</h2>
                <?php if (!empty($paymentLogs)): ?>
                    <?php foreach ($paymentLogs as $index => $log): ?>
                        <?php if ($index === 0): ?>
                            <div class="info-row">
                                <span class="info-label">Transaction ID:</span>
                                <span class="info-value"><?php echo htmlspecialchars($log['txn_id'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Amount:</span>
                                <span class="info-value"><?php echo isset($log['payment_amount']) ? 'RM' . number_format($log['payment_amount'], 2) : 'N/A'; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Currency:</span>
                                <span class="info-value"><?php echo htmlspecialchars($log['payment_currency'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Payer Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($log['payer_email'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status:</span>
                                <span class="info-value"><?php echo htmlspecialchars($log['payment_status']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Date:</span>
                                <span class="info-value"><?php echo date('F j, Y H:i:s', strtotime($log['created_at'])); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No payment information available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="order-items-section">
            <h2>Order Items</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Product ID</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <div class="item-info">
                                <div class="item-image">
                                    <?php if (!empty($item['ImagePath'])): ?>
                                        <img src="/<?php echo htmlspecialchars($item['ImagePath']); ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                                    <?php else: ?>
                                        <img src="../image/product-placeholder.jpg" alt="No Image">
                                    <?php endif; ?>
                                </div>
                                <div class="item-name">
                                    <?php echo htmlspecialchars($item['ProductName']); ?>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $item['ProductID']; ?></td>
                        <td>RM<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right">Subtotal:</td>
                        <td>RM<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right">Shipping:</td>
                        <td>RM<?php echo number_format($order['shipping_cost'], 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" class="text-right">Total:</td>
                        <td>RM<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <?php if (!empty($paymentLogs) && count($paymentLogs) > 1): ?>
        <div class="payment-logs-section">
            <h2>Payment History</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paymentLogs as $log): ?>
                    <tr>
                        <td><?php echo date('M j, Y H:i:s', strtotime($log['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($log['txn_id'] ?? 'N/A'); ?></td>
                        <td><?php echo isset($log['payment_amount']) ? 'RM' . number_format($log['payment_amount'], 2) : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($log['payment_status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../_foot.php'; ?>

<style>
.admin-content {
    padding: 15px;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.order-info-card {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 15px;
    margin-bottom: 0;
}

.order-info-card h2 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 18px;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
}

/* Add vertical scrolling to order items section */
.order-items-section {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 15px;
    margin-top: 0;
}

.order-items-section h2 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 18px;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
}

/* Create a scrollable container just for the tbody */
.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table tbody {
    display: block;
    max-height: 300px;
    overflow-y: auto;
}

.admin-table thead, 
.admin-table tfoot {
    display: table;
    width: 100%;
    table-layout: fixed;
}

.admin-table thead tr, 
.admin-table tbody tr, 
.admin-table tfoot tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

/* Style the scrollbar for better appearance */
.admin-table tbody::-webkit-scrollbar {
    width: 8px;
}

.admin-table tbody::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.admin-table tbody::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.admin-table tbody::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Ensure table cells have consistent widths */
.admin-table th:nth-child(1),
.admin-table td:nth-child(1) {
    width: 40%;
}

.admin-table th:nth-child(2),
.admin-table td:nth-child(2),
.admin-table th:nth-child(3),
.admin-table td:nth-child(3),
.admin-table th:nth-child(4),
.admin-table td:nth-child(4),
.admin-table th:nth-child(5),
.admin-table td:nth-child(5) {
    width: 15%;
}

/* Make table headers sticky */
.admin-table thead th {
    position: sticky;
    top: 43px; /* Adjust based on your h2 height */
    background-color: #f5f5f5;
    z-index: 5;
}

/* Remove any extra space in the main content area */
main {
    padding: 0;
}

/* Ensure consistent spacing */
.admin-header {
    margin-bottom: 15px;
}

/* Add the same scrolling behavior to payment logs if needed */
.payment-logs-section {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 15px;
    margin-top: 15px;
    max-height: 300px; /* Smaller max height for payment logs */
    overflow-y: auto;
}

.payment-logs-section h2 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 18px;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 10;
}
</style>