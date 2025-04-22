<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'homemade_food_delivery');

// Email configuration for 2FA
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'Homemade Food Delivery');

// Site configuration
define('SITE_URL', 'http://localhost/homemade-food-delivery');
define('SITE_NAME', 'Homemade Food Delivery');

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Security
define('HASH_COST', 10); // For bcrypt
?>