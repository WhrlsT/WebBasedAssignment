<?php
require_once '_base.php';
// Removed: require_once 'lib/SimplePager.php';

// Get category filter
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brandId = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$priceRange = isset($_GET['price']) ? $_GET['price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
// Add search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination settings
$productsPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Build the query
$params = [];
$countParams = []; // Separate params for count query if needed (e.g., price range without binding)
$whereConditions = [];

// Base query parts
$selectClause = "SELECT p.*, c.CategoryName, b.BrandName, 
                 (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath";
$fromClause = " FROM products p
                LEFT JOIN category c ON p.CategoryID = c.CategoryID
                LEFT JOIN brand b ON p.BrandID = b.BrandID";
$countSelectClause = "SELECT COUNT(*) as total";

// Add filters
if ($categoryId > 0) {
    $whereConditions[] = "p.CategoryID = ?";
    $params[] = $categoryId;
    $countParams[] = $categoryId;
}

if ($brandId > 0) {
    $whereConditions[] = "p.BrandID = ?";
    $params[] = $brandId;
    $countParams[] = $brandId;
}

// Add search condition
if (!empty($search)) {
    $whereConditions[] = "(p.ProductName LIKE ? OR p.ProductDescription LIKE ? OR c.CategoryName LIKE ? OR b.BrandName LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
}

// Price range filter - Note: These are added directly to the query string, not parameterized
$priceWhere = '';
if (!empty($priceRange)) {
    switch ($priceRange) {
        case 'under100':
            $priceWhere = "p.ProductPrice < 100";
            break;
        case '100-200':
            $priceWhere = "p.ProductPrice >= 100 AND p.ProductPrice <= 200";
            break;
        case '200-300':
            $priceWhere = "p.ProductPrice >= 200 AND p.ProductPrice <= 300";
            break;
        case 'over300':
            $priceWhere = "p.ProductPrice > 300";
            break;
    }
    if (!empty($priceWhere)) {
        $whereConditions[] = $priceWhere;
        // No parameters needed for countParams here as it's not parameterized
    }
}

// Combine where conditions
$whereClause = "";
if (!empty($whereConditions)) {
    $whereClause = " WHERE " . implode(" AND ", $whereConditions);
}

// Build Count Query
$countQuery = $countSelectClause . $fromClause . $whereClause;

// Execute Count Query
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalProducts = (int)$countStmt->fetchColumn();

// Calculate Total Pages
$totalPages = ($productsPerPage > 0 && $totalProducts > 0) ? ceil($totalProducts / $productsPerPage) : 1;
$page = min($page, $totalPages); // Adjust page if it exceeds total pages
$page = max(1, $page); // Ensure page is still at least 1 after adjustment

// Calculate Offset
$offset = ($page - 1) * $productsPerPage;

// Build Product Query
$query = $selectClause . $fromClause . $whereClause;

// Add sorting
$orderByClause = "";
switch ($sort) {
    case 'name_asc':
        $orderByClause = " ORDER BY p.ProductName ASC";
        break;
    case 'name_desc':
        $orderByClause = " ORDER BY p.ProductName DESC";
        break;
    case 'price_asc':
        $orderByClause = " ORDER BY p.ProductPrice ASC";
        break;
    case 'price_desc':
        $orderByClause = " ORDER BY p.ProductPrice DESC";
        break;
    case 'newest':
        $orderByClause = " ORDER BY p.date_added DESC";
        break;
    case 'bestselling':
        $orderByClause = " ORDER BY p.sales_count DESC";
        break;
    default:
        $orderByClause = " ORDER BY p.ProductID DESC";
}
$query .= $orderByClause;

// Add Limit and Offset
$query .= " LIMIT ? OFFSET ?";
$params[] = $productsPerPage;
$params[] = $offset;

// Execute Product Query
$stmt = $pdo->prepare($query);
// Bind parameters explicitly by type for LIMIT/OFFSET
$paramIndex = 1;
foreach ($params as $key => $value) {
    if ($key === count($params) - 2 || $key === count($params) - 1) { // Last two are LIMIT and OFFSET
        $stmt->bindValue($paramIndex++, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($paramIndex++, $value); // Let PDO determine type for others
    }
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Debug information (optional)
if (isset($_GET['debug'])) {
    echo '<div style="background: #f5f5f5; padding: 10px; margin: 10px; border: 1px solid #ddd;">';
    echo '<h3>Debug Information</h3>';
    echo '<p>Count Query: ' . htmlspecialchars($countQuery) . '</p>';
    echo '<p>Count Parameters: ' . print_r($countParams, true) . '</p>';
    echo '<p>Product Query: ' . htmlspecialchars($query) . '</p>';
    echo '<p>Product Parameters: ' . print_r($params, true) . '</p>';
    echo '<p>Total Products: ' . $totalProducts . '</p>';
    echo '<p>Total Pages: ' . $totalPages . '</p>';
    echo '<p>Current Page: ' . $page . '</p>';
    echo '<p>Products Per Page: ' . $productsPerPage . '</p>';
    echo '<p>Offset: ' . $offset . '</p>';
    echo '<p>Category ID: ' . $categoryId . '</p>';
    echo '<p>Brand ID: ' . $brandId . '</p>';
    echo '<p>Price Range: ' . htmlspecialchars($priceRange) . '</p>';
    echo '<p>Sort: ' . htmlspecialchars($sort) . '</p>';
    echo '<p>Search: ' . htmlspecialchars($search) . '</p>';
    echo '</div>';
}

// Get all categories for the filter
$categories = $pdo->query("SELECT * FROM category ORDER BY CategoryName")->fetchAll();

// Get brands based on category filter (or all if no category selected)
$brandQuery = "SELECT DISTINCT b.* FROM brand b ";
$brandJoinCondition = " JOIN products p ON b.BrandID = p.BrandID";
$brandWhereCondition = "";
if ($categoryId > 0) {
    $brandWhereCondition = " WHERE p.CategoryID = " . $categoryId; // Safe as $categoryId is cast to int
    $brandQuery .= $brandJoinCondition . $brandWhereCondition;
} elseif (!empty($search) || !empty($priceRange) || $brandId > 0) {
    // If other filters are active, join is needed to potentially limit brands shown
    // This part could be refined: maybe show all brands unless a category is chosen?
    // For now, let's join if any filter might affect product list
    $brandQuery .= $brandJoinCondition;
    // Add WHERE clause if needed based on other filters (complex, maybe simplify to just category filter affecting brands)
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
    // Ensure page=1 is removed if we are adding another filter/sort
    if ($param !== 'page' && isset($params['page'])) {
         unset($params['page']); // Reset to page 1 when changing filters/sort
    }
    if ($param === 'page' && $value == 1 && isset($params['page'])) {
         unset($params['page']); // Don't show page=1 in URL
    } elseif ($value == 1 && $param !== 'page') {
         // If adding a filter and value is 1 (e.g. category=1), keep page param if it exists and > 1
         // This logic might need refinement based on desired UX. Resetting is safer.
    }

    return '?' . http_build_query($params);
}

function removeQueryParam($param) {
    $params = $_GET;
    unset($params[$param]);
    // Reset page to 1 when removing a filter
    if ($param !== 'page' && isset($params['page'])) {
        unset($params['page']);
    }
    return empty($params) ? 'product.php' : '?' . http_build_query($params); // Link to base page if no params left
}

// Function to generate pagination HTML
function generatePagination($currentPage, $totalPages, $numLinks = 5) {
    if ($totalPages <= 1) {
        return '';
    }

    $html = '<div class="pagination pager-container">'; // Use similar class for styling

    // Previous Button
    if ($currentPage > 1) {
        $html .= '<a href="' . addQueryParam('page', $currentPage - 1) . '" class="pager-prev">&laquo; Previous</a>';
    } else {
        $html .= '<span class="pager-prev disabled">&laquo; Previous</span>';
    }

    // Page Number Links
    if ($totalPages <= $numLinks + 2) { // Show all pages if not too many
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $html .= '<span class="active">' . $i . '</span>';
            } else {
                $html .= '<a href="' . addQueryParam('page', $i) . '">' . $i . '</a>';
            }
        }
    } else { // Show links with ellipsis
        $start = max(1, $currentPage - floor($numLinks / 2));
        $end = min($totalPages, $start + $numLinks - 1);

        // Adjust start if end is reached early
        if ($end == $totalPages) {
            $start = max(1, $totalPages - $numLinks + 1);
        }

        // Ellipsis at the beginning?
        if ($start > 1) {
            $html .= '<a href="' . addQueryParam('page', 1) . '">1</a>';
            if ($start > 2) {
                $html .= '<span class="pager-ellipsis">...</span>';
            }
        }

        // Numbered links
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $currentPage) {
                $html .= '<span class="active">' . $i . '</span>';
            } else {
                $html .= '<a href="' . addQueryParam('page', $i) . '">' . $i . '</a>';
            }
        }

        // Ellipsis at the end?
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<span class="pager-ellipsis">...</span>';
            }
            $html .= '<a href="' . addQueryParam('page', $totalPages) . '">' . $totalPages . '</a>';
        }
    }


    // Next Button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . addQueryParam('page', $currentPage + 1) . '" class="pager-next">Next &raquo;</a>';
    } else {
        $html .= '<span class="pager-next disabled">Next &raquo;</span>';
    }

    $html .= '</div>';
    return $html;
}

