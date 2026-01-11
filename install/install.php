<?php
// CineCraze Installation Wizard

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        // Database configuration
        $dbHost = $_POST['db_host'] ?? 'localhost';
        $dbUser = $_POST['db_user'] ?? '';
        $dbPass = $_POST['db_pass'] ?? '';
        $dbName = $_POST['db_name'] ?? '';
        
        // Test connection
        $conn = @new mysqli($dbHost, $dbUser, $dbPass);
        
        if ($conn->connect_error) {
            $errors[] = "Database connection failed: " . $conn->connect_error;
        } else {
            // Create database if it doesn't exist
            $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn->select_db($dbName);
            
            // Create tables
            $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_username (username)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS movies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                thumbnail VARCHAR(500),
                video_url VARCHAR(500),
                trailer_url VARCHAR(500),
                type ENUM('movie', 'series', 'live') DEFAULT 'movie',
                category VARCHAR(100),
                year INT,
                rating DECIMAL(3,1) DEFAULT 0.0,
                duration INT,
                language VARCHAR(50),
                country VARCHAR(100),
                genre VARCHAR(255),
                cast_crew TEXT,
                featured BOOLEAN DEFAULT FALSE,
                status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_category (category),
                INDEX idx_title (title),
                INDEX idx_status (status),
                FULLTEXT idx_search (title, description)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS user_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                session_token VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_token (session_token),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            if ($conn->multi_query($sql)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
                
                // Update config file
                $configContent = "<?php\n";
                $configContent .= "// Database Configuration\n";
                $configContent .= "define('DB_HOST', '$dbHost');\n";
                $configContent .= "define('DB_USER', '$dbUser');\n";
                $configContent .= "define('DB_PASS', '$dbPass');\n";
                $configContent .= "define('DB_NAME', '$dbName');\n\n";
                $configContent .= "// Create connection\n";
                $configContent .= "function getDbConnection() {\n";
                $configContent .= "    static \$conn = null;\n    \n";
                $configContent .= "    if (\$conn === null) {\n";
                $configContent .= "        \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);\n        \n";
                $configContent .= "        if (\$conn->connect_error) {\n";
                $configContent .= "            die(\"Connection failed: \" . \$conn->connect_error);\n";
                $configContent .= "        }\n        \n";
                $configContent .= "        \$conn->set_charset(\"utf8mb4\");\n";
                $configContent .= "    }\n    \n";
                $configContent .= "    return \$conn;\n";
                $configContent .= "}\n\n";
                $configContent .= "// Close connection\n";
                $configContent .= "function closeDbConnection() {\n";
                $configContent .= "    \$conn = getDbConnection();\n";
                $configContent .= "    if (\$conn) {\n";
                $configContent .= "        \$conn->close();\n";
                $configContent .= "    }\n";
                $configContent .= "}\n";
                
                file_put_contents('../config/database.php', $configContent);
                
                $success[] = "Database configured successfully!";
                $step = 2;
            } else {
                $errors[] = "Error creating tables: " . $conn->error;
            }
            
            $conn->close();
        }
    } elseif ($step === 2) {
        // Admin account creation
        require_once '../config/database.php';
        
        $username = $_POST['admin_username'] ?? '';
        $email = $_POST['admin_email'] ?? '';
        $password = $_POST['admin_password'] ?? '';
        $confirmPassword = $_POST['admin_password_confirm'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = "All fields are required";
        } elseif ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        } else {
            $conn = getDbConnection();
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 'admin', 'active', NOW())");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                // Insert default categories
                $defaultCategories = [
                    ['name' => 'Action', 'slug' => 'action'],
                    ['name' => 'Comedy', 'slug' => 'comedy'],
                    ['name' => 'Drama', 'slug' => 'drama'],
                    ['name' => 'Horror', 'slug' => 'horror'],
                    ['name' => 'Sci-Fi', 'slug' => 'sci-fi'],
                    ['name' => 'Romance', 'slug' => 'romance'],
                    ['name' => 'Thriller', 'slug' => 'thriller'],
                    ['name' => 'Documentary', 'slug' => 'documentary']
                ];
                
                $catStmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                foreach ($defaultCategories as $cat) {
                    $catStmt->bind_param("ss", $cat['name'], $cat['slug']);
                    $catStmt->execute();
                }
                
                // Insert default settings
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('site_name', 'CineCraze')");
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('site_description', 'Your favorite movies and TV shows')");
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('site_installed', 'true')");
                
                // Create .htaccess file
                $htaccess = "RewriteEngine On\n";
                $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
                $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
                $htaccess .= "RewriteRule ^(.*)$ index.php [QSA,L]\n\n";
                $htaccess .= "# Disable directory browsing\n";
                $htaccess .= "Options -Indexes\n\n";
                $htaccess .= "# Protect config files\n";
                $htaccess .= "<FilesMatch \"(database|config)\\.php$\">\n";
                $htaccess .= "    Order allow,deny\n";
                $htaccess .= "    Deny from all\n";
                $htaccess .= "</FilesMatch>\n";
                
                file_put_contents('../.htaccess', $htaccess);
                
                $success[] = "Admin account created successfully!";
                $step = 3;
            } else {
                $errors[] = "Error creating admin account: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineCraze Installation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #e50914;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }
        
        .step.active {
            background: #e50914;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #e50914;
        }
        
        .btn {
            background: #e50914;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #b20710;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .success-icon {
            font-size: 64px;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .success-message {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .success-message h2 {
            color: #28a745;
            margin-bottom: 10px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 CineCraze</h1>
        <p class="subtitle">Installation Wizard</p>
        
        <div class="steps">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php foreach ($success as $msg): ?>
                    <p><?php echo htmlspecialchars($msg); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step === 1): ?>
            <h2>Database Configuration</h2>
            <p style="margin-bottom: 20px; color: #666;">Enter your database credentials</p>
            
            <form method="POST">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label>Database Username</label>
                    <input type="text" name="db_user" required>
                </div>
                
                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_pass">
                </div>
                
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" value="cinecraze_db" required>
                </div>
                
                <button type="submit" class="btn">Continue</button>
            </form>
        <?php elseif ($step === 2): ?>
            <h2>Create Admin Account</h2>
            <p style="margin-bottom: 20px; color: #666;">Set up your administrator account</p>
            
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="admin_username" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="admin_email" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="admin_password" required>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="admin_password_confirm" required>
                </div>
                
                <button type="submit" class="btn">Create Account</button>
            </form>
        <?php else: ?>
            <div class="success-icon">✓</div>
            <div class="success-message">
                <h2>Installation Complete!</h2>
                <p style="color: #666;">CineCraze has been successfully installed.</p>
            </div>
            
            <div style="text-align: center;">
                <a href="../index.php" class="btn" style="text-decoration: none; display: inline-block; margin-right: 10px;">Visit Site</a>
                <a href="../admin/index.php" class="btn" style="text-decoration: none; display: inline-block; background: #007bff;">Admin Dashboard</a>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                <strong>Important:</strong> For security reasons, please delete the <code>/install</code> directory.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
