<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

// If already logged in as admin, redirect to dashboard
if (isLoggedIn() && isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $user = getUserByEmail($email);
        
        if ($user && verifyPassword($password, $user['password'])) {
            if ($user['is_admin'] != 1) {
                $error = 'Access denied. Admin privileges required.';
            } elseif ($user['is_active'] != 1) {
                $error = 'Your account has been deactivated.';
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Generate and update login token
                $token = generateToken();
                updateUserLoginToken($user['id'], $token);
                
                // Set cookie for remember me
                setcookie('login_token', $token, time() + TOKEN_EXPIRY, '/', '', false, true);
                
                // Log the login
                logAdminAction($user['id'], 'login', 'user', $user['id'], 'Admin logged in');
                
                header('Location: dashboard.php');
                exit();
            }
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
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b20710;
            --dark: #0a0a0a;
            --dark-2: #1a1a1a;
            --dark-3: #2d2d2d;
            --light: #ffffff;
            --light-2: #b3b3b3;
            --success: #46d369;
            --danger: #f40612;
            --shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-2) 50%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(15px, 3vw, 30px);
        }
        
        .login-container {
            background: var(--dark-2);
            border-radius: clamp(16px, 3vw, 24px);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: clamp(350px, 90vw, 450px);
            padding: clamp(30px, 5vw, 50px);
            border: 1px solid var(--dark-3);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: clamp(30px, 5vw, 40px);
        }
        
        .logo-section h1 {
            color: var(--primary);
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .logo-section p {
            color: var(--light-2);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .form-group {
            margin-bottom: clamp(20px, 4vw, 25px);
        }
        
        .form-group label {
            display: block;
            color: var(--light);
            margin-bottom: clamp(8px, 2vw, 10px);
            font-weight: 600;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: clamp(15px, 3vw, 20px);
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-2);
            font-size: clamp(1rem, 2.5vw, 1.2rem);
        }
        
        .form-group input {
            width: 100%;
            padding: clamp(14px, 3vw, 18px) clamp(40px, 8vw, 55px);
            background: var(--dark);
            border: 2px solid var(--dark-3);
            border-radius: clamp(10px, 2vw, 12px);
            color: var(--light);
            font-size: clamp(0.95rem, 2vw, 1rem);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--dark-2);
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.15);
        }
        
        .error-message {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
            padding: clamp(12px, 3vw, 15px);
            border-radius: clamp(8px, 2vw, 10px);
            margin-bottom: clamp(20px, 4vw, 25px);
            border-left: 4px solid var(--danger);
            font-size: clamp(0.9rem, 2vw, 0.95rem);
        }
        
        .btn {
            width: 100%;
            padding: clamp(14px, 3vw, 18px);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--light);
            border: none;
            border-radius: clamp(10px, 2vw, 12px);
            font-size: clamp(1rem, 2.5vw, 1.1rem);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .back-link {
            text-align: center;
            margin-top: clamp(20px, 4vw, 25px);
        }
        
        .back-link a {
            color: var(--light-2);
            text-decoration: none;
            font-size: clamp(0.9rem, 2vw, 0.95rem);
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: var(--primary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                padding: clamp(20px, 5vw, 30px);
            }
        }
        
        /* Smart watch support */
        @media (max-width: 320px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 15px;
            }
            
            .logo-section h1 {
                font-size: 1.5rem;
            }
        }
        
        /* Tablet landscape and desktop */
        @media (min-width: 768px) {
            .login-container {
                padding: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h1>
                <i class="fas fa-film"></i>
                <?php echo SITE_NAME; ?>
            </h1>
            <p>Admin Panel Login</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i>
                Login to Dashboard
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
