<?php
require_once 'includes/auth.php';

// Logout user
$result = logoutUser();

// Redirect to home page
header('Location: index.php');
exit;
?>