<?php
require '_base.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: profile.php?tab=orders');
    exit;
}

$orderId = (int)$_GET['id'];

// First, check if the shipping_addresses table has the expected columns
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM shipping_addresses");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build a dynamic query based on available columns
    $selectFields = "o.*";
    $availableColumns = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code'];
    
    foreach ($availableColumns as $column) {
        if (in_array($column, $columns)) {
            $selectFields .= ", sa.$column";
        }
    }
    
    // Get order details with shipping address
    $stmt = $pdo->prepare("
        SELECT $selectFields
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.id = sa.order_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Redirect if order not found or doesn't belong to the user
    if (!$order) {
        header('Location: profile.php?tab=orders');
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.ProductName, 
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
    
} catch (PDOException $e) {
    // Handle database error
    $error = "Database error: " . $e->getMessage();
}

$_title = 'Order Details - ' . $order['order_reference'];
include '_head.php';
?>

<div class="order-details-container">
    <div class="order-details-header">
        <h1>Order Details</h1>
        <a href="profile.php?tab=orders" class="back-to-orders"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <!-- Order Status Tracker -->
        <div class="order-status-tracker">
            <?php
            $statuses = ['Order Placed', 'Order Paid', 'Order Shipped Out', 'Order Received'];
            $currentStatus = $order['status'];
            $statusIndex = 0;
            
            // Determine current status index
            if ($currentStatus == 'Pending') {
                $statusIndex = 0;
            } elseif ($currentStatus == 'Paid') {
                $statusIndex = 1;
            } elseif ($currentStatus == 'Shipped') {
                $statusIndex = 2;
            } elseif ($currentStatus == 'Delivered') {
                $statusIndex = 3;
            }
            ?>
            
            <div class="status-timeline">
                <?php foreach ($statuses as $index => $status): ?>
                    <div class="status-step <?php echo $index <= $statusIndex ? 'active' : ''; ?>">
                        <div class="status-icon">
                            <?php if ($index == 0): ?>
                                <i class="fas fa-clipboard-list"></i>
                            <?php elseif ($index == 1): ?>
                                <i class="fas fa-money-bill-wave"></i>
                            <?php elseif ($index == 2): ?>
                                <i class="fas fa-truck"></i>
                            <?php else: ?>
                                <i class="fas fa-box-open"></i>
                            <?php endif; ?>
                        </div>
                        <div class="status-label"><?php echo $status; ?></div>
                        <?php if ($index == 1 && $order['payment_id']): ?>
                            <div class="status-detail">(RM<?php echo number_format($order['total_amount'], 2); ?>)</div>
                        <?php endif; ?>
                        <?php if ($index == 0 || $index <= $statusIndex): ?>
                            <div class="status-date">
                                <?php 
                                if ($index == 0) {
                                    echo date('d-m-Y H:i', strtotime($order['order_date']));
                                } elseif ($index == 1 && $order['payment_date']) {
                                    echo date('d-m-Y H:i', strtotime($order['payment_date']));
                                } elseif ($index == 2 && $order['status'] == 'Shipped') {
                                    // Placeholder for shipped date - you might want to add this to your database
                                    echo date('d-m-Y H:i', strtotime('+1 day', strtotime($order['payment_date'])));
                                } elseif ($index == 3 && $order['status'] == 'Delivered') {
                                    // Placeholder for delivery date - you might want to add this to your database
                                    echo date('d-m-Y H:i', strtotime('+3 days', strtotime($order['payment_date'])));
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($index < count($statuses) - 1): ?>
                        <div class="status-connector <?php echo $index < $statusIndex ? 'active' : ''; ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="order-details-grid">
            <div class="order-info-section">
                <div class="details-order-card">
                    <h2>Order Information</h2>
                    <div class="order-info-row">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['order_reference']); ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo ucfirst(htmlspecialchars($order['payment_method'] ?? 'N/A')); ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="info-label">Order Status:</span>
                        <span class="info-value status-badge <?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                    </div>
                </div>
                
                <div class="details-order-card">
                    <h2>Shipping Address</h2>
                    <?php if (isset($order['first_name'])): ?>
                        <div class="address-details">
                            <p><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                            <p><?php echo htmlspecialchars($order['address']); ?></p>
                            <p><?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['zip_code']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                            <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                        </div>
                        
                        <?php if (!empty($order['tracking_number'])): ?>
                        <div class="shipping-info">
                            <div class="shipping-info-row">
                                <span class="shipping-label">Tracking Number:</span>
                                <span class="tracking-number"><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['admin_notes'])): ?>
                        <div class="admin-notes">
                            <span class="admin-notes-label">Notes from Seller:</span>
                            <div class="admin-notes-content"><?php echo nl2br(htmlspecialchars($order['admin_notes'])); ?></div>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <p>No shipping address information available.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="order-items-section">
                <div class="details-order-card">
                    <h2>Order Items</h2>
                    <div class="order-items-list">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <?php if ($item['ImagePath']): ?>
                                        <img src="<?php echo htmlspecialchars($item['ImagePath']); ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                                    <div class="item-meta">
                                        <span class="item-price">RM<?php echo number_format($item['price'], 2); ?></span>
                                        <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                </div>
                                <div class="item-total">
                                    <span>RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="details-order-card">
                    <div class="order-summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value">RM<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></span>
                    </div>
                    <div class="order-summary-row">
                        <span class="summary-label">Shipping:</span>
                        <span class="summary-value">RM<?php echo number_format($order['shipping_cost'], 2); ?></span>
                    </div>
                    <div class="order-summary-row total">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value">RM<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '_foot.php'; ?>