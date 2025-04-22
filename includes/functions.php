<?php
require_once 'db.php';

// Generate a random verification code
function generateVerificationCode($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Check if password is strong
function isStrongPassword($password) {
    // At least 8 characters
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    
    // At least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
    }
    
    // At least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
    }
    
    // At least one number
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number'];
    }
    
    // At least one special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one special character'];
    }
    
    return ['valid' => true, 'message' => 'Password is strong'];
}

// Send verification code via email (simplified version without PHPMailer dependency)
function sendVerificationEmail($email, $name, $code) {
    $to = $email;
    $subject = 'Your Verification Code for Homemade Food Delivery';
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Homemade Food Delivery <noreply@homemadefood.com>' . "\r\n";
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .code { font-size: 24px; font-weight: bold; color: #e67e22; letter-spacing: 5px; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Welcome to Homemade Food Delivery!</h2>
            <p>Hello ' . $name . ',</p>
            <p>Thank you for registering. To complete your registration, please use the following verification code:</p>
            <p class="code">' . $code . '</p>
            <p>This code will expire in 15 minutes.</p>
            <p>If you did not request this code, please ignore this email.</p>
            <div class="footer">
                <p>© ' . date('Y') . ' Homemade Food Delivery. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // For development purposes
    error_log("Verification code for $email: $code");
    
    // return mail($to, $subject, $message, $headers);
    
    return true;
}

// Clean and validate input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user type
function isUserType($type) {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $type;
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

// Display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}



// Get user data
function getUserData($userId) {
    global $db;
    return $db->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);
}

// // Get food items by chef
function getFoodItemsByChef($chefId) {
    global $db;
    return $db->select("SELECT * FROM food_items WHERE chef_id = ? ORDER BY created_at DESC", [$chefId]);
}

// Get nearby food items
function getNearbyFoodItems($limit = 10) {
    global $db;
    return $db->select("
        SELECT f.*, u.name as chef_name 
        FROM food_items f 
        JOIN users u ON f.chef_id = u.id 
        WHERE f.is_available = 1 
        ORDER BY RAND() 
        LIMIT ?", [$limit]);
}

// Get order details
function getOrderDetails($orderId) {
    global $db;
    $order = $db->selectOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
    
    if (!$order) {
        return null;
    }
    
    $items = $db->select("
        SELECT oi.*, f.name, f.image 
        FROM order_items oi 
        JOIN food_items f ON oi.food_item_id = f.id 
        WHERE oi.order_id = ?", 
        [$orderId]
    );
    
    $order['items'] = $items;
    return $order;
}

// Get top rated food items
function getTopRatedFoodItems($limit = 4) {
    global $db;
    return $db->select("
        SELECT f.*, u.name as chef_name 
        FROM food_items f 
        JOIN users u ON f.chef_id = u.id 
        WHERE f.is_available = 1 
        ORDER BY f.rating DESC 
        LIMIT ?", [$limit]);
}
//Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
} 
?>