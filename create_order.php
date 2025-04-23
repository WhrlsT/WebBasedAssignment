<?php
require '_base.php';

// Redirect to login if not logged in
if (!$loggedIn) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

// Check if cart is empty
$stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartCount = $stmt->fetchColumn();

if ($cartCount == 0) {
    header('Location: cart.php?error=empty_cart');
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get cart items
    $stmt = $pdo->prepare("
        SELECT c.*, p.ProductName, p.Price 
        FROM cart c
        JOIN products p ON c.product_id = p.ProductID
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['Price'] * $item['quantity'];
    }
    
    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, order_date)
        VALUES (?, ?, 'Pending', NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $orderId = $pdo->lastInsertId();
    
    // Add order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($cartItems as $item) {
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['Price']
        ]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect to checkout with order ID
    header("Location: checkout.php?order_id=$orderId");
    exit;
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Error creating order: " . $e->getMessage());
    header('Location: cart.php?error=checkout_failed');
    exit;
}
?>