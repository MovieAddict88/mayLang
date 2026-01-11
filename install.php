<?php
/**
 * CineCraze Web Installer
 * 
 * This script helps you set up CineCraze on any hosting (free or paid)
 * After installation, DELETE this file for security
 */

// Check if already installed
if (file_exists('includes/config.php')) {
    $config_content = file_get_contents('includes/config.php');
    if (strpos($config_content, "define('INSTALLED', true)") !== false) {
        die('
            <html>
            <head>
                <title>Already Installed</title>
                <style>
                    body { font-family: Arial, sans-serif; background: #0a0a0a; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                    .container { text-align: center; background: #1a1a1a; padding: 40px; border-radius: 16px; max-width: 500px; }
                    h1 { color: #e50914; }
                    a { color: #e50914; text-decoration: none; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>✓ Already Installed</h1>
                    <p>CineCraze is already installed on this server.</p>
                    <p><a href="index.php">Go to Home</a> | <a href="admin/">Go to Admin Panel</a></p>
                    <p style="margin-top: 30px; font-size: 0.9em; color: #666;">
                        <strong>Security Notice:</strong> Please delete install.php from your server.
                    </p>
                </div>
            </body>
            </html>
        ');
    }
}

$step = isset($_REQUEST['step']) ? (int)$_REQUEST['step'] : 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Step 1: Check requirements
        $step = 2;
    } elseif ($step === 2) {
        // Step 2: Database configuration
        $host = $_POST['db_host'] ?? 'localhost';
        $name = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';
        $site_url = $_POST['site_url'] ?? '';
        $site_name = $_POST['site_name'] ?? 'CineCraze';
        
        // Test database connection
        try {
            $dsn = "mysql:host={$host};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if database exists, create if not
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");
            
            // Store credentials in session for next step
            session_start();
            $_SESSION['install_db_host'] = $host;
            $_SESSION['install_db_name'] = $name;
            $_SESSION['install_db_user'] = $user;
            $_SESSION['install_db_pass'] = $pass;
            $_SESSION['install_site_url'] = $site_url;
            $_SESSION['install_site_name'] = $site_name;
            
            $step = 3;
        } catch (PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
        }
    } elseif ($step === 3) {
        // Step 3: Import database schema
        session_start();
        
        $host = $_SESSION['install_db_host'];
        $name = $_SESSION['install_db_name'];
        $user = $_SESSION['install_db_user'];
        $pass = $_SESSION['install_db_pass'];
        
        try {
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Read and execute SQL file
            $sql = file_get_contents('database.sql');
            
            // Remove CREATE DATABASE commands as we already created it
            $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
            $sql = preg_replace('/USE .*;/i', '', $sql);
            
            // Split into individual queries
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($queries as $query) {
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
            
            $success = "Database tables created successfully!";
            $step = 4;
        } catch (PDOException $e) {
            $error = "Database import failed: " . $e->getMessage();
        }
    } elseif ($step === 4) {
        // Step 4: Create admin account
        session_start();
        
        $admin_email = $_POST['admin_email'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';
        $admin_username = $_POST['admin_username'] ?? 'admin';
        
        if (empty($admin_email) || empty($admin_password)) {
            $error = "Email and password are required";
        } elseif (strlen($admin_password) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            try {
                $host = $_SESSION['install_db_host'];
                $name = $_SESSION['install_db_name'];
                $user = $_SESSION['install_db_user'];
                $pass = $_SESSION['install_db_pass'];
                
                $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Delete default admin if exists
                $pdo->exec("DELETE FROM users WHERE email = 'admin@cinecraze.com'");
                
                // Create new admin
                $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, is_admin, is_active) VALUES (?, ?, ?, 'Administrator', 1, 1)");
                $stmt->execute([$admin_username, $admin_email, $hashed_password]);
                
                $step = 5;
            } catch (PDOException $e) {
                $error = "Failed to create admin account: " . $e->getMessage();
            }
        }
    } elseif ($step === 5) {
        // Step 5: Write config file
        session_start();
        
        $host = $_SESSION['install_db_host'];
        $name = $_SESSION['install_db_name'];
        $user = $_SESSION['install_db_user'];
        $pass = $_SESSION['install_db_pass'];
        $site_url = $_SESSION['install_site_url'];
        $site_name = $_SESSION['install_site_name'];
        
        $config_content = "<?php
/**
 * CineCraze Configuration File
 * 
 * Auto-generated by installer
 * IMPORTANT: Keep this file secure
 */

// Database Configuration
define('DB_HOST', '{$host}');
define('DB_NAME', '{$name}');
define('DB_USER', '{$user}');
define('DB_PASS', '{$pass}');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', '{$site_name}');
define('SITE_URL', '{$site_url}');
define('ADMIN_URL', SITE_URL . '/admin');

// Security Configuration
define('SESSION_LIFETIME', 86400 * 30);
define('TOKEN_EXPIRY', 86400 * 30);
define('PASSWORD_SALT', '" . bin2hex(random_bytes(32)) . "');

// Path Configuration
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

// Session Configuration
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Installation check
define('INSTALLED', true);
";
        
        if (file_put_contents('includes/config.php', $config_content)) {
            // Create upload directory
            @mkdir('uploads', 0755, true);
            @mkdir('uploads/posters', 0755, true);
            @mkdir('uploads/backdrops', 0755, true);
            
            // Clear installation session
            session_destroy();
            
            $step = 6;
        } else {
            $error = "Failed to write config.php. Please check file permissions.";
        }
    }
}

// Check PHP requirements
function checkRequirements() {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'JSON Extension' => extension_loaded('json'),
        'MBString Extension' => extension_loaded('mbstring'),
        'File Uploads Enabled' => ini_get('file_uploads'),
        'Session Support' => function_exists('session_start'),
    ];
    
    return $requirements;
}

$requirements = checkRequirements();
$all_requirements_met = !in_array(false, $requirements, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineCraze Installer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b20710;
            --dark: #0a0a0a;
            --dark-2: #1a1a1a;
            --dark-3: #2d2d2d;
            --light: #ffffff;
            --gray: #8c8c8c;
            --success: #46d369;
            --danger: #f40612;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-2) 50%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .installer-container {
            background: var(--dark-2);
            border-radius: 16px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        
        .installer-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 30px;
            text-align: center;
            color: var(--light);
        }
        
        .installer-header h1 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .installer-body {
            padding: clamp(25px, 5vw, 40px);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--dark-3);
            z-index: 0;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--dark-3);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
            font-size: 0.9rem;
        }
        
        .step.active {
            background: var(--primary);
            color: var(--light);
        }
        
        .step.completed {
            background: var(--success);
            color: var(--light);
        }
        
        h2 {
            color: var(--light);
            margin-bottom: 20px;
            font-size: clamp(1.2rem, 3vw, 1.5rem);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: var(--light);
            margin-bottom: 8px;
            font-weight: 500;
            font-size: clamp(0.9rem, 2vw, 0.95rem);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: var(--dark);
            border: 2px solid var(--dark-3);
            border-radius: 8px;
            color: var(--light);
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group small {
            display: block;
            color: var(--gray);
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .requirements-list {
            list-style: none;
            margin: 20px 0;
        }
        
        .requirements-list li {
            padding: 12px;
            margin-bottom: 10px;
            background: var(--dark-3);
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--light);
        }
        
        .req-status {
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .req-status.pass {
            color: var(--success);
        }
        
        .req-status.fail {
            color: var(--danger);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .alert-error {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .alert-success {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .alert-info {
            background: rgba(0, 212, 255, 0.1);
            color: #00d4ff;
            border-left: 4px solid #00d4ff;
        }
        
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--light);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: var(--dark-3);
            color: var(--light);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .success-icon {
            text-align: center;
            margin: 30px 0;
        }
        
        .success-icon i {
            font-size: 80px;
            color: var(--success);
        }
        
        .final-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .final-links a {
            flex: 1;
            min-width: 150px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
            }
            
            .final-links {
                flex-direction: column;
            }
            
            .final-links a {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>
                <i class="fas fa-film"></i>
                CineCraze Installer
            </h1>
            <p>Easy setup for any hosting (free or paid)</p>
        </div>
        
        <div class="installer-body">
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'completed' : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3</div>
                <div class="step <?php echo $step >= 4 ? ($step > 4 ? 'completed' : 'active') : ''; ?>">4</div>
                <div class="step <?php echo $step >= 5 ? ($step > 5 ? 'completed' : 'active') : ''; ?>">5</div>
                <div class="step <?php echo $step == 6 ? 'active' : ''; ?>">6</div>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($step === 1): ?>
                <h2>Step 1: System Requirements</h2>
                <ul class="requirements-list">
                    <?php foreach ($requirements as $name => $met): ?>
                    <li>
                        <span><?php echo $name; ?></span>
                        <span class="req-status <?php echo $met ? 'pass' : 'fail'; ?>">
                            <?php echo $met ? '✓' : '✗'; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if (!$all_requirements_met): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> Some requirements are not met. Please contact your hosting provider or install the required PHP extensions.
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" <?php echo !$all_requirements_met ? 'disabled' : ''; ?>>
                            Continue <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 2): ?>
                <h2>Step 2: Database Configuration</h2>
                <div class="alert alert-info">
                    <strong>Tip:</strong> Get these credentials from your hosting control panel (cPanel, Plesk, etc.)
                </div>
                
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                        <small>Usually "localhost" on most hosting</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" required>
                        <small>The name of your MySQL database</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass">
                        <small>Leave empty if no password (not recommended)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_url">Site URL</label>
                        <input type="url" id="site_url" name="site_url" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>" required>
                        <small>Your website URL (no trailing slash)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" value="CineCraze" required>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            Test Connection & Continue <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 3): ?>
                <h2>Step 3: Import Database</h2>
                <div class="alert alert-info">
                    <strong>Info:</strong> This will create all necessary database tables.
                </div>
                
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    <p style="color: var(--light); margin-bottom: 20px;">
                        Click the button below to import the database schema.
                    </p>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            Import Database <i class="fas fa-database"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 4): ?>
                <h2>Step 4: Create Admin Account</h2>
                <div class="alert alert-info">
                    <strong>Important:</strong> This will be your admin login. Keep it secure!
                </div>
                
                <form method="POST">
                    <input type="hidden" name="step" value="4">
                    <div class="form-group">
                        <label for="admin_username">Admin Username</label>
                        <input type="text" id="admin_username" name="admin_username" value="admin" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Admin Email</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" required minlength="6">
                        <small>Minimum 6 characters</small>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            Create Account <i class="fas fa-user-plus"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 5): ?>
                <h2>Step 5: Finalize Installation</h2>
                <div class="alert alert-info">
                    <strong>Almost done!</strong> Click below to write the configuration file.
                </div>
                
                <form method="POST">
                    <input type="hidden" name="step" value="5">
                    <p style="color: var(--light); margin-bottom: 20px;">
                        This will create the config.php file with your settings.
                    </p>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            Finalize Setup <i class="fas fa-check"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 6): ?>
                <h2>Installation Complete!</h2>
                
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <div class="alert alert-success">
                    <strong>Congratulations!</strong> CineCraze has been successfully installed.
                </div>
                
                <div class="alert alert-error">
                    <strong>Security Warning:</strong> Please delete <code>install.php</code> from your server immediately for security reasons.
                </div>
                
                <div class="alert alert-info">
                    <strong>Next Steps:</strong>
                    <ol style="margin: 10px 0 0 20px;">
                        <li>Delete install.php file</li>
                        <li>Login to admin panel</li>
                        <li>Add your TMDB API key in Settings</li>
                        <li>Start adding content</li>
                    </ol>
                </div>
                
                <div class="final-links">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go to Home
                    </a>
                    <a href="admin/" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Admin Panel
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
