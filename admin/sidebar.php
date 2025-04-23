<div class="sidebar-header">
    <h3>Admin Panel</h3>
</div>

<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <a href="index.php" class="sidebar-link">
                <img src="../image/dashboard-icon.png" alt="Dashboard" width="18" height="18" style="margin-right: 10px; filter: brightness(0) invert(1);">
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
            <a href="products.php" class="sidebar-link">
                <img src="../image/product-icon.png" alt="Products" width="18" height="18" style="margin-right: 10px; filter: brightness(0) invert(1);">
                <span>Products</span>
            </a>
        </li>
        
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
            <a href="orders.php" class="sidebar-link">
                <img src="../image/order-icon.png" alt="Orders" width="18" height="18" style="margin-right: 10px; filter: brightness(0) invert(1);">
                <span>Orders</span>
            </a>
        </li>
        
        <li class="sidebar-item <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
            <a href="users.php" class="sidebar-link">
                <img src="../image/profile-icon.png" alt="Users" width="18" height="18" style="margin-right: 10px; filter: brightness(0) invert(1);">
                <span>Users</span>
            </a>
        </li>
    </ul>
</nav>

<div class="sidebar-footer">
    <a href="../logout.php" class="sidebar-link logout-link">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>