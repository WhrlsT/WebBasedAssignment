<?php
// Include base file which has database connection and session handling
include '_base.php';

// Check  is logged in
if (!$loggedIn) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize variables for messages
$updateSuccess = false;
$updateError = false;
$passwordSuccess = false;
$passwordError = false;
$pictureSuccess = false;
$pictureError = false;

// Handle profile picture upload
if (isset($_POST['upload_picture'])) {
    // Check if file was uploaded without errors
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profilePicture']['name'];
        $filesize = $_FILES['profilePicture']['size'];
        $filetype = $_FILES['profilePicture']['type'];
        $tmp_name = $_FILES['profilePicture']['tmp_name'];
        
        // Validate file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $pictureError = "Error: Please select a valid file format (JPG, JPEG, PNG, GIF).";
        } else if ($filesize > 5242880) { // 5MB max
            $pictureError = "Error: File size must be less than 5MB.";
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = 'profile_pictures/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate a unique filename
            $new_filename = $userId . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Move the file
            if (move_uploaded_file($tmp_name, $upload_path)) {
                // Delete old profile picture if exists
                if (!empty($user['profileimgpath']) && file_exists('profile_pictures/' . $user['profileimgpath'])) {
                    unlink('profile_pictures/' . $user['profileimgpath']);
                }
                
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET profileimgpath = ? WHERE UserID = ?");
                if ($stmt->execute([$new_filename, $userId])) {
                    $pictureSuccess = true;
                    
                    // Update user data
                    $user['profileimgpath'] = $new_filename;
                } else {
                    $pictureError = "Error: Failed to update database.";
                }
            } else {
                $pictureError = "Error: Failed to upload file.";
            }
        }
    } else {
        $pictureError = "Error: Please select a file to upload.";
    }
}

// Handle profile information update
if (isset($_POST['update_profile'])) {
    // Get form data
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $currentPassword = $_POST['currentPassword'] ?? '';

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($email) || empty($currentPassword)) {
        $updateError = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $updateError = "Please enter a valid email address.";
    } else {
        // Verify current password using SHA1
        $stmt = $pdo->prepare("SELECT Password FROM users WHERE UserID = ?");
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();

        // SHA1 encryption for current password verification
        $currentPasswordHash = sha1($currentPassword);

        if ($currentPasswordHash === $storedHash) {
            // Check if email is changed
            if ($email != $user['Email']) {
                // Email changed - For now, just update it
                // You could add email verification here if needed
            }

            // Update user information
            $stmt = $pdo->prepare("UPDATE users SET FirstName = ?, LastName = ?, Email = ? WHERE UserID = ?");
            if ($stmt->execute([$firstName, $lastName, $email, $userId])) {
                $updateSuccess = true;

                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE UserID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $updateError = "Failed to update profile. Please try again.";
            }
        } else {
            $updateError = "Incorrect password. Please try again.";
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    // Get form data
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = "All password fields are required.";
    } elseif (strlen($newPassword) < 8) {
        $passwordError = "New password must be at least 8 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = "New passwords do not match.";
    } else {
        // Verify current password using SHA1
        $stmt = $pdo->prepare("SELECT Password FROM users WHERE UserID = ?");
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();

        // SHA1 encryption for current password to compare with stored hash
        $currentPasswordHash = sha1($currentPassword);

        if ($currentPasswordHash === $storedHash) {
            // Encrypt the new password with SHA1
            $newPasswordHash = sha1($newPassword);

            // Update password in database
            $stmt = $pdo->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
            if ($stmt->execute([$newPasswordHash, $userId])) {
                $passwordSuccess = true;
            } else {
                $passwordError = "Failed to update password. Please try again.";
            }
        } else {
            $passwordError = "Current password is incorrect.";
        }
    }
}

$_title = 'My Profile';
include '_head.php';

// Define a constant to prevent direct access to tab files
define('INCLUDED_FROM_PROFILE', true);
?>

