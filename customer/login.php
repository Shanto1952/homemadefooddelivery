<?php
$pageTitle = 'Customer Login';
$includeAuth = true;

require_once '../includes/auth.php';

// Enable error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Check for success message from registration
$successMessage = isset($_GET['message']) ? $_GET['message'] : '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $verificationCode = isset($_POST['verification_code']) ? sanitizeInput($_POST['verification_code']) : '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // Process verification code if provided
    if (!empty($verificationCode)) {
        if (isset($_SESSION['user_id_for_verification'])) {
            $verifyResult = verifyUser($_SESSION['user_id_for_verification'], $verificationCode);
            
            if ($verifyResult['success']) {
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = $verifyResult['message'];
            }
        } elseif (isset($_SESSION['user_id_for_2fa'])) {
            $verify2FAResult = verify2FA($_SESSION['user_id_for_2fa'], $verificationCode);
            
            if ($verify2FAResult['success']) {
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = $verify2FAResult['message'];
            }
        }
        
        // If we get here with a verification code but no success, continue to login attempt
    }

    // Only attempt login if no verification was processed or verification failed
    if (empty($errors) && (empty($verificationCode) || (!isset($_SESSION['user_id_for_verification']) && !isset($_SESSION['user_id_for_2fa'])))) {
        // Login user
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            if (isset($result['needs_2fa']) && $result['needs_2fa']) {
                // Store user ID for 2FA
                $_SESSION['user_id_for_2fa'] = $result['user_id'];
                
                // For development, show the verification code
                $twoFactorCode = $result['verification_code'];
                $needs2FA = true;
                $twoFactorMessage = "Please enter the verification code sent to your email. For development: $twoFactorCode";
            } else {
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            }
        } elseif (isset($result['needs_verification']) && $result['needs_verification']) {
            // Store user ID for verification
            $_SESSION['user_id_for_verification'] = $result['user_id'];
            
            // For development, show the verification code
            $verificationCode = $result['verification_code'];
            $needsVerification = true;
            $verificationMessage = "Account not verified. Please enter the verification code sent to your email.";
        } else {
            $errors[] = $result['message'];
        }
    }
}

    // If verification code is provided
    if (!empty($verificationCode)) {
        // Email verification
        if (isset($_SESSION['user_id_for_verification'])) {
            $verifyResult = verifyUser($_SESSION['user_id_for_verification'], $verificationCode);

            if ($verifyResult['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = $verifyResult['message'];
            }
        }

        // 2FA verification
        if (isset($_SESSION['user_id_for_2fa'])) {
            $verify2FAResult = verify2FA($_SESSION['user_id_for_2fa'], $verificationCode);

            if ($verify2FAResult['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = $verify2FAResult['message'];
            }
        }
    }
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-section {
            flex: 1;
            padding: 40px;
            background: #fff;
        }
        
        .image-section {
            flex: 1;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-section img {
            max-width: 100%;
            height: auto;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-header .logo {
            margin-bottom: 20px;
        }
        
        .form-header .logo img {
            height: 60px;
        }
        
        .form-header h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #e67e22;
            outline: none;
        }
        .loginImage{
            height: 400px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #222;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #000;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .form-footer a {
            color: #e67e22;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .verification-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .text-right {
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .image-section {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <div class="form-header">
                <h2>Welcome Back!</h2>
                <div class="logo">
                    <a href="../index.php">
                        <img src="../assets/images/logo.png" alt="Homemade Food Delivery">
                    </a>
                </div>
                <h3>CUSTOMER LOG IN</h3>
            </div>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (isset($needsVerification) && $needsVerification): ?>
                <div class="alert alert-info">
                    <?php echo $verificationMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($needs2FA) && $needs2FA): ?>
                <div class="alert alert-info">
                    <?php echo $twoFactorMessage; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email/Phone</label>
                    <input type="text" id="email" name="email" class="form-control" placeholder="Enter your email/phone" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    <div class="text-right">
                        <a href="forgot-password.php">Forgot Password</a>
                    </div>
                </div>
                
                <?php if (isset($needsVerification) || isset($needs2FA)): ?>
                <div class="form-group">
                    <label for="verification_code">Verification Code</label>
                    <input type="text" id="verification_code" name="verification_code" class="form-control" placeholder="Enter verification code" maxlength="6">
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn">Sign In</button>
                
                <div class="form-footer">
                    <p><a href="register.php">Create Account</a></p>
                </div>
            </form>
        </div>
        
        <div class="image-section">
        <img src="../assets/images/loginImage.jfif" alt="Chef Hat" class = "loginImage">
        </div>
    </div>
</body>
</html>