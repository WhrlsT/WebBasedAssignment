<?php
require_once '../_base.php';

// Check if user is logged in and is an admin
if (!$loggedIn || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Get dashboard statistics
$userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'];
$productCount = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
$orderCount = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get recent orders - limiting to 5 orders
$recentOrders = $pdo->query("
    SELECT o.id, o.order_reference, o.total_amount, o.status, o.order_date, u.Username 
    FROM orders o
    JOIN users u ON o.user_id = u.UserID
    ORDER BY o.order_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Get data for category pie chart
$categoryData = $pdo->query("
    SELECT c.CategoryName, COUNT(oi.id) as order_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.ProductID
    JOIN category c ON p.CategoryID = c.CategoryID
    GROUP BY c.CategoryID
    ORDER BY order_count DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Get data for monthly sales bar chart
$monthlySales = $pdo->query("
    SELECT 
        DATE_FORMAT(o.order_date, '%Y-%m') as month,
        SUM(o.total_amount) as total_sales
    FROM orders o
    WHERE o.status != 'Cancelled' 
    AND o.order_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

$_title = 'Admin Dashboard';
include '../_head.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p class="welcome-message">Welcome, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>!</p>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../image/profile-icon.png" alt="Users" width="32" height="32">
                </div>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $userCount; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../image/product-icon.png" alt="Products" width="32" height="32">
                </div>
                <div class="stat-content">
                    <h3>Total Products</h3>
                    <p class="stat-number"><?php echo $productCount; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <img src="../image/order-icon.png" alt="Orders" width="32" height="32">
                </div>
                <div class="stat-content">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $orderCount; ?></p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-actions">
            <a href="users.php" class="dashboard-action-btn">
                <img src="../image/profile-icon.png" alt="Users" width="32" height="32">
                <span>User Management</span>
            </a>
            <a href="products.php" class="dashboard-action-btn">
                <img src="../image/product-icon.png" alt="Products" width="32" height="32">
                <span>Product Management</span>
            </a>
            <a href="orders.php" class="dashboard-action-btn">
                <img src="../image/order-icon.png" alt="Orders" width="32" height="32">
                <span>Order Management</span>
            </a>

        </div>
        
        <!-- Charts Section -->
        <div class="chart-container">
            <div class="chart-tabs">
                <div class="chart-tab active" onclick="showChart('category')">Order Category Chart</div>
                <div class="chart-tab" onclick="showChart('sales')">Monthly Sales Chart</div>
            </div>
            
            <div class="chart-content active" id="category-chart">
                <h3 class="chart-header">Orders by Category</h3>
                <canvas id="categoryChart" class="chart-canvas"></canvas>
            </div>
            
            <div class="chart-content" id="sales-chart">
                <h3 class="chart-header">Monthly Sales</h3>
                <canvas id="salesChart" class="chart-canvas"></canvas>
            </div>
        </div>
        
        <div class="recent-orders">
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="orders.php" class="view-all-btn">View All</a>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" class="no-records">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_reference']); ?></td>
                                <td><?php echo htmlspecialchars($order['Username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="action-btn view">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Pie Chart
    const categoryData = <?php echo json_encode($categoryData); ?>;
    const categoryLabels = categoryData.map(item => item.CategoryName);
    const categoryValues = categoryData.map(item => item.order_count);
    
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryValues,
                backgroundColor: [
                    '#ffcb05', '#4bc0c0', '#36a2eb', '#ff6384', '#9966ff', '#ff9f40'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    
    // Monthly Sales Bar Chart
    const salesData = <?php echo json_encode($monthlySales); ?>;
    const months = salesData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleString('default', { month: 'short', year: 'numeric' });
    });
    const sales = salesData.map(item => item.total_sales);
    
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Monthly Sales ($)',
                data: sales,
                backgroundColor: '#ffcb05',
                borderColor: '#e6b800',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>

<!-- Add this JavaScript -->
<script>
function showChart(chartType) {
    // Hide all chart contents
    document.querySelectorAll('.chart-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Deactivate all tabs
    document.querySelectorAll('.chart-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected chart and activate its tab
    document.getElementById(chartType + '-chart').classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>

<?php include '../_foot.php'; ?>