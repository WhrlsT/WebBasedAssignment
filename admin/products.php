<?php
require_once '../_base.php';
require_once '../lib/SimplePager.php';

// Check if user is logged in and is an admin
if (!$loggedIn || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Handle product actions
$action = $_GET['action'] ?? '';
$productId = $_GET['id'] ?? '';
$successMessage = '';
$errorMessage = '';

// Get categories and brands
$categories = $pdo->query("SELECT * FROM category ORDER BY CategoryName")->fetchAll();
$brands = $pdo->query("SELECT * FROM brand ORDER BY BrandName")->fetchAll();

// Search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';

// Add category and brand filter parameters
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brandFilter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

// Handle AJAX requests for form content
if (($action === 'add' || $action === 'edit') && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Get product data for edit
    $productData = [];
    if ($action === 'edit' && !empty($productId)) {
        $stmt = $pdo->prepare("SELECT p.*, ps.Quantity as stock 
                             FROM products p 
                             LEFT JOIN product_stocks ps ON p.ProductID = ps.ProductID 
                             WHERE p.ProductID = ?");
        $stmt->execute([$productId]);
        $productData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <div class="admin-form-container">
        <h2><?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Product</h2>
        <form action="products.php" method="POST" enctype="multipart/form-data" class="admin-form">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" id="product_name" name="product_name" 
                       value="<?php echo htmlspecialchars($productData['ProductName'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="product_description">Description</label>
                <textarea id="product_description" name="product_description" rows="4" required><?php echo htmlspecialchars($productData['ProductDescription'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="product_price">Price ($)</label>
                <input type="number" id="product_price" name="product_price" step="0.01" min="0" 
                       value="<?php echo htmlspecialchars($productData['ProductPrice'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="product_category">Category</label>
                <select id="product_category" name="product_category" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['CategoryID']; ?>" 
                            <?php echo (isset($productData['CategoryID']) && $productData['CategoryID'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                            <?php echo $category['CategoryName']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="product_brand">Brand</label>
                <select id="product_brand" name="product_brand" required>
                    <option value="">Select Brand</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo $brand['BrandID']; ?>" 
                            <?php echo (isset($productData['BrandID']) && $productData['BrandID'] == $brand['BrandID']) ? 'selected' : ''; ?>>
                            <?php echo $brand['BrandName']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="product_stock">Stock Quantity</label>
                <input type="number" id="product_stock" name="product_stock" min="0" 
                       value="<?php echo htmlspecialchars($productData['stock'] ?? '0'); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Product Images (First image will be the cover)</label>
                <div class="product-images-container">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="image-upload-group">
                            <label for="product_image<?php echo $i; ?>">Image <?php echo $i; ?></label>
                            <input type="file" id="product_image<?php echo $i; ?>" name="product_image<?php echo $i; ?>" 
                                   accept="image/*" <?php echo ($i === 1 && $action === 'add') ? 'required' : ''; ?> 
                                   onchange="previewImage(this, 'preview_image<?php echo $i; ?>')">
                            <div class="image-preview" id="preview_image<?php echo $i; ?>">
                                <?php if ($action === 'edit' && !empty($productData['ProductID'])): ?>
                                    <?php 
                                    $imageQuery = $pdo->prepare("SELECT picturePath FROM productpictures WHERE productID = ? AND DisplayOrder = ?");
                                    $imageQuery->execute([$productData['ProductID'], $i]);
                                    $imagePath = $imageQuery->fetchColumn();
                                    if ($imagePath): 
                                    ?>
                                        <img src="../<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image <?php echo $i; ?>">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="<?php echo $action === 'add' ? 'add_product' : 'update_product'; ?>" class="admin-button">
                    <?php echo $action === 'add' ? 'Add' : 'Update'; ?> Product
                </button>
                <button type="button" class="admin-button cancel" onclick="document.getElementById('productModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
    <?php
    exit; // Stop execution after sending the form
}

// Handle delete action
if ($action === 'delete' && !empty($productId)) {
    try {
        $pdo->beginTransaction();
        
        // Delete product images first
        $pdo->prepare("DELETE FROM productpictures WHERE productID = ?")->execute([$productId]);
        
        // Delete product stock
        $pdo->prepare("DELETE FROM product_stocks WHERE ProductID = ?")->execute([$productId]);
        
        // Delete the product
        $pdo->prepare("DELETE FROM products WHERE ProductID = ?")->execute([$productId]);
        
        $pdo->commit();
        $successMessage = "Product deleted successfully!";
        header("Location: products.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = "Error deleting product: " . $e->getMessage();
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new product
    if (isset($_POST['add_product'])) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO products (CategoryID, BrandID, ProductName, ProductPrice, ProductDescription) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['product_category'],
                $_POST['product_brand'],
                $_POST['product_name'],
                $_POST['product_price'],
                $_POST['product_description']
            ]);
            
            $productId = $pdo->lastInsertId();
            
            $stockStmt = $pdo->prepare("INSERT INTO product_stocks (ProductID, Quantity) VALUES (?, ?)");
            $stockStmt->execute([$productId, $_POST['product_stock']]);
            
            $uploadDir = '../product_images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            for ($i = 1; $i <= 5; $i++) {
                $fileField = 'product_image' . $i;
                
                if (!empty($_FILES[$fileField]['name'])) {
                    $fileName = time() . '_' . $i . '_' . basename($_FILES[$fileField]['name']);
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetFile)) {
                        $isCover = ($i === 1) ? 1 : 0;
                        
                        $imageStmt = $pdo->prepare("INSERT INTO productpictures (productID, isCover, picturePath, DisplayOrder) 
                                                  VALUES (?, ?, ?, ?)");
                        $imageStmt->execute([$productId, $isCover, 'product_images/' . $fileName, $i]);
                    }
                }
            }
            
            $pdo->commit();
            $successMessage = "Product added successfully!";
            header("Location: products.php");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
    
    // Update existing product
    if (isset($_POST['update_product'])) {
        try {
            $pdo->beginTransaction();
            
            // Get the product ID from the form submission instead of URL
            $updateProductId = $_POST['product_id'] ?? 0;
            
            $stmt = $pdo->prepare("UPDATE products SET 
                                  CategoryID = ?, 
                                  BrandID = ?, 
                                  ProductName = ?, 
                                  ProductPrice = ?, 
                                  ProductDescription = ? 
                                  WHERE ProductID = ?");
            $stmt->execute([
                $_POST['product_category'],
                $_POST['product_brand'],
                $_POST['product_name'],
                $_POST['product_price'],
                $_POST['product_description'],
                $updateProductId
            ]);
            
            $stockStmt = $pdo->prepare("UPDATE product_stocks SET Quantity = ? WHERE ProductID = ?");
            $stockStmt->execute([$_POST['product_stock'], $updateProductId]);
            
            $pdo->commit();
            $successMessage = "Product updated successfully!";
            header("Location: products.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Error updating product: " . $e->getMessage();
        }
    }
}

$_title = 'Admin - Product Management';
include '../_head.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>Product Management</h1>
            <div class="admin-actions">
                <a href="javascript:void(0);" onclick="openAddModal()" class="admin-btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        </div>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <div class="admin-filters">
            <form method="get" action="products.php" class="search-form" id="filterForm">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" id="searchInput">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-group">
                    <select name="category" id="categoryFilter">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['CategoryID']; ?>" <?php echo $categoryFilter == $category['CategoryID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="brand" id="brandFilter">
                        <option value="0">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['BrandID']; ?>" <?php echo $brandFilter == $brand['BrandID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['BrandName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="sort-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sortFilter">
                        <option value="id_desc" <?php echo $sort === 'id_desc' ? 'selected' : ''; ?>>Newest</option>
                        <option value="id_asc" <?php echo $sort === 'id_asc' ? 'selected' : ''; ?>>Oldest</option>
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </div>
            </form>
        </div>
        
        <?php
        $query = "SELECT p.*, c.CategoryName, b.BrandName, ps.Quantity as stock,
                  (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) AS picturePath
                  FROM products p
                  LEFT JOIN category c ON p.CategoryID = c.CategoryID
                  LEFT JOIN brand b ON p.BrandID = b.BrandID
                  LEFT JOIN product_stocks ps ON p.ProductID = ps.ProductID";
        
        // Build WHERE clause for filters
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(p.ProductName LIKE ? OR p.ProductDescription LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($categoryFilter > 0) {
            $whereConditions[] = "p.CategoryID = ?";
            $params[] = $categoryFilter;
        }
        
        if ($brandFilter > 0) {
            $whereConditions[] = "p.BrandID = ?";
            $params[] = $brandFilter;
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        switch ($sort) {
            case 'name_asc': $query .= " ORDER BY p.ProductName ASC"; break;
            case 'name_desc': $query .= " ORDER BY p.ProductName DESC"; break;
            case 'price_asc': $query .= " ORDER BY p.ProductPrice ASC"; break;
            case 'price_desc': $query .= " ORDER BY p.ProductPrice DESC"; break;
            case 'id_asc': $query .= " ORDER BY p.ProductID ASC"; break;
            default: $query .= " ORDER BY p.ProductID DESC";
        }
        
        $pager = new SimplePager($query, $params, 10, $page);
        $products = $pager->result;
        $totalProducts = $pager->item_count;
        $totalPages = $pager->page_count;
        ?>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="no-records">No products found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['ProductID']; ?></td>
                            <td>
                                <?php if (!empty($product['picturePath'])): ?>
                                    <img src="../<?php echo htmlspecialchars($product['picturePath']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>" class="product-thumbnail">
                                <?php else: ?>
                                    <div class="no-image">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                            <td><?php echo htmlspecialchars($product['CategoryName'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($product['BrandName'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($product['ProductPrice'], 2); ?></td>
                            <td><?php echo $product['stock'] ?? '0'; ?></td>
                            <td class="actions">
                                <a href="javascript:void(0);" onclick="openEditModal(<?php echo $product['ProductID']; ?>)" class="action-btn edit">Edit</a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $product['ProductID']; ?>)" class="action-btn delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&brand=<?php echo $brandFilter; ?>&sort=<?php echo $sort; ?>" class="page-link">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&brand=<?php echo $brandFilter; ?>&sort=<?php echo $sort; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&brand=<?php echo $brandFilter; ?>&sort=<?php echo $sort; ?>" class="page-link">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div id="modalFormContent"></div>
    </div>
</div>

<script>
function openAddModal() {
    fetch('products.php?action=add', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('modalFormContent').innerHTML = html;
        document.getElementById('productModal').style.display = 'block';
    })
    .catch(error => console.error('Error:', error));
}

function openEditModal(productId) {
    fetch(`products.php?action=edit&id=${productId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('modalFormContent').innerHTML = html;
        document.getElementById('productModal').style.display = 'block';
    })
    .catch(error => console.error('Error:', error));
}

function confirmDelete(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        window.location.href = `products.php?action=delete&id=${productId}`;
    }
}

// Close modal when clicking X or outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    const closeBtn = document.querySelector('.close-modal');
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Image Preview';
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Add event listeners for instant filtering
document.addEventListener('DOMContentLoaded', function() {
    // Get filter elements
    const filterForm = document.getElementById('filterForm');
    const categoryFilter = document.getElementById('categoryFilter');
    const brandFilter = document.getElementById('brandFilter');
    const sortFilter = document.getElementById('sortFilter');
    const searchInput = document.getElementById('searchInput');
    
    // Add change event listeners to all filter elements
    categoryFilter.addEventListener('change', submitForm);
    brandFilter.addEventListener('change', submitForm);
    sortFilter.addEventListener('change', submitForm);
    
    // Add input event listener with debounce for search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(submitForm, 500); // 500ms debounce
    });
    
    function submitForm() {
        filterForm.submit();
    }
});

</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 5px;
}

.close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: black;
}

/* Updated filter styles */
.admin-filters {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.admin-filters .search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.search-group {
    display: flex;
    width: 200px; /* Make search bar shorter */
}

.search-group input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
}

.search-btn {
    padding: 8px 12px;
    background-color: #4e73df;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
}

.filter-group {
    display: flex;
    gap: 10px;
}

.filter-group select, 
.sort-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Image preview styles */
.image-upload-group {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
}

.image-preview {
    margin-top: 10px;
    min-height: 100px;
    border: 1px dashed #ccc;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f9f9f9;
}

.image-preview img {
    max-width: 100%;
    max-height: 150px;
    object-fit: contain;
}

.product-images-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}
</style>

<?php include '../_foot.php';


