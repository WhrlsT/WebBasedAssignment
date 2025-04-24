<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        .container { width: 100%; margin: 0 auto; }
        h1, h2 { text-align: center; color: #333; }
        .order-info, .shipping-info, .items-table, .summary { margin-bottom: 20px; border: 1px solid #eee; padding: 15px; }
        .order-info p, .shipping-info p { margin: 5px 0; }
        .info-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order Receipt</h1>

        <div class="order-info">
            <h2>Order Information</h2>
            <p><span class="info-label">Order Number:</span> <?php echo htmlspecialchars($order['order_reference']); ?></p>
            <p><span class="info-label">Order Date:</span> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
            <p><span class="info-label">Payment Method:</span> <?php echo ucfirst(htmlspecialchars($order['payment_method'] ?? 'N/A')); ?></p>
            <p><span class="info-label">Status:</span> <?php echo htmlspecialchars($order['status']); ?></p>
            <?php if (!empty($order['payment_id'])): ?>
                <p><span class="info-label">Payment ID:</span> <?php echo htmlspecialchars($order['payment_id']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($shipping)): ?>
        <div class="shipping-info">
            <h2>Shipping Address</h2>
            <p><?php echo htmlspecialchars($shipping['first_name'] . ' ' . $shipping['last_name']); ?></p>
            <p><?php echo htmlspecialchars($shipping['address']); ?></p>
            <p><?php echo htmlspecialchars($shipping['city'] . ', ' . $shipping['state'] . ' ' . $shipping['zip_code']); ?></p>
            <p><?php echo htmlspecialchars($shipping['phone']); ?></p>
            <p><?php echo htmlspecialchars($shipping['email']); ?></p>
        </div>
        <?php endif; ?>

        <div class="items-table">
            <h2>Order Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td class="text-right">RM<?php echo number_format($item['price'], 2); ?></td>
                        <td class="text-right">RM<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary">
            <h2>Summary</h2>
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">RM<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td class="text-right">RM<?php echo number_format($order['shipping_cost'], 2); ?></td>
                </tr>
                <tr class="total">
                    <td>Total:</td>
                    <td class="text-right">RM<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </table>
        </div>

        <p style="text-align: center; margin-top: 30px;">Thank you for your order!</p>
    </div>
</body>
</html>