<div class="profile-page">
    <div class="profile-header">
        <h1>My Profile</h1>
        <p>Manage your account information and settings</p>
    </div>
    
    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-picture-container">
                <?php
                // Check if user has a profile picture, otherwise use empty_image.jpg
                $profilePic = !empty($user['profileimgpath']) ? 'profile_pictures/' . $user['profileimgpath'] : 'image/empty_image.jpg';
                ?>
                <img src="<?php echo $profilePic; ?>" alt="Profile Picture" class="profile-picture" id="profile-preview">
                
                <form action="" method="POST" enctype="multipart/form-data" class="picture-form">
                    <div class="file-input-container">
                        <input type="file" id="profilePicture" name="profilePicture" accept="image/*" onchange="previewProfileImage(this)">
                        <label for="profilePicture" class="file-input-label">Choose File</label>
                    </div>
                    
                    <button type="submit" name="upload_picture" class="upload-button">Upload Picture</button>
                    
                    <?php if ($pictureSuccess): ?>
                        <div class="success-message">Profile picture updated successfully!</div>
                    <?php endif; ?>
                    
                    <?php if ($pictureError): ?>
                        <div class="error-message"><?php echo $pictureError; ?></div>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="profile-menu">
                <a href="#profile-info" class="menu-item active">Profile Information</a>
                <a href="#change-password" class="menu-item">Change Password</a>
                <a href="#order-history" class="menu-item">Order History</a>
                <a href="logout.php" class="menu-item logout">Logout</a>
            </div>
        </div>
        
        <div class="profile-details">
            <?php 
            // Include the profile tabs
            include 'profile_tabs/profile_info.php';
            include 'profile_tabs/change_password.php';
            include 'profile_tabs/order_history.php';
            ?>
        </div>
    </div>
</div>

<script>
// Profile menu navigation
document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.profile-menu .menu-item');
    const sections = document.querySelectorAll('.profile-section');
    
    // Check for tab parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam) {
        // Map URL parameter to section ID
        const tabMap = {
            'info': 'profile-info',
            'password': 'change-password',
            'orders': 'order-history'
        };
        
        const targetId = tabMap[tabParam];
        
        if (targetId) {
            // Hide all sections
            sections.forEach(section => section.classList.add('hidden'));
            
            // Show the target section
            document.getElementById(targetId).classList.remove('hidden');
            
            // Update menu active state
            menuItems.forEach(mi => mi.classList.remove('active'));
            document.querySelector(`.menu-item[href="#${targetId}"]`).classList.add('active');
        }
    }
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.classList.contains('logout')) return; // Skip for logout link
            
            e.preventDefault();
            
            // Remove active class from all menu items
            menuItems.forEach(mi => mi.classList.remove('active'));
            
            // Add active class to clicked menu item
            this.classList.add('active');
            
            // Hide all sections
            sections.forEach(section => section.classList.add('hidden'));
            
            // Show the target section
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).classList.remove('hidden');
            
            // Update URL without reloading the page
            const tabParam = {
                'profile-info': 'info',
                'change-password': 'password',
                'order-history': 'orders'
            }[targetId];
            
            if (tabParam) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tabParam);
                window.history.pushState({}, '', url);
            }
        });
    });
    
    // Profile picture preview
    window.previewProfileImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('profile-preview').src = e.target.result;
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    };
    
    // Confirm profile update
    document.getElementById('confirm-update').addEventListener('click', function() {
        // Simple validation
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;
        const email = document.getElementById('email').value;
        const currentPassword = document.getElementById('currentPassword').value;
        
        if (!firstName || !lastName || !email || !currentPassword) {
            alert('Please fill in all required fields.');
            return;
        }
        
        if (!validateEmail(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Submit the form
        document.getElementById('profile-form').submit();
    });
    
    // Email validation function
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
});
</script>                

<?php include '_foot.php'; ?>