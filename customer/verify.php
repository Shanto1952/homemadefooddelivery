<?php
$pageTitle = 'Verify Account';
$includeAuth = true;

require_once '../includes/auth.php';

// Check if user ID is set
if (!isset($_SESSION['user_id_for_verification'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id_for_verification'];
$email = $_SESSION['registration_email'] ?? '';

// Process verification form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitizeInput($_POST['verification_code']);
    
    // Validate code
    if (empty($code)) {
        $errors[] = 'Verification code is required';
    } elseif (strlen($code) !== 6) {
        $errors[] = 'Verification code must be 6 digits';
    }
    
    if (empty($errors)) {
        // Verify user
        $result = verifyUser($userId, $code);
        
        if ($result['success']) {
            // Clear session variables
            unset($_SESSION['user_id_for_verification']);
            unset($_SESSION['registration_email']);
            
            // Set success message
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'success';
            
            // Redirect to login page
            header('Location: login.php');
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="verification-container">
        <div class="logo">
            <a href="../index.php">
                <img src="../assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
            </a>
        </div>
        
        <h2>Verify Your Account</h2>
        <p>We've sent a 6-digit verification code to <strong><?php echo $email; ?></strong>. Please enter the code below to verify your account.</p>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form id="verification-form" method="POST" action="">
            <div class="verification-code">
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>
            
            <input type="hidden" id="verification-code" name="verification_code">
            
            <button type="submit" class="btn btn-block">Verify Account</button>
        </form>
        
        <div class="form-footer">
            <p>Didn't receive the code? <a href="#" id="resend-code" data-user-id="<?php echo $userId; ?>">Resend Code</a></p>
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="../assets/js/auth.js"></script>
</body>
</html>