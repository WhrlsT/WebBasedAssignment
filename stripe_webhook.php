<?php
require '_base.php';
require 'config_stripe.php';

// Set your webhook secret
$webhookSecret = 'whsec_your_webhook_secret';

// Get the raw POST data
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    // Initialize Stripe - Fix: Use the variable from config_stripe.php instead of undefined constant
    // \Stripe\Stripe::setApiKey(STRIPE_API_KEY);
    
    // The API key is already set in config_stripe.php, so this line is not needed
    
    // Verify the event
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sigHeader, $webhookSecret
    );
    
    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            
            // Get order details from metadata
            $orderId = $session->metadata->order_id ?? null;
            $orderReference = $session->metadata->order_reference ?? null;
            
            if ($orderId && $orderReference) {
                // Update order status
                $stmt = $pdo->prepare("
                    UPDATE orders 
                    SET status = 'Paid', payment_id = ? 
                    WHERE id = ? AND order_reference = ?
                ");
                $stmt->execute([$session->payment_intent, $orderId, $orderReference]);
                
                // Log the transaction
                $stmt = $pdo->prepare("
                    INSERT INTO payment_logs (txn_id, order_reference, payment_amount, payment_currency, payer_email, payment_status, ipn_data)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $session->payment_intent,
                    $orderReference,
                    $session->amount_total / 100, // Convert from cents
                    strtoupper($session->currency),
                    $session->customer_details->email ?? '',
                    'Completed',
                    json_encode($session)
                ]);
                
                // Get order items
                $stmt = $pdo->prepare("
                    SELECT oi.product_id, oi.quantity
                    FROM order_items oi
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$orderId]);
                $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Update product sales count
                foreach ($orderItems as $item) {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET SalesCount = SalesCount + ? 
                        WHERE ProductID = ?
                    ");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }
            }
            break;
            
        case 'payment_intent.payment_failed':
            $paymentIntent = $event->data->object;
            
            // Log failed payment
            $stmt = $pdo->prepare("
                INSERT INTO payment_logs (txn_id, payment_status, ipn_data)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $paymentIntent->id,
                'Failed',
                json_encode($paymentIntent)
            ]);
            break;
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
} catch (Exception $e) {
    // Other error
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}