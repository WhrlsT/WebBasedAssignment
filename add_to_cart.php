<?php
require_once '_base.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!$loggedIn) {
    echo json_encode(['success' => false, 'redirect' => 'login.php', 'message' => 'Please log in to add items to your cart']);
    exit;
}

// Get product ID and quantity from POST data
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate inputs
if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

try {
    // Check if product exists and has stock
    $stmt = $pdo->prepare("SELECT p.ProductID, p.ProductName, p.ProductPrice, ps.Quantity as stock 
                          FROM products p 
                          LEFT JOIN product_stocks ps ON p.ProductID = ps.ProductID 
                          WHERE p.ProductID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit;
    }
    
    // Check if user already has this product in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE UserID = ? AND ProductID = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cartItem) {
        // Update quantity
        $newQuantity = $cartItem['Quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET Quantity = ? WHERE CartID = ?");
        $stmt->execute([$newQuantity, $cartItem['CartID']]);
    } else {
        // Add new item to cart
        $stmt = $pdo->prepare("INSERT INTO cart (UserID, ProductID, Quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $productId, $quantity]);
    }
    
    // Increment sales_count for the product
    $stmt = $pdo->prepare("UPDATE products SET sales_count = sales_count + ? WHERE ProductID = ?");
    $stmt->execute([$quantity, $productId]);
    
    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
