<?php
require '../_base.php';
require_once '../lib/SimplePager.php';

// To this:
if (!$loggedIn || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// Get order statuses for filter
$statusStmt = $pdo->query("SELECT DISTINCT status FROM orders");
$statuses = $statusStmt->fetchAll(PDO::FETCH_COLUMN);

$_title = 'Admin - Order Management';
include '../_head.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Order Management</h1>
        </div>
        
        <div class="admin-filters">
            <form method="get" action="orders.php" class="search-form">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search by order # or customer..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $statusOption): ?>
                            <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo $status === $statusOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($statusOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="sort-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="date_desc" <?php echo $sort === 'date_desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="date_asc" <?php echo $sort === 'date_asc' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="amount_desc" <?php echo $sort === 'amount_desc' ? 'selected' : ''; ?>>Amount (High to Low)</option>
                        <option value="amount_asc" <?php echo $sort === 'amount_asc' ? 'selected' : ''; ?>>Amount (Low to High)</option>
                    </select>
                </div>
            </form>
        </div>
        
        <?php
        // Build base query
        $query = "SELECT o.*, u.Username 
                  FROM orders o
                  JOIN users u ON o.user_id = u.UserID";
        
        // Add filters
        $params = [];
        if (!empty($status)) {
            $query .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $query .= (strpos($query, 'WHERE') === false ? " WHERE " : " AND ");
            $query .= "(o.order_reference LIKE ? OR u.Username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Add sorting
        switch ($sort) {
            case 'date_asc': $query .= " ORDER BY o.order_date ASC"; break;
            case 'date_desc': $query .= " ORDER BY o.order_date DESC"; break;
            case 'amount_desc':
                $query .= " ORDER BY o.total_amount DESC";
                break;
            case 'amount_asc':
                $query .= " ORDER BY o.total_amount ASC";
                break;
            default:
                $query .= " ORDER BY o.order_date DESC";
        }
        
        // Create pager
        $pager = new SimplePager($query, $params, 10, $page);
        $orders = $pager->result;
        
        // Get total count directly if not available from pager
        if (!isset($pager->item_count)) {
            $countQuery = "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.UserID";
            
            // Add the same filters to count query
            if (!empty($status)) {
                $countQuery .= " WHERE o.status = ?";
            }
            
            if (!empty($search)) {
                $countQuery .= (strpos($countQuery, 'WHERE') === false ? " WHERE " : " AND ");
                $countQuery .= "(o.order_reference LIKE ? OR u.Username LIKE ?)";
            }
            
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalOrders = $countStmt->fetchColumn();
            $totalPages = ceil($totalOrders / 10);
        } else {
            $totalOrders = $pager->item_count;
            $totalPages = $pager->page_count;
        }
        ?>
        
        <div class="admin-table-container">
            <div class="table-header">
                <span>Showing <?php echo count($orders); ?> of <?php echo $totalOrders; ?> orders</span>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="sortable">Order #</th>
                        <th class="sortable">Customer</th>
                        <th class="sortable">Date</th>
                        <th class="sortable">Amount</th>
                        <th class="sortable">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_reference']); ?></td>
                                <td><?php echo htmlspecialchars($order['Username']); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($order['order_date'])); ?></td>
                                <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="update_order.php?id=<?php echo $order['id']; ?>" class="edit-btn" title="Edit Order">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-results">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo ($page - 1); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="page-link">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo ($page + 1); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.action-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.view-btn, .edit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    color: #fff;
    transition: background-color 0.3s;
}

.view-btn {
    background-color: #007bff;
}

.edit-btn {
    background-color: #28a745;
}

.view-btn:hover {
    background-color: #0069d9;
}

.edit-btn:hover {
    background-color: #218838;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
}

.status-badge.pending { background-color: #ffeeba; color: #856404; }
.status-badge.paid { background-color: #c3e6cb; color: #155724; }
.status-badge.processing { background-color: #b8daff; color: #004085; }
.status-badge.shipped { background-color: #d1ecf1; color: #0c5460; }
.status-badge.delivered { background-color: #d4edda; color: #155724; }
.status-badge.cancelled { background-color: #f8d7da; color: #721c24; }
.status-badge.refunded { background-color: #d6d8d9; color: #1b1e21; }
</style>

<?php include '../_foot.php'; ?>