


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Average Addiction' ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link href='https://fonts.googleapis.com/css?family=Varela Round' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/dropdownlist.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <!-- Logo and Store Name on the Left -->
            <div class="logo-section">
                <a href="index.php" class="d-flex align-items-center text-dark text-decoration-none">
                    <img src="/image/logo.png" alt="SigmaMart Logo" width="120" height="120">
                </a>
            </div>

            <!-- Search Bar in the Middle - Hide for admin users -->
            <?php if(!(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin')): ?>
            <div class="search-section">
                <div class="header-search">
                    <form action="product.php" method="GET">
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- User Navigation on the Right -->
            <div class="user-navigation">
                <?php if($loggedIn): ?>
                    <?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'): ?>
                        <!-- Admin Navigation -->
                        <div class="user-nav-container">
                            <?php
                            // Get fresh user data from database to ensure up-to-date information
                            $refreshStmt = $pdo->prepare("SELECT FirstName, LastName, profileimgpath FROM users WHERE UserID = ?");
                            $refreshStmt->execute([$_SESSION['user_id']]);
                            $refreshUser = $refreshStmt->fetch();
                            
                            // Update session with fresh data
                            $_SESSION['first_name'] = $refreshUser['FirstName'];
                            $_SESSION['last_name'] = $refreshUser['LastName'];
                            $_SESSION['profile_picture'] = $refreshUser['profileimgpath'];
                            
                            // Get first name and last name from updated session
                            $firstName = $_SESSION['first_name'] ?? '';
                            $lastName = $_SESSION['last_name'] ?? '';
                            $fullName = $firstName . ' ' . $lastName;
                            
                            // Get profile picture from updated session or use default
                            $profilePic = !empty($_SESSION['profile_picture']) ? 'profile_pictures/' . $_SESSION['profile_picture'] : 'image/profile-icon.jpg';
                            ?>
                            <!-- Changed link from /profile.php to /admin/index.php for admin users -->
                            <a href="/admin/index.php" class="nav-button">
                                <img src="<?php echo $profilePic[0] === '/' ? $profilePic : '/' . $profilePic; ?>" alt="Profile" class="profile-pic">
                                <span class="username"><?php echo $fullName; ?> (Admin)</span>
                            </a>
                            <!-- Rest of admin navigation -->
                        </div>
                    <?php else: ?>
                        <!-- Regular User Navigation -->
                        <div class="user-nav-container">
                            <?php
                            // Get fresh user data from database to ensure up-to-date information
                            $refreshStmt = $pdo->prepare("SELECT FirstName, LastName, profileimgpath FROM users WHERE UserID = ?");
                            $refreshStmt->execute([$_SESSION['user_id']]);
                            $refreshUser = $refreshStmt->fetch();
                            
                            // Update session with fresh data
                            $_SESSION['first_name'] = $refreshUser['FirstName'];
                            $_SESSION['last_name'] = $refreshUser['LastName'];
                            $_SESSION['profile_picture'] = $refreshUser['profileimgpath'];
                            
                            // Get first name and last name from updated session
                            $firstName = $_SESSION['first_name'] ?? '';
                            $lastName = $_SESSION['last_name'] ?? '';
                            $fullName = $firstName . ' ' . $lastName;
                            
                            // Get profile picture from updated session or use default
                            $profilePic = !empty($_SESSION['profile_picture']) ? 'profile_pictures/' . $_SESSION['profile_picture'] : 'image/profile-icon.jpg';
                            ?>
                            <a href="profile.php" class="nav-button">
                                <img src="<?php echo $profilePic; ?>" alt="Profile" class="profile-pic">
                                <span class="username"><?php echo $fullName; ?></span>
                            </a>
                            <!-- Cart button links directly to cart page -->
                            <a href="cart.php" class="nav-button">
                                <img src="image/cart_logo.png" alt="Cart">
                                My Cart
                            </a>
                            <!-- Rest of user navigation -->
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- User is not logged in, display login and cart buttons side by side -->
                    <div class="user-nav-container">
                        <a href="login.php" class="nav-button">
                            <img src="image/profile-icon.png" alt="User">
                            User LogIn
                        </a>
                        <!-- Cart button redirects to login page -->
                        <a href="login.php?redirect=cart" class="nav-button">
                            <img src="image/cart_logo.png" alt="Cart">
                            My Cart
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Category Navigation Bar - Hide for admin users -->
    <?php if(!(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin')): ?>
    <nav class="category-nav">
        <ul class="category-list">
            <?php
            try {
                // Get all categories from database
                $navCategories = $pdo->query("SELECT CategoryID, CategoryName FROM category ORDER BY CategoryName")->fetchAll();
                
                // Add "All Products" as first option
                echo '<li class="category-item">';
                echo '<a href="/product.php" class="category-link' . (!isset($_GET['category']) ? ' active' : '') . '">All Products</a>';
                echo '</li>';
                
                // Display each category with link to filtered products
                foreach ($navCategories as $cat) {
                    echo '<li class="category-item">';
                    echo '<a href="/product.php?category=' . $cat['CategoryID'] . '&from=nav" class="category-link">' . htmlspecialchars($cat['CategoryName']) . '</a>';
                    echo '</li>';
                }
            } catch (PDOException $e) {
                // Silently handle database errors to prevent breaking the page
                echo '<li class="category-item"><a href="/product.php" class="category-link">All Products</a></li>';
            }
            ?>
        </ul>
    </nav>
    <?php endif; ?>

    <main>
        <!-- Main content goes here -->
    </main>
</body>
</html>

