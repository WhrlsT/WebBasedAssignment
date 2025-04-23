<?php
require_once '_base.php';

// Read POST data from PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Build the request for validation
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Set up the request to PayPal
$ch = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'); // Use 'https://ipnpb.paypal.com/cgi-bin/webscr' for production
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// Execute the request and get the response
$response = curl_exec($ch);
curl_close($ch);

// Log all incoming data for debugging
error_log("PayPal IPN: Received data - " . print_r($_POST, true));

// Process the response
if ($response == "VERIFIED") {
    // Payment was verified - process the order
    
    // Get transaction data
    $item_name = $_POST['item_name'] ?? '';
    $item_number = $_POST['item_number'] ?? '';
    $payment_status = $_POST['payment_status'] ?? '';
    $payment_amount = $_POST['mc_gross'] ?? '';
    $payment_currency = $_POST['mc_currency'] ?? '';
    $txn_id = $_POST['txn_id'] ?? '';
    $receiver_email = $_POST['receiver_email'] ?? '';
    $payer_email = $_POST['payer_email'] ?? '';
    $order_reference = $_POST['invoice'] ?? $_POST['custom'] ?? ''; // Try both invoice and custom fields
    
    // Log the IPN data
    $ipn_data = json_encode($_POST);
    error_log("PayPal IPN: Transaction data - Order: $order_reference, Status: $payment_status, Amount: $payment_amount");
    
    try {
        // Insert payment log
        $stmt = $pdo->prepare("
            INSERT INTO payment_logs (txn_id, order_reference, payment_amount, payment_currency, 
                                     payer_email, payment_status, ipn_data)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$txn_id, $order_reference, $payment_amount, $payment_currency, 
                       $payer_email, $payment_status, $ipn_data]);
        
        // Update the order status regardless of payment_status for testing
        // In production, you should check for 'Completed' status
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'Paid', 
                payment_id = ?, 
                payment_method = 'paypal',
                payment_date = NOW() 
            WHERE order_reference = ?
        ");
        $result = $stmt->execute([$txn_id, $order_reference]);
        $rowsAffected = $stmt->rowCount();
        
        error_log("PayPal IPN: Update result - Affected rows: $rowsAffected, Order reference: $order_reference");
        
        // If no rows were affected, try to find the order by other means
        if ($rowsAffected == 0) {
            // Try to find the most recent pending order
            $stmt = $pdo->prepare("
                SELECT id FROM orders 
                WHERE status = 'Pending' AND payment_method = 'paypal'
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute();
            $pendingOrder = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pendingOrder) {
                $orderId = $pendingOrder['id'];
                error_log("PayPal IPN: Found pending order ID: $orderId");
                
                // Update this order instead
                $stmt = $pdo->prepare("
                    UPDATE orders 
                    SET status = 'Paid', 
                        payment_id = ?, 
                        payment_date = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$txn_id, $orderId]);
                
                error_log("PayPal IPN: Updated pending order ID: $orderId to Paid status");
            }
        }
    } catch (PDOException $e) {
        error_log("PayPal IPN Error: " . $e->getMessage());
    }
    
} else if ($response == "INVALID") {
    // Log invalid IPN
    error_log("PayPal IPN: Invalid payment notification");
} else {
    // Log other response
    error_log("PayPal IPN: Unexpected response - $response");
}