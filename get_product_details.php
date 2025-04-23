<?php
require_once '_base.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate input
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Get product details
    $stmt = $pdo->prepare("SELECT ProductName as name, ProductPrice as price FROM products WHERE ProductID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'name' => $product['name'], 'price' => $product['price']]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}