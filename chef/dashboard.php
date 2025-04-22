<?php
$pageTitle = 'Chef Dashboard';

require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a chef
if (!isLoggedIn() || !isUserType('chef')) {
    header('Location: login.php');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserData($userId);

// Get chef's food items
$foodItems = getFoodItemsByChef($userId);
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
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title h1 {
            font-size: 24px;
            color: #333;
        }
        
        .dashboard-actions .btn {
            margin-left: 10px;
        }
        
        .section-title {
            margin-bottom: 20px;
        }
        
        .section-title h2 {
            font-size: 20px;
            color: #333;
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
        
        .food-card-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .food-card-actions .btn {
            flex: 1;
            margin: 0 5px;
            text-align: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #333;
            margin: 20px 0 10px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
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
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-actions {
                margin-top: 15px;
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
            
            <div class="user-info">
                <img src="/placeholder.svg?height=40&width=40" alt="User">
                <span class="user-name"><?php echo $user['name']; ?></span>
                <a href="../logout.php" class="btn btn-dark">Log Out</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Chef Dashboard</h1>
                <p>Manage your food items and orders</p>
            </div>
            
            <div class="dashboard-actions">
                <a href="add-food.php" class="btn">Add New Food Item</a>
                <a href="orders.php" class="btn">View Orders</a>
            </div>
        </div>
        
        <div class="section-title">
            <h2>Your Food Items</h2>
        </div>
        
        <?php if (empty($foodItems)): ?>
            <div class="empty-state">
                <h3>No food items yet</h3>
                <p>Start adding your delicious food items to showcase to customers</p>
                <a href="add-food.php" class="btn">Add Food Item</a>
            </div>
        <?php else: ?>
            <div class="food-grid">
                <?php foreach ($foodItems as $item): ?>
                <div class="food-card">
                    <div class="food-card-image">
                        <img src="<?php echo isset($item['image']) ? $item['image'] : '/placeholder.svg?height=180&width=280'; ?>" alt="<?php echo $item['name']; ?>">
                    </div>
                    <div class="food-card-content">
                        <h3 class="food-card-title"><?php echo $item['name']; ?></h3>
                        <div class="food-card-meta">
                            <div class="food-card-price"><?php echo formatPrice($item['price']); ?></div>
                            <div class="food-card-time"><?php echo $item['preparation_time']; ?> Mins</div>
                        </div>
                        <div class="food-card-actions">
                            <a href="edit-food.php?id=<?php echo $item['id']; ?>" class="btn">Edit</a>
                            <a href="delete-food.php?id=<?php echo $item['id']; ?>" class="btn btn-dark" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
                <p>Contact: 01768844091</p>
            </div>
        </div>
    </footer>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>