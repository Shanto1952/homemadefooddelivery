<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Check session timeout
if (isLoggedIn()) {
    checkSessionTimeout();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <a href="index.php">
                    <img class = "hero_logo" src="assets\images\logo.png">
                </a>
            </div>
            
            <div class="search-bar">
                <form class="search-form">
                    <input type="text" placeholder="Enter item or Home you are looking for">
                </form>
            </div>
            
            <div class="user-actions">
                <?php if (isLoggedIn()): ?>
                    <?php if (isUserType('customer')): ?>
                        <a href="customer/order-history.php" class="btn btn-outline">Order History</a>
                    <?php elseif (isUserType('chef')): ?>
                        <a href="chef/dashboard.php" class="btn btn-outline">Dashboard</a>
                    <?php endif; ?>
                    
                    <div class="user-profile">
                        <span><?php echo $_SESSION['user_name']; ?></span>
                        <a href="logout.php" class="btn btn-dark">Log Out</a>
                    </div>
                <?php else: ?>
                    <a href="customer/login.php" class="btn btn-outline">Customer Login</a>
                    <a href="chef/login.php" class="btn">Chef Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <div class="container">
        <?php displayFlashMessage(); ?>
    </div>