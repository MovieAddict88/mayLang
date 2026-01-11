<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

$error = '';

if (isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        if (login($email, $password)) {
            $redirect = $_GET['redirect'] ?? '/index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            background: #1a1a1a;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            max-width: 450px;
            width: 100%;
            padding: 40px;
            border: 1px solid #333;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #e50914;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #999;
            font-size: 14px;
        }
        
        h2 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #333;
            border-radius: 5px;
            font-size: 14px;
            background: #0f0f0f;
            color: #fff;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #e50914;
        }
        
        .btn {
            width: 100%;
            background: #e50914;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #b20710;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #e50914;
            text-decoration: none;
            font-size: 14px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            color: #666;
            margin: 20px 0;
            font-size: 14px;
        }
        
        @media (max-width: 480px) {
            .auth-container {
                padding: 25px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
        }
        
        @media (max-width: 320px) {
            body {
                padding: 10px;
            }
            
            .auth-container {
                padding: 20px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <h1>🎬 CineCraze</h1>
            <p>Your favorite movies & TV shows</p>
        </div>
        
        <h2>Sign In</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Sign In</button>
        </form>
        
        <div class="divider">OR</div>
        
        <div class="links">
            <p style="color: #999; margin-bottom: 10px;">Don't have an account?</p>
            <a href="/register.php">Create Account</a>
        </div>
    </div>
</body>
</html>
