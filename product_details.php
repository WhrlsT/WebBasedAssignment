<?php
require_once '_base.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no product ID provided
if ($productId <= 0) {
    header('Location: product.php');
    exit;
}

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.CategoryName, b.BrandName, ps.Quantity as stock
    FROM products p
    LEFT JOIN category c ON p.CategoryID = c.CategoryID
    LEFT JOIN brand b ON p.BrandID = b.BrandID
    LEFT JOIN product_stocks ps ON p.ProductID = ps.ProductID
    WHERE p.ProductID = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if product not found
if (!$product) {
    header('Location: product.php');
    exit;
}

// Get product images
$stmt = $pdo->prepare("
    SELECT * FROM productpictures 
    WHERE productID = ? 
    ORDER BY isCover DESC, DisplayOrder ASC
");
$stmt->execute([$productId]);
$productImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related products (same category)
$stmt = $pdo->prepare("
    SELECT p.*, 
    (SELECT picturePath FROM productpictures WHERE productID = p.ProductID AND isCover = 1 LIMIT 1) as ImagePath
    FROM products p
    WHERE p.CategoryID = ? AND p.ProductID != ?
    ORDER BY RAND()
    LIMIT 4
");
$stmt->execute([$product['CategoryID'], $productId]);
$relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_title = htmlspecialchars($product['ProductName']) . ' - SigmaMart';
include '_head.php';
?>

<div class="product-details-container">
    <div class="breadcrumb">
        <a href="index.php">Home</a> &gt; 
        <a href="product.php">Products</a> &gt; 
        <a href="product.php?category=<?php echo $product['CategoryID']; ?>"><?php echo htmlspecialchars($product['CategoryName']); ?></a> &gt; 
        <span><?php echo htmlspecialchars($product['ProductName']); ?></span>
    </div>

    <div class="product-details-grid">
        <div class="product-images">
            <?php if (!empty($productImages)): ?>
                <div class="main-image-slider">
                    <div class="slider-container">
                        <?php foreach ($productImages as $index => $image): ?>
                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($image['picturePath']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($productImages) > 1): ?>
                        <button class="slider-nav prev">&lt;</button>
                        <button class="slider-nav next">&gt;</button>
                    <?php endif; ?>
                </div>
                <?php if (count($productImages) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach ($productImages as $index => $image): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <img src="<?php echo htmlspecialchars($image['picturePath']); ?>" alt="Thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="main-image">
                    <img src="image/product-placeholder.jpg" alt="No Image Available">
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['ProductName']); ?></h1>
            
            <div class="product-meta">
                <span class="product-brand">Brand: <a href="product.php?brand=<?php echo $product['BrandID']; ?>"><?php echo htmlspecialchars($product['BrandName']); ?></a></span>
                <span class="product-category">Category: <a href="product.php?category=<?php echo $product['CategoryID']; ?>"><?php echo htmlspecialchars($product['CategoryName']); ?></a></span>
            </div>
            
            <div class="product-price">RM<?php echo number_format($product['ProductPrice'], 2); ?></div>
            
            <div class="product-stock">
                <?php if ($product['stock'] > 0): ?>
                    <span class="in-stock">In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php else: ?>
                    <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <div class="product-description">
                <h3>Description</h3>
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($product['ProductDescription'])); ?>
                </div>
            </div>
            
            <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn minus">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <button type="button" class="quantity-btn plus">+</button>
                    </div>
                </div>
                
                <button type="submit" class="add-to-cart-btn" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($relatedProducts)): ?>
    <div class="related-products">
        <h2>Related Products</h2>
        <div class="product-grid">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?php echo $relatedProduct['ProductID']; ?>">
                        <div class="product-image">
                            <?php if (!empty($relatedProduct['ImagePath'])): ?>
                                <img src="<?php echo htmlspecialchars($relatedProduct['ImagePath']); ?>" alt="<?php echo htmlspecialchars($relatedProduct['ProductName']); ?>">
                            <?php else: ?>
                                <img src="image/product-placeholder.jpg" alt="No Image">
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($relatedProduct['ProductName']); ?></h3>
                            <div class="product-price">RM<?php echo number_format($relatedProduct['ProductPrice'], 2); ?></div>
                        </div>
                    </a>
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $relatedProduct['ProductID']; ?>, 1)">Add to Cart</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="js/cart.js"></script>
<?php include '_foot.php'; ?>

<!-- Add this before the closing body tag or before including _foot.php -->
<?php if (!empty($productImages) && count($productImages) > 1): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const thumbnails = document.querySelectorAll('.thumbnail');
    const prevBtn = document.querySelector('.slider-nav.prev');
    const nextBtn = document.querySelector('.slider-nav.next');
    let currentIndex = 0;
    
    // Function to show a specific slide
    function showSlide(index) {
        // Validate index
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        
        // Hide all slides
        slides.forEach(slide => {
            slide.style.display = 'none';
            slide.classList.remove('active');
        });
        
        // Remove active class from all thumbnails
        thumbnails.forEach(thumb => {
            thumb.classList.remove('active');
        });
        
        // Show the selected slide
        slides[index].style.display = 'block';
        slides[index].classList.add('active');
        thumbnails[index].classList.add('active');
        
        // Update current index
        currentIndex = index;
    }
    
    // Initialize the slider
    showSlide(0);
    
    // Event listeners for navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSlide(currentIndex - 1);
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSlide(currentIndex + 1);
        });
    }
    
    // Event listeners for thumbnails
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', function() {
            showSlide(index);
        });
    });
    
    // Optional: Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            showSlide(currentIndex - 1);
        } else if (e.key === 'ArrowRight') {
            showSlide(currentIndex + 1);
        }
    });
    
    // Optional: Add swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    const sliderContainer = document.querySelector('.slider-container');
    
    sliderContainer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    sliderContainer.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            // Swipe left, show next slide
            showSlide(currentIndex + 1);
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right, show previous slide
            showSlide(currentIndex - 1);
        }
    }
});
</script>
<?php endif; ?>

<style>
/* Product Image Slider Styles */
.product-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.product-images {
    position: relative;
}

.main-image-slider {
    position: relative;
    width: 100%;
    margin-bottom: 15px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.slider-container {
    position: relative;
    width: 100%;
    height: 400px;
    background-color: #fff;
}

.slide {
    position: relative;
    width: 100%;
    height: 100%;
    display: none;
}

.slide.active {
    display: block;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(255, 255, 255, 0.8);
    color: #333;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.slider-nav:hover {
    background-color: #fff;
    transform: translateY(-50%) scale(1.1);
}

.slider-nav.prev {
    left: 10px;
}

.slider-nav.next {
    right: 10px;
}

.thumbnail-images {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 5px 0;
    scrollbar-width: thin;
}

.thumbnail {
    flex: 0 0 80px;
    height: 80px;
    border-radius: 5px;
    overflow: hidden;
    cursor: pointer;
    opacity: 0.7;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.thumbnail:hover {
    opacity: 0.9;
}

.thumbnail.active {
    opacity: 1;
    border-color: #4a90e2;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-details-grid {
        grid-template-columns: 1fr;
    }
    
    .slider-container {
        height: 300px;
    }
    
    .thumbnail {
        flex: 0 0 60px;
        height: 60px;
    }
}
</style>