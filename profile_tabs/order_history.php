<?php
// Prevent direct access to this file
if (!defined('INCLUDED_FROM_PROFILE')) {
    header('Location: ../profile.php');
    exit;
}

// Include SimplePager class first
require_once 'lib/SimplePager.php';

// Get order status counts
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM orders 
    WHERE user_id = ? 
    GROUP BY status
");
$stmt->execute([$userId]);
$statusCounts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = $row['count'];
}

// Get total orders count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$userId]);
$totalOrders = $stmt->fetchColumn();

// Handle sorting and filtering
$sort = $_GET['sort'] ?? 'date_desc';
$month = $_GET['month'] ?? '';
$year = $_GET['year'] ?? '';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? ''; // Add status filter

// Define the base query
$query = "
SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count 
FROM orders o 
WHERE o.user_id = ?";

$params = [$userId];

// Add search filter if provided
if (!empty($search)) {
    $query .= " AND o.order_reference LIKE ?";
    $params[] = "%$search%";
}

// Add date filters if provided
if (!empty($month) && !empty($year)) {
    $query .= " AND MONTH(o.order_date) = ? AND YEAR(o.order_date) = ?";
    $params[] = $month;
    $params[] = $year;
} else if (!empty($year)) {
    $query .= " AND YEAR(o.order_date) = ?";
    $params[] = $year;
}

// Add status filter if provided
if (!empty($status)) {
    $query .= " AND o.status = ?";
    $params[] = $status;
}

// Add sorting
switch ($sort) {
    case 'date_asc':
        $query .= " ORDER BY o.order_date ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY o.total_amount DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY o.total_amount ASC";
        break;
    case 'ref_asc':
        $query .= " ORDER BY o.order_reference ASC";
        break;
    case 'ref_desc':
        $query .= " ORDER BY o.order_reference DESC";
        break;
    default:
        $query .= " ORDER BY o.order_date DESC";
}

// Get available years and months for filtering
$stmt = $pdo->prepare("
    SELECT DISTINCT YEAR(order_date) as year, MONTH(order_date) as month, 
    COUNT(*) as count
    FROM orders 
    WHERE user_id = ?
    GROUP BY YEAR(order_date), MONTH(order_date)
    ORDER BY year DESC, month DESC
");
$stmt->execute([$userId]);
$dateFilters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all available statuses for filtering
$stmt = $pdo->prepare("
    SELECT DISTINCT status
    FROM orders 
    WHERE user_id = ?
    ORDER BY status
");
$stmt->execute([$userId]);
$availableStatuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Use SimplePager for pagination
require_once 'lib/SimplePager.php';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pager = new SimplePager($query, $params, 10, $page); // 10 orders per page
$orders = $pager->result;
$totalPages = $pager->page_count;
?>

<div id="order-history" class="profile-section hidden">
    <h2>Order History</h2>
    
    <div class="order-stats">
        <div class="stat-box">
            <h3>Total Orders</h3>
            <p class="stat-number"><?php echo $totalOrders; ?></p>
        </div>
        
        <div class="stat-box">
            <h3>Pending</h3>
            <p class="stat-number"><?php echo $statusCounts['Pending'] ?? 0; ?></p>
        </div>
        
        <div class="stat-box">
            <h3>Paid</h3>
            <p class="stat-number"><?php echo $statusCounts['Paid'] ?? 0; ?></p>
        </div>
    </div>
    
    <div class="order-filters">
        <form method="get" action="" id="order-filter-form">
            <input type="hidden" name="tab" value="orders">
            
            <div class="filter-group">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="date_desc" <?php echo $sort === 'date_desc' ? 'selected' : ''; ?>>Date (Newest)</option>
                    <option value="date_asc" <?php echo $sort === 'date_asc' ? 'selected' : ''; ?>>Date (Oldest)</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="ref_asc" <?php echo $sort === 'ref_asc' ? 'selected' : ''; ?>>Reference (A-Z)</option>
                    <option value="ref_desc" <?php echo $sort === 'ref_desc' ? 'selected' : ''; ?>>Reference (Z-A)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status-filter">Status:</label>
                <select name="status" id="status-filter" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <?php foreach ($availableStatuses as $availableStatus): ?>
                        <option value="<?php echo htmlspecialchars($availableStatus); ?>" <?php echo $status === $availableStatus ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($availableStatus); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date-filter">Period:</label>
                <select name="year" id="year-filter" onchange="this.form.submit()">
                    <option value="">All Years</option>
                    <?php 
                    $years = [];
                    foreach ($dateFilters as $filter) {
                        if (!in_array($filter['year'], $years)) {
                            $years[] = $filter['year'];
                            echo '<option value="' . $filter['year'] . '" ' . 
                                ($year == $filter['year'] ? 'selected' : '') . '>' . 
                                $filter['year'] . '</option>';
                        }
                    }
                    ?>
                </select>
                
                <select name="month" id="month-filter" onchange="this.form.submit()" <?php echo empty($year) ? 'disabled' : ''; ?>>
                    <option value="">All Months</option>
                    <?php if (!empty($year)): ?>
                        <?php 
                        $monthNames = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        
                        foreach ($dateFilters as $filter) {
                            if ($filter['year'] == $year) {
                                echo '<option value="' . $filter['month'] . '" ' . 
                                    ($month == $filter['month'] ? 'selected' : '') . '>' . 
                                    $monthNames[$filter['month']] . ' (' . $filter['count'] . ')</option>';
                            }
                        }
                        ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="filter-group search-group">
                <input type="text" name="search" placeholder="Search by order reference" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search) || !empty($month) || !empty($year) || !empty($status)): ?>
                    <a href="?tab=orders" class="clear-filter-btn">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>You haven't placed any orders yet.</p>
            <a href="index.php" class="shop-now-btn">Shop Now</a>
        </div>
    <?php else: ?>
        <div class="order-cards">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-ref">
                            <span class="label">Order Reference:</span>
                            <span class="value"><?php echo htmlspecialchars($order['order_reference']); ?></span>
                        </div>
                        <div class="order-status <?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-card-body">
                        <div class="order-info">
                            <div class="order-info-row">
                                <span class="label">Order Date:</span>
                                <span class="value"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                            </div>
                            <div class="order-info-row">
                                <span class="label">Items:</span>
                                <span class="value"><?php echo $order['item_count']; ?></span>
                            </div>
                        </div>
                        
                        <div class="price-container">
                            <span class="price-label">Total Price:</span>
                            <span class="value price">RM<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        
                        <div class="order-card-footer">
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-details-btn">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?tab=orders&page=<?php echo ($page - 1); ?>&sort=<?php echo $sort; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="page-link prev">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?tab=orders&page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?tab=orders&page=<?php echo ($page + 1); ?>&sort=<?php echo $sort; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="page-link next">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enable/disable month filter based on year selection
        const yearFilter = document.getElementById('year-filter');
        const monthFilter = document.getElementById('month-filter');
        
        if (yearFilter && monthFilter) {
            yearFilter.addEventListener('change', function() {
                if (this.value === '') {
                    monthFilter.disabled = true;
                    monthFilter.value = '';
                } else {
                    monthFilter.disabled = false;
                }
            });
        }
    });
    </script>
</div>