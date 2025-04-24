<?php
require '_base.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    header('Location: profile.php?tab=orders');
    exit;
}

$orderId = (int)$_POST['order_id'];

try {
    $stmt = $pdo->prepare("
        SELECT o.id, o.order_reference, o.total_amount, o.status
        FROM orders o
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Redirect if order not found or doesn't belong to the user
    if (!$order) {
        header('Location: profile.php?tab=orders');
        exit;
    }
    
    // Check if order is eligible for refund
    $eligibleStatuses = ['delivered'];
    if (!in_array(strtolower($order['status']), $eligibleStatuses)) {
        $_SESSION['error'] = "This order is not eligible for refund.";
        header("Location: order_details.php?id=$orderId");
        exit;
    }
    
    // Check if refund already requested
    $stmt = $pdo->prepare("
        SELECT id FROM refund_requests 
        WHERE order_id = ? AND status IN ('pending', 'approved')
    ");
    $stmt->execute([$orderId]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "A refund request for this order already exists.";
        header("Location: order_details.php?id=$orderId");
        exit;
    }
} catch (PDOException $e) {
    // Handle database error
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: order_details.php?id=$orderId");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.ProductName, 
               (SELECT picturePath FROM productpictures WHERE ProductID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath
        FROM order_items oi
        JOIN products p ON oi.product_id = p.ProductID
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
    $orderItems = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_refund'])) {
    $reason = $_POST['reason'] ?? '';
    $details = $_POST['details'] ?? '';
    
    // Validate input
    if (empty($reason)) {
        $error = "Please select a reason for your refund request.";
    } else {
        try {
            // Insert refund request
            $stmt = $pdo->prepare("
                INSERT INTO refund_requests 
                (order_id, user_id, reason, details, status, requested_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $orderId,
                $_SESSION['user_id'],
                $reason,
                $details
            ]);
            
            // Optionally update order status
            $stmt = $pdo->prepare("
                UPDATE orders SET status = 'Refund Request' WHERE id = ?
            ");
            $stmt->execute([$orderId]);
            
            $_SESSION['success'] = "Your refund request has been submitted successfully.";
            header("Location: order_details.php?id=$orderId");
            exit;
            
        } catch (PDOException $e) {
            $error = "Failed to submit refund request: " . $e->getMessage();
        }
    }
}

$_title = 'Request Refund - ' . $order['order_reference'];
include '_head.php';
?>

<div class="refund-details-container">
    <div class="refund-details-header">
        <h1>Refund</h1>
        <a href="order_details.php?id=<?php echo $orderId; ?>" class="back-to-order">
            <i class="fas fa-arrow-left"></i> Back to Order Details
        </a>
    </div>
        
    <div class="info-summary">
        <div class="order-info-summary">
            
            <div class="items-grid">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item-image">
                        <?php if (!empty($item['ImagePath'])): ?>
                            <img src="<?php echo htmlspecialchars($item['ImagePath']); ?>" 
                                alt="<?php echo htmlspecialchars($item['ProductName']); ?>"
                                class="item-thumbnail">
                        <?php else: ?>
                            <div class="no-image-thumbnail">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="item-info">
                            <span class="item-name"><?php echo htmlspecialchars($item['ProductName']); ?></span>
                            <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-info-row">
                <span class="info-label">Order Number:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['order_reference']); ?></span>
            </div>
            <div class="order-info-row">
                <span class="info-label">Total:</span>
                <span class="info-value">RM<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
            <div class="order-info-row">
                <span class="info-label">Current Status:</span>
                <span class="info-value"><?php echo strtolower($order['status']); ?></span>
            </div>
        </div>
        <div class="refund-right">
            <form method="POST" class="refund-request-form">
                <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
            
                <div class="form-group">
                    <div class="form-sub">
                        <label for="reason">Reason for Refund</label>
                        <select name="reason" id="reason" class="form-control" required>
                            <option value="">-- Select a reason --</option>
                            <option value="changed_mind">Changed my mind</option>
                            <option value="wrong_item">Wrong item received</option>
                            <option value="defective">Item is defective/damaged</option>
                            <option value="not_as_described">Item not as described</option>
                            <option value="late_delivery">Item arrived too late</option>
                            <option value="other">Other reason</option>
                        </select>
                    </div>

                    <div class="form-sub">
                        <label for="details">Additional Details (Optional)</label>
                        <textarea name="details" id="details" class="form-control" rows="4" 
                                placeholder="Provide any additional information"></textarea>
                    </div>
                </div>
                
                <!-- Move the submit button inside the form -->
                <div class="form-actions">
                    <button type="submit" name="submit_refund" class="btn btn-primary">Submit Refund Request</button>
                    <a href="order_details.php?id=<?php echo $orderId; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form> <!-- Form now closes AFTER the buttons -->
        </div>
    </div>
        
    <!-- Removed the original form-actions div from here -->
        
    <div class="form-group">
        <label>Refund Method</label>
        <div class="refund-method-info">
            <p>The refund will be issued to your original payment method.</p>
            <p>Processing time: 5-7 business days after approval.</p>
        </div>
    </div>
        
        
    
    
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    
    
    
</div>


<?php include '_foot.php'; ?>