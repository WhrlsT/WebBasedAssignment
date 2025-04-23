<?php
// Prevent direct access to this file
if (!defined('INCLUDED_FROM_PROFILE')) {
    header('Location: ../profile.php');
    exit;
}
?>

<div id="change-password" class="profile-section hidden">
    <h2>Change Password</h2>
    
    <?php if ($passwordSuccess): ?>
        <div class="success-message">Password changed successfully!</div>
    <?php endif; ?>
    
    <?php if ($passwordError): ?>
        <div class="error-message"><?php echo $passwordError; ?></div>
    <?php endif; ?>
    
    <form action="" method="POST" class="password-form">
        <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="currentPassword" required>
        </div>
        
        <div class="form-group">
            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword" required>
            <small>Password must be at least 8 characters long</small>
        </div>
        
        <div class="form-group">
            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
        </div>
        
        <button type="submit" name="change_password" class="upload-button">Change Password</button>
    </form>
</div>