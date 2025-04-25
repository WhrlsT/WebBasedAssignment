<?php
require '_base.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $loginIdentifier = $_POST['loginIdentifier'] ?? '';
    $password = $_POST['loginPassword'] ?? '';
    $rememberMe = isset($_POST['rememberMe']);

    // Validate form data
    if (empty($loginIdentifier) || empty($password)) {
        header("Location: login.php?error=empty_fields");
        exit;
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE UserName = ? OR Email = ?");
        $stmt->execute([$loginIdentifier, $loginIdentifier]);
        $user = $stmt->fetch();

        if (!$user) {
            header("Location: login.php?error=invalid_credentials");
            exit;
        }

        // --- Add Deactivation Check ---
        if (!isset($user['is_active']) || $user['is_active'] == 0) {
            // User account is deactivated
            header("Location: login.php?error=account_deactivated");
            exit;
        }
        // --- End Deactivation Check ---


        // Verify password (assuming old sha1, ideally switch to password_verify)
        // IMPORTANT: You are using sha1 which is insecure. You should migrate to password_hash() and password_verify().
        // The admin panel uses password_hash(), so there's an inconsistency.
        // For this example, I'll keep the sha1 check as it is in your original code,
        // but strongly recommend updating password handling across the application.
        if (sha1($password) === $user['Password']) {

            // Check if email is verified for registration (only if is_verified column exists)
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                // Generate OTP for verification
                $otp = generate_otp();
                $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                // Store OTP in database
                $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code, expires_at, type) VALUES (?, ?, ?, 'registration')");
                $stmt->execute([$user['Email'], $otp, $expires]);

                // Send OTP email
                send_otp_email($user['Email'], $otp);

                // Store email in session for verification page
                $_SESSION['temp_email'] = $user['Email'];
                $_SESSION['last_otp_sent'] = time();

                // Redirect to verification page
                header("Location: verify_code.php?type=registration");
                exit;
            }

            // Check if user is admin - require OTP verification
            if ($user['UserType'] === 'Admin') {
                // Generate OTP for login
                $otp = generate_otp();
                $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                // Store OTP in database using 'login' type
                $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code, expires_at, type) VALUES (?, ?, ?, 'login')");
                $stmt->execute([$user['Email'], $otp, $expires]);

                // Send OTP email
                send_admin_otp_email($user['Email'], $otp);

                // Store admin data in session for verification
                $_SESSION['temp_email'] = $user['Email'];
                $_SESSION['temp_user_id'] = $user['UserID'];
                $_SESSION['temp_user_data'] = [
                    'first_name' => $user['FirstName'],
                    'last_name' => $user['LastName'],
                    'username' => $user['UserName'],
                    'email' => $user['Email'],
                    'user_type' => $user['UserType'],
                    'profile_picture' => $user['profileimgpath']
                ];
                $_SESSION['last_otp_sent'] = time();

                // Set remember me data if selected
                if ($rememberMe) {
                    $_SESSION['remember_me'] = true;
                }

                // Redirect to verification page with login type
                header("Location: verify_code.php?type=login&admin_verify=true");
                exit;
            }

            // For regular users, set remember me cookie if selected
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days

                // Store token in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE UserID = ?");
                $stmt->execute([$token, $user['UserID']]);

                // Set cookie
                setcookie('remember_token', $token, $expires, '/');
            }

            // Set session data for regular users
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['last_name'] = $user['LastName'];
            $_SESSION['username'] = $user['UserName'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['user_type'] = $user['UserType'];
            $_SESSION['profile_picture'] = $user['profileimgpath'];

            // Add this line to set is_admin flag
            $_SESSION['is_admin'] = ($user['UserType'] === 'Admin') ? 1 : 0;

            // Redirect to home page
            header("Location: index.php");
            exit;
        } else {
            header("Location: login.php?error=invalid_credentials");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php?error=database_error");
        exit;
    }
} else {
    // Redirect to login page if accessed directly
    header("Location: login.php");
    exit;
}

// Function to send admin OTP email (keep as is or modify if needed)
function send_admin_otp_email($email, $otp) {
    // You can customize this function to send a special admin OTP email
    // For now, we'll reuse the existing send_otp_email function
    send_otp_email($email, $otp);
}

// Make sure generate_otp() and send_otp_email() are defined in _base.php or included files
?>
