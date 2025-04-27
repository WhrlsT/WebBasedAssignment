<?php
require_once '../_base.php';
// Remove the SimplePager requirement
// require_once '../lib/SimplePager.php';

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
                        <?php
                        // Fetch existing image path for edit mode
                        $existingImagePath = null;
                        if ($action === 'edit' && !empty($productData['ProductID'])) {
                            $imageQuery = $pdo->prepare("SELECT picturePath FROM productpictures WHERE productID = ? AND DisplayOrder = ?");
                            $imageQuery->execute([$productData['ProductID'], $i]);
                            $existingImagePath = $imageQuery->fetchColumn();
                        }
                        ?>
                        <div class="image-upload-wrapper">
                            <input type="file" id="product_image<?php echo $i; ?>" name="product_image<?php echo $i; ?>"
                                   accept="image/*" <?php echo ($i === 1 && $action === 'add') ? 'required' : ''; ?>
                                   onchange="previewImage(this, 'preview_image<?php echo $i; ?>')"
                                   style="display: none;"  />  <?php // Hide the default input ?>

                            <div class="image-preview-box" id="preview_image<?php echo $i; ?>"
                                 onclick="triggerFileInput('product_image<?php echo $i; ?>')"
                                 ondragover="handleDragOver(event)"
                                 ondragleave="handleDragLeave(event)"
                                 ondrop="handleDrop(event, 'product_image<?php echo $i; ?>')">

                                <?php if ($existingImagePath): ?>
                                    <img src="../<?php echo htmlspecialchars($existingImagePath); ?>" alt="Product Image <?php echo $i; ?>">
                                    <span>Drag & drop or click to replace Image <?php echo $i; ?></span>
                                <?php else: ?>
                                    <span>Drag & drop or click to upload Image <?php echo $i; ?></span>
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
            
            // Update stock quantity
            $stockStmt = $pdo->prepare("UPDATE product_stocks SET Quantity = ? WHERE ProductID = ?");
            $stockStmt->execute([$_POST['product_stock'], $updateProductId]);

            // --- Start: Image Update Logic ---
            $uploadDir = '../product_images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            for ($i = 1; $i <= 5; $i++) {
                $fileField = 'product_image' . $i;

                // Check if a new file was uploaded for this slot
                if (!empty($_FILES[$fileField]['name'])) {
                    // 1. Delete old image file and DB record if it exists
                    $oldImageStmt = $pdo->prepare("SELECT picturePath FROM productpictures WHERE productID = ? AND DisplayOrder = ?");
                    $oldImageStmt->execute([$updateProductId, $i]);
                    $oldImagePath = $oldImageStmt->fetchColumn();

                    if ($oldImagePath) {
                        // Delete the old file from the server
                        $oldFileFullPath = '../' . $oldImagePath;
                        if (file_exists($oldFileFullPath)) {
                            unlink($oldFileFullPath);
                        }
                        // Delete the old record from the database
                        $deleteStmt = $pdo->prepare("DELETE FROM productpictures WHERE productID = ? AND DisplayOrder = ?");
                        $deleteStmt->execute([$updateProductId, $i]);
                    }

                    // 2. Upload new image
                    $fileName = time() . '_' . $i . '_' . basename($_FILES[$fileField]['name']);
                    $targetFile = $uploadDir . $fileName;
                    $relativePath = 'product_images/' . $fileName; // Path to store in DB

                    if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetFile)) {
                        // 3. Insert new image record into DB
                        $isCover = ($i === 1) ? 1 : 0;
                        $imageStmt = $pdo->prepare("INSERT INTO productpictures (productID, isCover, picturePath, DisplayOrder) 
                                                  VALUES (?, ?, ?, ?)");
                        $imageStmt->execute([$updateProductId, $isCover, $relativePath, $i]);
                    } else {
                        // Handle upload error if needed
                        // You might want to throw an exception or set an error message
                        // For simplicity, we'll continue, but the image won't be updated
                        // $errorMessage = "Failed to upload image " . $i; 
                        // $pdo->rollBack(); // Optionally rollback if image upload is critical
                        // break; // Exit the loop
                    }
                }
            }
            // --- End: Image Update Logic ---
            
            $pdo->commit();
            $successMessage = "Product updated successfully!";
            header("Location: products.php?success=1"); // Add success flag
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Error updating product: " . $e->getMessage();
            // Optionally redirect back with error: header("Location: products.php?error=" . urlencode($errorMessage)); exit;
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
        
        <?php 
        // Display messages based on session or URL parameters
        if (isset($_GET['success'])) {
             $successMessage = "Product operation successful!";
        }
        if (isset($_GET['error'])) {
             $errorMessage = htmlspecialchars($_GET['error']);
        }
        ?>

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
                    <select name="category" id="categoryFilter" onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['CategoryID']; ?>" <?php echo $categoryFilter == $category['CategoryID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="brand" id="brandFilter" onchange="this.form.submit()">
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
                    <select name="sort" id="sortFilter" onchange="this.form.submit()">
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
        // Define products per page
        $productsPerPage = 10; // You can adjust this value
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page); // Ensure page is at least 1
        $offset = ($page - 1) * $productsPerPage;

        // Build the base query parts
        $selectClause = "SELECT p.*, c.CategoryName, b.BrandName, ps.Quantity as stock,
                         (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) AS picturePath";
        $fromClause = " FROM products p
                        LEFT JOIN category c ON p.CategoryID = c.CategoryID
                        LEFT JOIN brand b ON p.BrandID = b.BrandID
                        LEFT JOIN product_stocks ps ON p.ProductID = ps.ProductID";
        $countSelectClause = "SELECT COUNT(p.ProductID)"; // Count based on the main table

        // Build WHERE clause for filters
        $whereConditions = [];
        $params = []; // Parameters for the main query (including LIMIT/OFFSET)
        $countParams = []; // Parameters for the count query (without LIMIT/OFFSET)

        if (!empty($search)) {
            $whereConditions[] = "(p.ProductName LIKE ? OR p.ProductDescription LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $countParams[] = $searchParam;
            $countParams[] = $searchParam;
        }

        if ($categoryFilter > 0) {
            $whereConditions[] = "p.CategoryID = ?";
            $params[] = $categoryFilter;
            $countParams[] = $categoryFilter;
        }

        if ($brandFilter > 0) {
            $whereConditions[] = "p.BrandID = ?";
            $params[] = $brandFilter;
            $countParams[] = $brandFilter;
        }

        $whereClause = "";
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
        }

        // --- Calculate Total Products ---
        $countQuery = $countSelectClause . $fromClause . $whereClause;
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($countParams);
        $totalProducts = $countStmt->fetchColumn();
        $totalPages = ceil($totalProducts / $productsPerPage);
        // Ensure page number is valid after calculating total pages
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $productsPerPage; // Recalculate offset if page was adjusted

        // --- Build Sorting ---
        $orderByClause = "";
        switch ($sort) {
            case 'id_asc': $orderByClause = " ORDER BY p.ProductID ASC"; break;
            case 'name_asc': $orderByClause = " ORDER BY p.ProductName ASC"; break;
            case 'name_desc': $orderByClause = " ORDER BY p.ProductName DESC"; break;
            case 'price_asc': $orderByClause = " ORDER BY p.ProductPrice ASC"; break;
            case 'price_desc': $orderByClause = " ORDER BY p.ProductPrice DESC"; break;
            case 'id_desc': // Fallthrough default
            default: $orderByClause = " ORDER BY p.ProductID DESC"; break;
        }

        // --- Build Final Query with LIMIT and OFFSET ---
        $query = $selectClause . $fromClause . $whereClause . $orderByClause . " LIMIT ? OFFSET ?";
        $params[] = $productsPerPage; // Add LIMIT value to params
        $params[] = $offset;          // Add OFFSET value to params

        // --- Fetch Products for the Current Page ---
        $stmt = $pdo->prepare($query);
        // PDO needs integer types for LIMIT/OFFSET, bind them explicitly
        $stmt->bindValue(count($params) - 1, $productsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(count($params), $offset, PDO::PARAM_INT);
        // Bind the rest of the parameters
        for ($i = 0; $i < count($params) - 2; $i++) {
             // Parameter indices start from 1
            $stmt->bindValue($i + 1, $params[$i]);
        }
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Removed SimplePager instantiation
        // $pager = new SimplePager($query, $params, $productsPerPage, $page);
        // $products = $pager->result;
        // $totalProducts = $pager->item_count;
        // $totalPages = $pager->page_count;
        ?>

        <div class="admin-table-container">
            <div class="table-header">
                <span>Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</span>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
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
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['picturePath'])): ?>
                                        <img src="../<?php echo htmlspecialchars($product['picturePath']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>" class="product-thumbnail">
                                    <?php else: ?>
                                        <img src="../images/placeholder.png" alt="No Image" class="product-thumbnail">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                <td><?php echo htmlspecialchars($product['CategoryName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['BrandName'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($product['ProductPrice'], 2); ?></td>
                                <td><?php echo $product['stock'] ?? 0; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="javascript:void(0);" onclick="openEditModal(<?php echo $product['ProductID']; ?>)" class="edit-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="products.php?action=delete&id=<?php echo $product['ProductID']; ?>"
                                           onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');"
                                           class="delete-btn" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-results">No products found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // --- Manual Pagination Links ---
            if ($totalPages > 1):
                // Build base URL for pagination links, preserving filters
                $queryParams = $_GET;
                unset($queryParams['page']); // Remove existing page param
                $baseUrl = 'products.php?' . http_build_query($queryParams);
                $separator = empty($queryParams) ? '' : '&';
            ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl . $separator; ?>page=<?php echo ($page - 1); ?>" class="page-link">&laquo; Previous</a>
                    <?php endif; ?>

                    <?php
                    // Determine pagination range (e.g., show 2 links before and after current page)
                    $range = 2;
                    $start = max(1, $page - $range);
                    $end = min($totalPages, $page + $range);

                    if ($start > 1) {
                        echo '<a href="' . $baseUrl . $separator . 'page=1" class="page-link">1</a>';
                        if ($start > 2) {
                            echo '<span class="page-link dots">...</span>';
                        }
                    }

                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="<?php echo $baseUrl . $separator; ?>page=<?php echo $i; ?>" class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor;

                    if ($end < $totalPages) {
                        if ($end < $totalPages - 1) {
                            echo '<span class="page-link dots">...</span>';
                        }
                        echo '<a href="' . $baseUrl . $separator . 'page=' . $totalPages . '" class="page-link">' . $totalPages . '</a>';
                    }
                    ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseUrl . $separator; ?>page=<?php echo ($page + 1); ?>" class="page-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php // --- End Manual Pagination Links --- ?>
        </div>
    </div>
</div>

<!-- Modal Structure -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="document.getElementById('productModal').style.display='none'">&times;</span>
        <div id="modalBody">
            <!-- AJAX content will be loaded here -->
            Loading...
        </div>
    </div>
</div>

<script>
// Function to open the modal for adding a product
function openAddModal() {
    fetch('products.php?action=add', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalBody').innerHTML = html;
            document.getElementById('productModal').style.display = 'block';
            initializeImagePreview(); // Re-initialize if needed
        })
        .catch(error => console.error('Error loading add form:', error));
}

// Function to open the modal for editing a product
function openEditModal(productId) {
    fetch(`products.php?action=edit&id=${productId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalBody').innerHTML = html;
            document.getElementById('productModal').style.display = 'block';
            initializeImagePreview(); // Re-initialize if needed
        })
        .catch(error => console.error('Error loading edit form:', error));
}

// Close modal if clicked outside
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Image Preview and Drag & Drop Functions
function initializeImagePreview() {
    // This function might be needed if dynamic content requires re-binding events
    // For now, assuming the initial load handles it.
}

function triggerFileInput(inputId) {
    document.getElementById(inputId).click();
}

function previewImage(input, previewId) {
    const file = input.files[0];
    const previewBox = document.getElementById(previewId);
    previewBox.innerHTML = ''; // Clear previous content

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Image Preview';
            previewBox.appendChild(img);
            const span = document.createElement('span');
            span.textContent = 'Drag & drop or click to replace';
            previewBox.appendChild(span);
        }
        reader.readAsDataURL(file);
    } else {
        // If no file is selected (e.g., user cancels), show the placeholder text
        const span = document.createElement('span');
        // Determine the image number from the previewId
        const imageNumber = previewId.replace('preview_image', '');
        span.textContent = `Drag & drop or click to upload Image ${imageNumber}`;
        previewBox.appendChild(span);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    event.currentTarget.classList.add('dragover');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    event.currentTarget.classList.remove('dragover');
}

function handleDrop(event, inputId) {
    event.preventDefault();
    event.stopPropagation();
    event.currentTarget.classList.remove('dragover');

    const files = event.dataTransfer.files;
    const inputElement = document.getElementById(inputId);

    if (files.length > 0) {
        inputElement.files = files; // Assign dropped files to the file input
        // Trigger the change event to update the preview
        const changeEvent = new Event('change', { bubbles: true });
        inputElement.dispatchEvent(changeEvent);
    }
}

// Call initialization on page load
document.addEventListener('DOMContentLoaded', initializeImagePreview);

</script>

<style>
/* Add styles for pagination dots if needed */
.pagination .dots {
    padding: 8px 12px;
    margin: 0 2px;
    color: #6c757d;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

/* Styles for image preview */
.product-images-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px; /* Increased gap */
}

.image-upload-wrapper {
    flex: 1 1 calc(20% - 15px); /* Adjust basis for 5 images per row, considering gap */
    max-width: calc(20% - 15px);
    position: relative;
}

.image-preview-box {
    border: 2px dashed #ccc;
    border-radius: 5px;
    width: 100%;
    height: 150px; /* Fixed height */
    display: flex;
    flex-direction: column; /* Stack image and text vertically */
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-align: center;
    color: #666;
    font-size: 13px; /* Slightly smaller font */
    transition: border-color 0.3s, background-color 0.3s;
    overflow: hidden; /* Hide overflow */
    position: relative; /* Needed for absolute positioning of span */
    background-color: #f9f9f9; /* Light background */
}

.image-preview-box:hover,
.image-preview-box.dragover {
    border-color: #007bff;
    background-color: #e9f5ff; /* Light blue background on hover/drag */
}

.image-preview-box img {
    max-width: 100%;
    max-height: 110px; /* Limit image height */
    object-fit: contain; /* Scale image while preserving aspect ratio */
    display: block;
    margin-bottom: 5px; /* Space between image and text */
}

.image-preview-box span {
    display: block;
    padding: 0 5px; /* Padding for the text */
    line-height: 1.3;
}

/* Ensure the span text is visible even when an image is present */
.image-preview-box img + span {
    position: absolute;
    bottom: 5px;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.7); /* Semi-transparent background for text */
    font-size: 11px;
    padding: 2px 0;
}

.product-thumbnail {
    max-width: 60px;
    max-height: 60px;
    object-fit: contain;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto; /* Adjusted margin for better centering */
    padding: 30px; /* Increased padding */
    border: 1px solid #888;
    width: 80%; /* Responsive width */
    max-width: 800px; /* Max width */
    border-radius: 8px; /* Rounded corners */
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2); /* Softer shadow */
}

.close-button {
    color: #aaa;
    position: absolute; /* Position relative to modal-content */
    top: 10px;
    right: 15px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-button:hover,
.close-button:focus {
    color: black;
    text-decoration: none;
}

/* Admin Form Styles within Modal */
.admin-form-container {
    padding: 0; /* Remove padding if modal-content has it */
}

.admin-form-container h2 {
    margin-top: 0;
    margin-bottom: 25px; /* Increased bottom margin */
    color: #333;
    border-bottom: 1px solid #eee; /* Separator line */
    padding-bottom: 10px;
}

.admin-form .form-group {
    margin-bottom: 20px; /* Increased spacing */
}

.admin-form label {
    display: block;
    margin-bottom: 8px; /* Increased space below label */
    font-weight: 600; /* Bolder labels */
    color: #555;
}

.admin-form input[type="text"],
.admin-form input[type="number"],
.admin-form input[type="file"],
.admin-form textarea,
.admin-form select {
    width: 100%;
    padding: 10px 12px; /* Adjusted padding */
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
    transition: border-color 0.3s;
}

.admin-form input:focus,
.admin-form textarea:focus,
.admin-form select:focus {
    border-color: #007bff;
    outline: none;
}

.admin-form textarea {
    resize: vertical; /* Allow vertical resize */
    min-height: 100px; /* Minimum height */
}

.admin-form .form-actions {
    margin-top: 30px; /* Increased top margin */
    display: flex;
    justify-content: flex-end; /* Align buttons to the right */
    gap: 10px; /* Space between buttons */
}

.admin-button {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.3s, color 0.3s;
}

.admin-button[type="submit"] {
    background-color: #007bff;
    color: white;
}

.admin-button[type="submit"]:hover {
    background-color: #0056b3;
}

.admin-button.cancel {
    background-color: #f8f9fa; /* Lighter background */
    color: #333;
    border: 1px solid #ccc; /* Add border */
}

.admin-button.cancel:hover {
    background-color: #e2e6ea;
}

</style>

<?php include '../_foot.php'; ?>


