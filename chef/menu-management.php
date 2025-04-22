<?php
$pdo = new PDO('mysql:host=localhost;dbname=homemade', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if user is logged in and is a chef
if (!isLoggedIn() || !isUserType('chef')) {
    header('Location: login.php');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserData($userId);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ADD ITEM
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;


    
    $sql = "INSERT INTO food_items (name, description, price, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$name, $description, $price, $quantity,$name, $description, $price, $quantity])) {
        echo "Item added successfully!";
    } else {
        echo "Failed to add item.";
    }
} else {
    // GET ITEMS
    $stmt = $pdo->query("SELECT * FROM food_items ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if(isset($_POST['submit'])){
	header(header: 'location:"C:\Users\Lenovo\Downloads\homemade-food-delivery\assets\css\add_item.html"');
}
?>
