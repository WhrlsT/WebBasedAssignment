<?php
require '_base.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!$loggedIn) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to update your cart.']);
    exit;
}

// Check if request is POST and has required parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cartId = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    try {
        if ($quantity > 0) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET Quantity = ? WHERE CartID = ? AND UserID = ?");
            $stmt->execute([$quantity, $cartId, $_SESSION['user_id']]);
        } else {
            // Remove item if quantity is 0
            $stmt = $pdo->prepare("DELETE FROM cart WHERE CartID = ? AND UserID = ?");
            $stmt->execute([$cartId, $_SESSION['user_id']]);
        }
        
        // Get updated cart totals
        $stmt = $pdo->prepare("
            SELECT SUM(c.Quantity * p.ProductPrice) as subtotal, SUM(c.Quantity) as item_count
            FROM cart c
            JOIN products p ON c.ProductID = p.ProductID
            WHERE c.UserID = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $subtotal = $result['subtotal'] ?? 0;
        $itemCount = $result['item_count'] ?? 0;
        $shippingCost = $subtotal > 0 ? 10.00 : 0;
        $total = $subtotal + $shippingCost;
        
        echo json_encode([
            'success' => true,
            'subtotal' => $subtotal,
            'shipping' => $shippingCost,
            'total' => $total,
            'item_count' => (int)$itemCount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>