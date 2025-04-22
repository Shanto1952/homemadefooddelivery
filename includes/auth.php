<?php
require_once 'db.php';
require_once 'functions.php';

session_start();

// Register a new user
function registerUser($name, $email, $phone, $address, $password, $userType) {
    global $db;
    
    // Check if email already exists
    $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Check password strength
    $passwordCheck = isStrongPassword($password);
    if (!$passwordCheck['valid']) {
        return ['success' => false, 'message' => $passwordCheck['message']];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Generate verification code
    $verificationCode = generateVerificationCode();
    
    // Insert user into database
    $userId = $db->insert(
        "INSERT INTO users (name, email, phone, address, password, user_type, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$name, $email, $phone, $address, $hashedPassword, $userType, $verificationCode]
    );
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
    
    // Send verification email
    $emailSent = sendVerificationEmail($email, $name, $verificationCode);
    
    if (!$emailSent) {
        return [
            'success' => true, 
            'message' => 'Registration successful but failed to send verification email. Please contact support.',
            'user_id' => $userId,
            'verification_code' => $verificationCode // For development, return the code
        ];
    }
    
    return [
        'success' => true, 
        'message' => 'Registration successful. Please check your email for verification code.',
        'user_id' => $userId,
        'verification_code' => $verificationCode // For development, return the code
    ];
}

// Verify user with code
function verifyUser($userId, $code) {
    global $db;
    
    // For debugging
    error_log("Verifying user ID: $userId with code: $code");
    
    $user = $db->selectOne("SELECT * FROM users WHERE id = ? AND verification_code = ?", [$userId, $code]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid verification code'];
    }
    
    $updated = $db->update(
        "UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?",
        [$userId]
    );
    
    if (!$updated) {
        return ['success' => false, 'message' => 'Verification failed. Please try again.'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['last_activity'] = time();
    
    return ['success' => true, 'message' => 'Account verified successfully. You are now logged in.'];
}
// Login user
function loginUser($email, $password) {
    global $db;
    
    $user = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    if (!$user['is_verified']) {
        // Generate new verification code
        $verificationCode = generateVerificationCode();
        
        $db->update(
            "UPDATE users SET verification_code = ? WHERE id = ?",
            [$verificationCode, $user['id']]
        );
        
        sendVerificationEmail($user['email'], $user['name'], $verificationCode);
        
        return [
            'success' => false, 
            'message' => 'Account not verified. A new verification code has been sent to your email.',
            'user_id' => $user['id'],
            'needs_verification' => true,
            'verification_code' => $verificationCode // For development, return the code
        ];
    }
    
    // Generate 2FA code for login
    $twoFactorCode = generateVerificationCode();
    
    $db->update(
        "UPDATE users SET verification_code = ? WHERE id = ?",
        [$twoFactorCode, $user['id']]
    );
    
    sendVerificationEmail($user['email'], $user['name'], $twoFactorCode);
    
    return [
        'success' => true, 
        'message' => 'Please enter the verification code sent to your email.',
        'user_id' => $user['id'],
        'needs_2fa' => true,
        'verification_code' => $twoFactorCode // For development, return the code
    ];
}

// Verify 2FA code
function verify2FA($userId, $code) {
    global $db;
    
    // For debugging
    error_log("Verifying 2FA for user ID: $userId with code: $code");
    
    $user = $db->selectOne("SELECT * FROM users WHERE id = ? AND verification_code = ?", [$userId, $code]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid verification code'];
    }
    
    // Clear verification code
    $db->update(
        "UPDATE users SET verification_code = NULL WHERE id = ?",
        [$userId]
    );
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['last_activity'] = time();
    
    return [
        'success' => true, 
        'message' => 'Login successful',
        'user_type' => $user['user_type']
    ];
}

// Logout user
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    return ['success' => true, 'message' => 'Logged out successfully'];
}

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) { // 30 minutes
        logoutUser();
        return true;
    }
    
    $_SESSION['last_activity'] = time();
    return false;
}

// Reset password
function resetPassword($email) {
    global $db;
    
    $user = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Email not found'];
    }
    
    // Generate reset code
    $resetCode = generateVerificationCode();
    
    $db->update(
        "UPDATE users SET verification_code = ? WHERE id = ?",
        [$resetCode, $user['id']]
    );
    
    sendVerificationEmail($user['email'], $user['name'], $resetCode);
    
    return [
        'success' => true, 
        'message' => 'Password reset code has been sent to your email',
        'user_id' => $user['id'],
        'verification_code' => $resetCode // For development, return the code
    ];
}

// Update password
function updatePassword($userId, $code, $newPassword) {
    global $db;
    
    $user = $db->selectOne("SELECT * FROM users WHERE id = ? AND verification_code = ?", [$userId, $code]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid verification code'];
    }
    
    // Check password strength
    $passwordCheck = isStrongPassword($newPassword);
    if (!$passwordCheck['valid']) {
        return ['success' => false, 'message' => $passwordCheck['message']];
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    
    // Update password and clear verification code
    $updated = $db->update(
        "UPDATE users SET password = ?, verification_code = NULL WHERE id = ?",
        [$hashedPassword, $userId]
    );
    
    if (!$updated) {
        return ['success' => false, 'message' => 'Password update failed. Please try again.'];
    }
    
    return ['success' => true, 'message' => 'Password updated successfully. You can now log in with your new password.'];
}
?>