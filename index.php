<?php
require_once '_base.php';

// Get featured products
$featuredProductsQuery = "
    SELECT p.*, 
    (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath,
    b.BrandName, c.CategoryName
    FROM products p
    LEFT JOIN brand b ON p.BrandID = b.BrandID
    LEFT JOIN category c ON p.CategoryID = c.CategoryID
    WHERE (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) IS NOT NULL
    ORDER BY p.sales_count DESC, p.date_added DESC
    LIMIT 4
";
$featuredProducts = $pdo->query($featuredProductsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Get new arrivals
$newArrivalsQuery = "
    SELECT p.*, 
    (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath,
    b.BrandName, c.CategoryName
    FROM products p
    LEFT JOIN brand b ON p.BrandID = b.BrandID
    LEFT JOIN category c ON p.CategoryID = c.CategoryID
    WHERE (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) IS NOT NULL
    ORDER BY p.date_added DESC
    LIMIT 4
";
$newArrivals = $pdo->query($newArrivalsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Get popular brands
$popularBrandsQuery = "
    SELECT b.*, COUNT(p.ProductID) as product_count
    FROM brand b
    JOIN products p ON b.BrandID = p.BrandID
    GROUP BY b.BrandID
    ORDER BY product_count DESC
    LIMIT 10
";
$popularBrands = $pdo->query($popularBrandsQuery)->fetchAll(PDO::FETCH_ASSOC);

$_title = 'SigmaMart - Collectibles, Plushies & Trading Cards';
include '_head.php';
?>

<!-- Hero Section with Video, Slider and Description -->
<section class="home_hero-section">
    <div class="home_hero-container">
        <!-- Video Section -->
        <div class="home_video-container">
            <h2>Welcome to SigmaMart</h2>
            <div class="home_youtube-embed">
                <iframe width="100%" height="400" src="https://www.youtube.com/embed/VS2xRMmXFoo?si=WGzZE5LqkcQ_QouS&autoplay=1" title="SigmaMart Introduction" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
        
        <!-- Description Section -->
        <div class="home_description-container">
            <div class="home_description-content">
                <h2>About SigmaMart</h2>
                <p>Welcome to SigmaMart, your ultimate destination for collectible blind boxes, adorable plushies, and rare trading cards. We curate the finest selection of collectibles from around the world, bringing joy to collectors of all ages.</p>
                <p>Whether you're a seasoned collector or just starting your journey, SigmaMart offers a diverse range of products from popular franchises and independent artists alike. Discover the thrill of unboxing mystery collectibles, find your next cuddly companion, or complete your trading card collection.</p>
                <div class="home_features">
                    <div class="home_feature">
                        <i class="fas fa-shipping-fast"></i>
                        <h3>Fast Shipping</h3>
                        <p>Quick delivery nationwide</p>
                    </div>
                    <div class="home_feature">
                        <i class="fas fa-check-circle"></i>
                        <h3>Authentic Products</h3>
                        <p>100% genuine items</p>
                    </div>
                    <div class="home_feature">
                        <i class="fas fa-headset"></i>
                        <h3>Customer Support</h3>
                        <p>Available 7 days a week</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="home_featured-categories">
    <div class="home_category-container">
        <div class="home_section-header">
            <h2>SHOP BY CATEGORY</h2>
        </div>
        <div class="home_category-grid">
            <a href="product.php?category=1001" class="home_category-card">
                <div class="home_category-image">
                    <img src="image/category-blindbox.png" alt="Blind Boxes">
                </div>
                <div class="home_category-info">
                    <h3>Blind Boxes</h3>
                    <p>Unbox the mystery</p>
                    <span class="home_shop-now">Shop Now →</span>
                </div>
            </a>
            <a href="product.php?category=1002" class="home_category-card">
                <div class="home_category-image">
                    <img src="image/category-plush.png" alt="Plush Toys">
                </div>
                <div class="home_category-info">
                    <h3>Plush Toys</h3>
                    <p>Cuddly companions</p>
                    <span class="home_shop-now">Shop Now →</span>
                </div>
            </a>
            <a href="product.php?category=1003" class="home_category-card">
                <div class="home_category-image">
                    <img src="image/category-cards.png" alt="Trading Cards">
                </div>
                <div class="home_category-info">
                    <h3>Trading Cards</h3>
                    <p>Build your collection</p>
                    <span class="home_shop-now">Shop Now →</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Popular Brands Section -->
<section class="home_popular-brands">
    <div class="home_category-container">
        <div class="home_section-header">
            <h2>Shop by Brand</h2>
        </div>
        <div class="home_brands-grid">
            <?php 
            // Display all brands in a grid
            foreach ($popularBrands as $brand): 
            ?>
                <a href="product.php?brand=<?php echo $brand['BrandID']; ?>" class="home_brand-card">
                    <h3><?php echo htmlspecialchars($brand['BrandName']); ?></h3>
                    <span class="home_product-count"><?php echo $brand['product_count']; ?> products</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals Section -->
<section class="home_new-arrivals">
    <div class="home_category-container">
        <div class="home_section-header">
            <h2>New Arrivals</h2>
        </div>
        <div class="home_product-grid">
            <?php foreach ($newArrivals as $product): ?>
                <div class="home_product-card">
                    <div class="home_product-badge">New</div>
                    <a href="product_details.php?id=<?php echo $product['ProductID']; ?>" class="home_product-link">
                        <div class="home_product-image">
                            <img src="<?php echo $product['ImagePath']; ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                        </div>
                        <div class="home_product-info">
                            <h3 class="home_product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                            <p class="home_product-brand"><?php echo htmlspecialchars($product['BrandName']); ?></p>
                            <p class="home_product-price">RM<?php echo number_format($product['ProductPrice'], 2); ?></p>
                        </div>
                    </a>
                    <button class="home_add-to-cart-btn" data-product-id="<?php echo $product['ProductID']; ?>">Add to Cart</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="home_featured-products">
    <div class="home_category-container">
        <div class="home_section-header">
            <h2>Featured Products</h2>
        </div>
        <div class="home_product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="home_product-card">
                    <?php if ($product['sales_count'] > 5): ?>
                        <div class="home_product-badge bestseller">Bestseller</div>
                    <?php endif; ?>
                    <a href="product_details.php?id=<?php echo $product['ProductID']; ?>" class="home_product-link">
                        <div class="home_product-image">
                            <img src="<?php echo $product['ImagePath']; ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                        </div>
                        <div class="home_product-info">
                            <h3 class="home_product-name"><?php echo htmlspecialchars($product['ProductName']); ?></h3>
                            <p class="home_product-brand"><?php echo htmlspecialchars($product['BrandName']); ?></p>
                            <p class="home_product-price">RM<?php echo number_format($product['ProductPrice'], 2); ?></p>
                        </div>
                    </a>
                    <button class="home_add-to-cart-btn" data-product-id="<?php echo $product['ProductID']; ?>">Add to Cart</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include '_foot.php'; ?>

<!-- Add the cart popup HTML structure -->
<div id="cart-popup-overlay" class="cart-popup-overlay" style="display: none;"></div>
<div id="cart-popup" class="cart-popup" style="display: none;">
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
</div>

<!-- Include the cart.js file which contains the popup functions -->
<script src="js/cart.js"></script>

<script>
// Initialize add to cart buttons on the homepage
document.addEventListener('DOMContentLoaded', function() {
    // Get all add to cart buttons with the home_add-to-cart-btn class
    const addToCartButtons = document.querySelectorAll('.home_add-to-cart-btn');
    
    // Add click event listener to each button
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            addToCart(productId, 1); // Call the addToCart function from cart.js
        });
    });
});
</script>