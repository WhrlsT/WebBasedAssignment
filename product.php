<?php
require_once '_base.php';
require_once 'lib/SimplePager.php';

// Get category filter
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brandId = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$priceRange = isset($_GET['price']) ? $_GET['price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
// Add search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination - set to 20 products per page (4x5 grid)
$productsPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Build the query
$params = [];
$whereConditions = [];

// Base query
$query = "SELECT p.*, c.CategoryName, b.BrandName, 
          (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath
          FROM products p
          LEFT JOIN category c ON p.CategoryID = c.CategoryID
          LEFT JOIN brand b ON p.BrandID = b.BrandID";

// Count query for total products (without LIMIT)
$countQuery = "SELECT COUNT(*) as total FROM products p";

// Add filters
if ($categoryId > 0) {
    $whereConditions[] = "p.CategoryID = ?";
    $params[] = $categoryId;
}

if ($brandId > 0) {
    $whereConditions[] = "p.BrandID = ?";
    $params[] = $brandId;
}

// Add search condition
if (!empty($search)) {
    $whereConditions[] = "(p.ProductName LIKE ? OR p.ProductDescription LIKE ? OR c.CategoryName LIKE ? OR b.BrandName LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Price range filter
if (!empty($priceRange)) {
    switch ($priceRange) {
        case 'under100':
            $whereConditions[] = "p.ProductPrice < 100";
            break;
        case '100-200':
            $whereConditions[] = "p.ProductPrice >= 100 AND p.ProductPrice <= 200";
            break;
        case '200-300':
            $whereConditions[] = "p.ProductPrice >= 200 AND p.ProductPrice <= 300";
            break;
        case 'over300':
            $whereConditions[] = "p.ProductPrice > 300";
            break;
    }
}

// Combine where conditions
if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
    $countQuery .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add sorting
switch ($sort) {
    case 'name_asc':
        $query .= " ORDER BY p.ProductName ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.ProductName DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY p.ProductPrice ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.ProductPrice DESC";
        break;
    case 'newest':
        $query .= " ORDER BY p.date_added DESC";
        break;
    case 'bestselling':
        $query .= " ORDER BY p.sales_count DESC";
        break;
    default:
        $query .= " ORDER BY p.ProductID DESC";
}

// Use SimplePager for pagination
$_db = $pdo; // SimplePager uses global $_db
$pager = new SimplePager($query, $params, $productsPerPage, $page);
// Add this after line 90 (after creating the SimplePager)
// Debug information
if (isset($_GET['debug'])) {
    echo '<div style="background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;">';
    echo '<h3>Debug Information</h3>';
    echo '<p>Query: ' . $query . '</p>';
    echo '<p>Parameters: ' . print_r($params, true) . '</p>';
    echo '<p>Total Products: ' . $totalProducts . '</p>';
    echo '<p>Category ID: ' . $categoryId . '</p>';
    echo '<p>Brand ID: ' . $brandId . '</p>';
    echo '</div>';
}
$products = $pager->result;
$totalProducts = $pager->item_count;
$totalPages = $pager->page_count;

// Make sure we have the correct count of products
if (empty($products) && $totalProducts > 0 && $page > 1) {
    // If we're on a page with no results but there are products,
    // redirect to the first page
    header("Location: " . removeQueryParam('page'));
    exit;
}

// Get all categories for the filter
$categories = $pdo->query("SELECT * FROM category ORDER BY CategoryName")->fetchAll();

// Get brands based on category filter
$brandQuery = "SELECT DISTINCT b.* FROM brand b 
               JOIN products p ON b.BrandID = p.BrandID";
if ($categoryId > 0) {
    $brandQuery .= " WHERE p.CategoryID = " . $categoryId;
}
$brandQuery .= " ORDER BY b.BrandName";
$brands = $pdo->query($brandQuery)->fetchAll();

// Get category name for display
$categoryName = "All Products";
if ($categoryId > 0) {
    $stmt = $pdo->prepare("SELECT CategoryName FROM category WHERE CategoryID = ?");
    $stmt->execute([$categoryId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $categoryName = $result['CategoryName'];
    }
}

$_title = $categoryName;
include '_head.php';

// Helper functions for URL parameters
function addQueryParam($param, $value) {
    $params = $_GET;
    $params[$param] = $value;
    return '?' . http_build_query($params);
}

function removeQueryParam($param) {
    $params = $_GET;
    unset($params[$param]);
    return empty($params) ? '?' : '?' . http_build_query($params);
}
?>

<div class="products-container">
    <h1 class="category-heading"><?php echo htmlspecialchars($categoryName); ?></h1>
    
    <div class="product-search-container">
        <form action="product.php" method="GET" class="product-search-form">
            <!-- Preserve existing filters -->
            <?php if ($categoryId > 0): ?>
                <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
            <?php endif; ?>
            <?php if ($brandId > 0): ?>
                <input type="hidden" name="brand" value="<?php echo $brandId; ?>">
            <?php endif; ?>
            <?php if (!empty($priceRange)): ?>
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($priceRange); ?>">
            <?php endif; ?>
            <?php if ($sort !== 'default'): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <?php endif; ?>
            
            <div class="search-input-container">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <?php if (!empty($search)): ?>
                <a href="<?php echo removeQueryParam('search'); ?>" class="clear-search">Clear Search</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="filter-bar">
        <div class="filter-section">
            <span class="filter-label">Filter:</span>
            
            <!-- Category Filter - Show in all cases except when coming from navigation -->
            <?php 
            // Check if user came from navigation by checking for a source parameter
            $fromNavigation = isset($_GET['from']) && $_GET['from'] === 'nav';
            
            // Show category filter unless we're on a specific category page AND came from navigation
            if (!($categoryId > 0 && $fromNavigation)): 
            ?>
            <div class="filter-dropdown">
                <button class="filter-button">Category</button>
                <div class="filter-dropdown-content">
                    <a href="product.php" class="<?php echo $categoryId == 0 ? 'active' : ''; ?>">All Products</a>
                    <?php foreach ($categories as $category): ?>
                    <a href="product.php?category=<?php echo $category['CategoryID']; ?>" 
                       class="<?php echo $categoryId == $category['CategoryID'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['CategoryName']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Brand Filter -->
            <div class="filter-dropdown">
                <button class="filter-button">Brand</button>
                <div class="filter-dropdown-content">
                    <a href="<?php echo removeQueryParam('brand'); ?>" class="<?php echo $brandId == 0 ? 'active' : ''; ?>">All Brands</a>
                    <?php foreach ($brands as $brand): ?>
                    <a href="<?php echo addQueryParam('brand', $brand['BrandID']); ?>" 
                       class="<?php echo $brandId == $brand['BrandID'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($brand['BrandName']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Price Filter -->
            <div class="filter-dropdown">
                <button class="filter-button">Price</button>
                <div class="filter-dropdown-content">
                    <a href="<?php echo removeQueryParam('price'); ?>" class="<?php echo $priceRange == '' ? 'active' : ''; ?>">All Prices</a>
                    <a href="<?php echo addQueryParam('price', 'under100'); ?>" class="<?php echo $priceRange == 'under100' ? 'active' : ''; ?>">Under RM100</a>
                    <a href="<?php echo addQueryParam('price', '100-200'); ?>" class="<?php echo $priceRange == '100-200' ? 'active' : ''; ?>">RM100 - RM200</a>
                    <a href="<?php echo addQueryParam('price', '200-300'); ?>" class="<?php echo $priceRange == '200-300' ? 'active' : ''; ?>">RM200 - RM300</a>
                    <a href="<?php echo addQueryParam('price', 'over300'); ?>" class="<?php echo $priceRange == 'over300' ? 'active' : ''; ?>">Over RM300</a>
                </div>
            </div>
        </div>
        
        <div class="sort-section">
            <span class="sort-label">Sort by:</span>
            <select id="sort-select" onchange="window.location.href=this.value">
                <option value="<?php echo addQueryParam('sort', 'default'); ?>" <?php echo $sort == 'default' ? 'selected' : ''; ?>>Default</option>
                <option value="<?php echo addQueryParam('sort', 'bestselling'); ?>" <?php echo $sort == 'bestselling' ? 'selected' : ''; ?>>Best Selling</option>
                <option value="<?php echo addQueryParam('sort', 'name_asc'); ?>" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                <option value="<?php echo addQueryParam('sort', 'name_desc'); ?>" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                <option value="<?php echo addQueryParam('sort', 'price_asc'); ?>" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                <option value="<?php echo addQueryParam('sort', 'price_desc'); ?>" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                <option value="<?php echo addQueryParam('sort', 'newest'); ?>" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
            </select>
        </div>
    </div>
    
    <div class="product-count">
        <?php echo $totalProducts; ?> products
    </div>
    
    <div class="product-grid">
        <?php 
        // Display products
        if (empty($products)): 
        ?>
            <div class="no-products">
                <p>No products found matching your criteria.</p>
            </div>
        <?php 
        else: 
            // Display actual products
            foreach ($products as $product): 
        ?>
            <div class="product-card">
                <a href="product_details.php?id=<?php echo $product['ProductID']; ?>">
                    <div class="product-image-wrapper">
                        <div class="product-image">
                            <?php if (!empty($product['ImagePath'])): ?>
                                <img src="<?php echo $product['ImagePath']; ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                            <?php else: ?>
                                <img src="image/product-placeholder.jpg" alt="No Image">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                        <div class="product-detail">
                            <span class="detail-label">CATEGORY:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($product['CategoryName']); ?></span>
                        </div>
                        <div class="product-detail">
                            <span class="detail-label">BRAND:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($product['BrandName']); ?></span>
                        </div>
                        <div class="product-price">RM<?php echo number_format($product['ProductPrice'], 2); ?></div>
                    </div>
                </a>
                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['ProductID']; ?>)">Add to Cart</button>
            </div>
        <?php 
            endforeach;
        endif; 
        ?>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php 
            // Use the improved SimplePager html method
            $pager->html(http_build_query($_GET), 'class="pager-container"', [
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
                'prev_class' => 'pager-prev',
                'next_class' => 'pager-next',
                'active_class' => 'active',
                'ellipsis_text' => '<span class="pager-ellipsis">...</span>'
            ]); 
            ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showCartPopup(productName, quantity, price) {
    // Create popup elements if they don't exist
    if (!document.getElementById('cart-popup-overlay')) {
        const overlay = document.createElement('div');
        overlay.id = 'cart-popup-overlay';
        overlay.className = 'cart-popup-overlay';
        document.body.appendChild(overlay);
        
        const popup = document.createElement('div');
        popup.id = 'cart-popup';
        popup.className = 'cart-popup';
        popup.innerHTML = `
            <div class="cart-popup-header">
                <h3>Added to Cart</h3>
                <button class="cart-popup-close" onclick="closeCartPopup()">&times;</button>
            </div>
            <div class="cart-popup-content">
                <div class="cart-popup-item">
                    <div class="cart-popup-item-row">
                        <span class="cart-popup-label">Item Name:</span>
                        <span class="cart-popup-value" id="popup-product-name"></span>
                    </div>
                    <div class="cart-popup-item-row">
                        <span class="cart-popup-label">Quantity:</span>
                        <span class="cart-popup-value" id="popup-quantity"></span>
                    </div>
                    <div class="cart-popup-item-row">
                        <span class="cart-popup-label">Price:</span>
                        <span class="cart-popup-value" id="popup-price"></span>
                    </div>
                </div>
            </div>
            <div class="cart-popup-actions">
                <a href="javascript:void(0)" class="cart-popup-btn cart-popup-continue" onclick="closeCartPopup()">Continue Shopping</a>
                <a href="cart.php" class="cart-popup-btn cart-popup-cart">Go to Cart</a>
            </div>
        `;
        document.body.appendChild(popup);
    }
    
    // Update popup content
    document.getElementById('popup-product-name').textContent = productName;
    document.getElementById('popup-quantity').textContent = quantity;
    document.getElementById('popup-price').textContent = 'RM' + parseFloat(price).toFixed(2);
    
    // Show popup
    document.getElementById('cart-popup-overlay').style.display = 'block';
    document.getElementById('cart-popup').style.display = 'block';
}

function closeCartPopup() {
    document.getElementById('cart-popup-overlay').style.display = 'none';
    document.getElementById('cart-popup').style.display = 'none';
}
</script>
<script src="js/cart.js"></script>
<?php include '_foot.php'; ?>

