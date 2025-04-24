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

// Get order details with customer information
$stmt = $pdo->prepare("SELECT o.*, u.Username 
                      FROM orders o
                      JOIN users u ON o.user_id = u.UserID
                      WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order not found
if (!$order) {
    header('Location: orders.php');
    exit;
}

// Get order items - Fix the column names to match your database
$itemsStmt = $pdo->prepare("SELECT oi.*, p.ProductName as name, pp.picturePath as image_url 
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.ProductID
                           LEFT JOIN productpictures pp ON p.ProductID = pp.productID AND pp.isCover = 1
                           WHERE oi.order_id = ?");
$itemsStmt->execute([$orderId]);
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $status = trim($_POST['status']);
    $trackingNumber = trim($_POST['tracking_number'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        // Check if tracking_number and admin_notes columns exist
        $checkColumnsStmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE 'tracking_number'");
        $checkColumnsStmt->execute();
        $trackingColumnExists = $checkColumnsStmt->rowCount() > 0;
        
        $checkNotesStmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE 'admin_notes'");
        $checkNotesStmt->execute();
        $notesColumnExists = $checkNotesStmt->rowCount() > 0;
        
        // If columns don't exist, alter table to add them
        if (!$trackingColumnExists || !$notesColumnExists) {
            if (!$trackingColumnExists) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100) NULL AFTER payment_method");
            }
            
            if (!$notesColumnExists) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN admin_notes TEXT NULL AFTER tracking_number");
            }
        }
        
        // Now update the order
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, tracking_number = ?, admin_notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $trackingNumber, $notes, $orderId]);
        
        // Redirect back to order details
        header("Location: order_details.php?id=$orderId&updated=1");
        exit;
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

$_title = 'Admin - Update Order';
include '../_head.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Update Order #<?php echo htmlspecialchars($order['order_reference']); ?></h1>
            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="back-btn">Back to Order Details</a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-form-container">
            <div class="order-summary">
                <div class="summary-item">
                    <span class="label">Customer:</span>
                    <span class="value"><?php echo htmlspecialchars($order['Username'] ?? 'Unknown'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('M j, Y H:i', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Amount:</span>
                    <span class="value">RM <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Current Status:</span>
                    <span class="value status-badge <?php echo strtolower($order['status']); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="order-items">
                <h3>Order Items</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td class="product-cell">
                                    <div class="product-info">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-thumbnail">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td>RM <?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <form method="post" action="update_order.php?id=<?php echo $order['id']; ?>" class="update-form">
                <h3>Update Order Status</h3>
                
                <div class="form-group">
                    <label for="order_reference">Order Reference</label>
                    <input type="text" id="order_reference" value="<?php echo htmlspecialchars($order['order_reference']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="status">Order Status</label>
                    <select name="status" id="status" required>
                        <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo $order['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Shipped" <?php echo $order['status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="Refunded" <?php echo $order['status'] === 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tracking_number">Tracking Number</label>
                    <input type="text" name="tracking_number" id="tracking_number" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="notes">Admin Notes</label>
                    <textarea name="notes" id="notes" rows="4"><?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_order" class="save-btn">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.admin-form-container {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px; /* Add margin at the bottom */
}

.order-summary {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.summary-item {
    display: flex;
    align-items: center;
}

.summary-item .label {
    font-weight: bold;
    margin-right: 10px;
    min-width: 120px;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
}

.status-badge.pending { background-color: #ffeeba; color: #856404; }
.status-badge.paid { background-color: #c3e6cb; color: #155724; }
.status-badge.processing { background-color: #b8daff; color: #004085; }
.status-badge.shipped { background-color: #d1ecf1; color: #0c5460; }
.status-badge.delivered { background-color: #d4edda; color: #155724; }
.status-badge.cancelled { background-color: #f8d7da; color: #721c24; }
.status-badge.refunded { background-color: #d6d8d9; color: #1b1e21; }

.order-items {
    margin-bottom: 20px;
    max-height: 400px; /* Set a max height */
    overflow-y: auto; /* Add vertical scrolling */
    border: 1px solid #eee;
    border-radius: 5px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0; /* Remove bottom margin */
}

.items-table th {
    position: sticky; /* Make headers sticky */
    top: 0; /* Stick to the top */
    background-color: #f5f5f5;
    z-index: 10; /* Ensure headers stay on top */
}

.items-table th, .items-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.items-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.product-cell {
    width: 40%;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.update-form {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: #ffcb05;
    outline: none;
}

.form-group input[readonly] {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.form-actions {
    margin-top: 20px;
}

.save-btn {
    background-color: #ffcb05;
    color: #333;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.save-btn:hover {
    background-color: #ffd740;
}

/* Add these new styles */
.admin-content {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 100px);
    padding-bottom: 50px;
    margin-top: 0; /* Remove negative margin */
}

main {
    min-height: auto; /* Change from 100vh to auto */
    padding-top: 0; /* Remove any top padding */
    margin-top: 0; /* Remove any top margin */
}

.admin-container {
    margin-top: 0; /* Remove top margin completely */
    padding-top: 10px; /* Add a small padding instead */
}

.admin-header {
    margin-bottom: 15px;
}

/* Add this to target the main element directly */
body > main {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

/* Target any potential wrapper elements */
.container, .wrapper, .content-wrapper {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

@media (max-width: 768px) {
    .order-summary {
        grid-template-columns: 1fr;
    }
    
    .order-items {
        max-height: 300px; /* Smaller max height on mobile */
    }
}
</style>

<?php include '../_foot.php'; ?>