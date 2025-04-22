<?php
$pageTitle = 'Customer Dashboard';
$includeCart = true;

require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isUserType('customer')) {
    header('Location: login.php');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserData($userId);

// Get nearby food items
$foodItems = getNearbyFoodItems(6);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Homemade Food Delivery</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
        }
        
        header {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo img {
            height: 50px;
        }
        
        .search-bar {
            flex-grow: 1;
            margin: 0 20px;
        }
        
        .search-bar input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 14px;
            outline: none;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .user-name {
            font-weight: 500;
            margin-right: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin-right: 10px;
            background-color: #e67e22;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #d35400;
        }
        
        .btn-dark {
            background-color: #333;
        }
        
        .btn-dark:hover {
            background-color: #000;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .section-title {
            margin-bottom: 30px;
        }
        
        .section-title h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .food-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .food-card:hover {
            transform: translateY(-5px);
        }
        
        .food-card-image {
            height: 180px;
            overflow: hidden;
        }
        
        .food-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .food-card-content {
            padding: 15px;
        }
        
        .food-card-title {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .food-card-chef {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .food-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .food-card-price {
            font-weight: bold;
            color: #e67e22;
            font-size: 16px;
        }
        
        .food-card-time {
            color: #666;
            font-size: 14px;
        }
        
        .add-to-cart {
            width: 100%;
        }
        
        footer {
            background-color: #2c3e50;
            color: #fff;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-logo img {
            height: 40px;
            margin-bottom: 15px;
        }
        
        .footer-text {
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .footer-social a {
            display: inline-block;
            margin-right: 15px;
            color: #fff;
            font-size: 20px;
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
            }
            
            .logo {
                margin-bottom: 15px;
            }
            
            .search-bar {
                margin: 15px 0;
                width: 100%;
            }
            
            .food-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .footer-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="../index.php">
                    <img src="../assets/images/logo.png" alt="Homemade Food Delivery">
                </a>
            </div>
            
            <div class="search-bar">
                <input type="text" placeholder="Enter item or Home you are looking for">
            </div>
            
            <div class="user-info">
                <img src="/placeholder.svg?height=40&width=40" alt="User">
                <span class="user-name"><?php echo $user['name']; ?></span>
                <a href="order-history.php" class="btn">Order History</a>
                <a href="../logout.php" class="btn btn-dark">Log Out</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="section-title">
            <h2>Nearby Home Foods</h2>
            <p>Discover delicious homemade meals near you</p>
        </div>
        
        <div class="food-grid">
            <?php foreach ($foodItems as $item): ?>
            <div class="food-card">
                <div class="food-card-image">
                    <img src="<?php echo isset($item['image']) ? $item['image'] : '/placeholder.svg?height=180&width=280'; ?>" alt="<?php echo $item['name']; ?>">
                </div>
                <div class="food-card-content">
                    <h3 class="food-card-title"><?php echo $item['name']; ?></h3>
                    <p class="food-card-chef">By <?php echo $item['chef_name']; ?></p>
                    <div class="food-card-meta">
                        <div class="food-card-price"><?php echo formatPrice($item['price']); ?></div>
                        <div class="food-card-time"><?php echo $item['preparation_time']; ?> Mins</div>
                    </div>
                    <button class="btn add-to-cart" 
                        data-id="<?php echo $item['id']; ?>" 
                        data-name="<?php echo $item['name']; ?>" 
                        data-price="<?php echo $item['price']; ?>" 
                        data-image="<?php echo isset($item['image']) ? $item['image'] : '/placeholder.svg?height=180&width=280'; ?>">
                        Add To Cart
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-info">
                <div class="footer-logo">
                    <img src="../assets/images/logo.png" alt="Homemade Food Delivery">
                </div>
                <p class="footer-text">Your Favorite Food, Delivered! 🍲🍽️</p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-contact">
                <p>Contact: 01515215020</p>
            </div>
        </div>
    </footer>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/cart.js"></script>
</body>
</html>