?>

<div class="products-container">
    <h1 class="category-heading"><?php echo htmlspecialchars($categoryName); ?></h1>

    <div class="product-search-container">
        <form action="product.php" method="GET" class="product-search-form">
            <!-- Preserve existing filters that are NOT the primary search -->
            <?php foreach ($_GET as $key => $value): ?>
                <?php if ($key !== 'search' && $key !== 'page'): // Keep filters, remove page ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>

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

            <!-- Category Filter -->
            <div class="filter-dropdown">
                <button class="filter-button">Category</button>
                <div class="filter-dropdown-content">
                    <a href="<?php echo removeQueryParam('category'); ?>" class="<?php echo $categoryId == 0 ? 'active' : ''; ?>">All Products</a>
                    <?php foreach ($categories as $category): ?>
                    <a href="<?php echo addQueryParam('category', $category['CategoryID']); ?>"
                       class="<?php echo $categoryId == $category['CategoryID'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['CategoryName']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

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
        <?php echo $totalProducts; ?> product<?php echo ($totalProducts !== 1) ? 's' : ''; ?> found
        <?php if ($totalPages > 1): ?>
             (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)
        <?php endif; ?>
    </div>

    <div class="product-grid">
        <?php
        // Display products
        if (empty($products)):
        ?>
            <div class="no-products">
                <p>No products found matching your criteria.</p>
                <?php
                // Suggest clearing filters if any are active
                $activeFilters = array_filter($_GET, function($key) {
                    return !in_array($key, ['page', 'sort']); // Ignore page and sort for this check
                }, ARRAY_FILTER_USE_KEY);
                if (!empty($activeFilters)):
                ?>
                <p><a href="product.php">Clear all filters and search</a></p>
                <?php endif; ?>
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
                                <img src="<?php echo htmlspecialchars($product['ImagePath']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                            <?php else: ?>
                                <img src="image/product-placeholder.jpg" alt="No Image Available">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                        <div class="product-detail">
                            <span class="detail-label">CATEGORY:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($product['CategoryName'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="product-detail">
                            <span class="detail-label">BRAND:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($product['BrandName'] ?? 'N/A'); ?></span>
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
    </div> <!-- End product-grid -->

    <?php
    // Display pagination links using the new function
    echo generatePagination($page, $totalPages);
    ?>

</div> <!-- End products-container -->
<style>
.pagination.pager-container {
    text-align: center;
    margin-top: 20px;
    margin-bottom: 20px;
    padding: 10px 0;
    font-size: 0.9em;
}
.pagination.pager-container a,
.pagination.pager-container span {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 2px;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #337ab7;
    background-color: #fff;
    border-radius: 4px;
    transition: background-color 0.2s, color 0.2s;
}
.pagination.pager-container a:hover {
    background-color: #eee;
    color: #23527c;
    border-color: #ccc;
}
.pagination.pager-container span.active {
    background-color: #337ab7;
    color: white;
    border-color: #337ab7;
    cursor: default;
}
.pagination.pager-container span.disabled {
    color: #777;
    background-color: #f5f5f5;
    border-color: #ddd;
    cursor: not-allowed;
}
.pagination.pager-container span.pager-ellipsis {
    border: none;
    background: none;
    padding: 8px 5px;
    color: #777;
}
</style>
<script>
// Keep existing JS for cart popup
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
                        <span class="cart-popup-value" id="popup-quantity">1</span> <!-- Default to 1 -->
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

        // Add event listener to close popup when clicking overlay
        overlay.addEventListener('click', closeCartPopup);
    }

    // Fetch product details if not provided directly (assuming addToCart provides them)
    // If addToCart only passes ID, you'd need an AJAX call here.
    // Assuming productName and price are passed correctly for now.
    document.getElementById('popup-product-name').textContent = productName;
    document.getElementById('popup-price').textContent = 'RM' + parseFloat(price).toFixed(2);
    // Quantity might need to be fetched or assumed as 1 initially
    document.getElementById('popup-quantity').textContent = quantity || 1;


    // Show popup
    document.getElementById('cart-popup-overlay').style.display = 'block';
    document.getElementById('cart-popup').style.display = 'block';
}

function closeCartPopup() {
    const overlay = document.getElementById('cart-popup-overlay');
    const popup = document.getElementById('cart-popup');
    if (overlay) overlay.style.display = 'none';
    if (popup) popup.style.display = 'none';
}

// Ensure cart.js is loaded and addToCart function exists and works as expected
// It should ideally call showCartPopup with name, quantity, price after adding item.
</script>
<script src="js/cart.js"></script> <?php // Make sure this file defines the addToCart function ?>
<?php include '_foot.php'; ?>