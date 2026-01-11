<?php
/**
 * CineCraze - Public Streaming Interface
 * 
 * This file serves as the main entry point for the public-facing streaming application.
 * It requires user authentication and integrates with the PHP/MySQL backend.
 * 
 * TODO: This is a starter template. The full conversion from index.html (8499 lines)
 * needs to be completed by integrating:
 * - Authentication modal (mandatory, cannot be closed)
 * - Content fetching from /api/content.php
 * - All features from the original index.html
 * - IndexedDB caching
 * - Responsive player with Plyr/Shaka
 */

require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

// Check authentication
$isAuthenticated = false;
$userData = null;

// Check for token in cookie
if (isset($_COOKIE['login_token']) && !empty($_COOKIE['login_token'])) {
    $user = getUserByToken($_COOKIE['login_token']);
    if ($user && $user['is_active'] == 1) {
        $isAuthenticated = true;
        $userData = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
    }
}

// If no cookie, check session
if (!$isAuthenticated && isLoggedIn()) {
    $userData = getUserById($_SESSION['user_id']);
    if ($userData && $userData['is_active'] == 1) {
        $isAuthenticated = true;
    } else {
        // Session invalid, clear it
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Stream Movies, TV Shows & Live TV</title>
    
    <!-- External Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css@4.1.7/css/flag-icons.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
    
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b20710;
            --dark: #141414;
            --dark-2: #1a1a1a;
            --dark-3: #222222;
            --light: #f5f5f5;
            --gray: #8c8c8c;
            --youtube-dark: #0f0f0f;
            --youtube-gray: #272727;
            --youtube-red: #ff0000;
            --transition: all 0.3s ease;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', 'Segoe UI', sans-serif;
            background-color: var(--youtube-dark);
            color: var(--light);
            overflow-x: hidden;
        }
        
        /* Loading screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            flex-direction: column;
            gap: 20px;
        }
        
        .loading-screen.hidden {
            display: none;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--dark-2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Authentication Modal */
        .auth-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            display: <?php echo $isAuthenticated ? 'none' : 'flex'; ?>;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(10px);
        }
        
        .auth-modal-content {
            background: var(--dark-2);
            border-radius: clamp(16px, 3vw, 24px);
            padding: clamp(30px, 5vw, 50px);
            width: clamp(300px, 90vw, 450px);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: clamp(25px, 4vw, 35px);
        }
        
        .auth-header h1 {
            color: var(--primary);
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .auth-header p {
            color: var(--gray);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .auth-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: clamp(20px, 4vw, 30px);
            border-bottom: 2px solid var(--dark-3);
        }
        
        .auth-tab {
            flex: 1;
            padding: clamp(12px, 2.5vw, 15px);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: clamp(0.95rem, 2vw, 1.1rem);
            font-weight: 600;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }
        
        .auth-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: clamp(18px, 3vw, 22px);
        }
        
        .form-group label {
            display: block;
            margin-bottom: clamp(8px, 2vw, 10px);
            color: var(--light);
            font-weight: 500;
            font-size: clamp(0.9rem, 2vw, 0.95rem);
        }
        
        .form-group input {
            width: 100%;
            padding: clamp(12px, 2.5vw, 15px);
            background: var(--dark);
            border: 2px solid var(--dark-3);
            border-radius: clamp(8px, 1.5vw, 10px);
            color: var(--light);
            font-size: clamp(0.95rem, 2vw, 1rem);
            transition: var(--transition);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.15);
        }
        
        .btn {
            width: 100%;
            padding: clamp(14px, 3vw, 16px);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--light);
            border: none;
            border-radius: clamp(8px, 1.5vw, 10px);
            font-size: clamp(1rem, 2.2vw, 1.1rem);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .error-message {
            background: rgba(244, 6, 18, 0.1);
            color: var(--primary);
            padding: clamp(12px, 2.5vw, 15px);
            border-radius: clamp(8px, 1.5vw, 10px);
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
            font-size: clamp(0.85rem, 1.8vw, 0.9rem);
            display: none;
        }
        
        .success-message {
            background: rgba(70, 211, 105, 0.1);
            color: #46d369;
            padding: clamp(12px, 2.5vw, 15px);
            border-radius: clamp(8px, 1.5vw, 10px);
            margin-bottom: 20px;
            border-left: 4px solid #46d369;
            font-size: clamp(0.85rem, 1.8vw, 0.9rem);
            display: none;
        }
        
        /* Main Content Placeholder */
        .main-content {
            padding: 80px 5% 50px;
            display: <?php echo $isAuthenticated ? 'block' : 'none'; ?>;
        }
        
        .welcome-section {
            text-align: center;
            padding: clamp(40px, 8vw, 80px) 0;
        }
        
        .welcome-section h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--youtube-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-section p {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        @media (max-width: 768px) {
            .auth-modal-content {
                width: 95vw;
                padding: 25px;
            }
        }
        
        @media (max-width: 320px) {
            .auth-modal-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="spinner"></div>
        <p>Loading <?php echo SITE_NAME; ?>...</p>
    </div>
    
    <!-- Authentication Modal -->
    <div class="auth-modal" id="authModal">
        <div class="auth-modal-content">
            <div class="auth-header">
                <h1>
                    <i class="fas fa-film"></i>
                    <?php echo SITE_NAME; ?>
                </h1>
                <p>Please login or register to continue</p>
            </div>
            
            <div class="auth-tabs">
                <button class="auth-tab active" onclick="switchTab('login')">Login</button>
                <button class="auth-tab" onclick="switchTab('register')">Register</button>
            </div>
            
            <div class="error-message" id="authError"></div>
            <div class="success-message" id="authSuccess"></div>
            
            <!-- Login Form -->
            <form class="auth-form active" id="loginForm" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                <button type="submit" class="btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
            
            <!-- Register Form -->
            <form class="auth-form" id="registerForm" onsubmit="handleRegister(event)">
                <div class="form-group">
                    <label for="reg_username">Username</label>
                    <input type="text" id="reg_username" name="username" required minlength="3">
                </div>
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg_fullname">Full Name</label>
                    <input type="text" id="reg_fullname" name="full_name">
                </div>
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="reg_password_confirm">Confirm Password</label>
                    <input type="password" id="reg_password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn" id="registerBtn">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>
        </div>
    </div>
    
    <!-- Main Content (shown after authentication) -->
    <div class="main-content" id="mainContent">
        <div class="welcome-section">
            <h1>Welcome to <?php echo SITE_NAME; ?>!</h1>
            <?php if ($isAuthenticated): ?>
            <p>Hello, <?php echo htmlspecialchars($userData['username']); ?>! 👋</p>
            <p>You are successfully logged in.</p>
            <?php endif; ?>
            <p style="margin-top: 40px; color: var(--gray); font-size: 0.9rem;">
                <strong>Note:</strong> The full conversion from index.html is in progress.<br>
                This page will be enhanced with all streaming features soon.
            </p>
            <div style="margin-top: 30px;">
                <a href="admin/" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                    <i class="fas fa-cog"></i> Admin Panel
                </a>
                <span style="margin: 0 15px; color: var(--gray);">|</span>
                <a href="#" onclick="handleLogout()" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- TODO: Add all content from original index.html here -->
        <!-- - Header with search -->
        <!-- - Hero carousel -->
        <!-- - Content grid/list -->
        <!-- - Video player -->
        <!-- - Filters -->
        <!-- - etc. -->
    </div>
    
    <script>
        // Configuration
        const API_BASE = '/api';
        const isAuthenticated = <?php echo $isAuthenticated ? 'true' : 'false'; ?>;
        
        // Show/hide loading screen
        function hideLoading() {
            document.getElementById('loadingScreen').classList.add('hidden');
        }
        
        // Switch between login and register tabs
        function switchTab(tab) {
            const tabs = document.querySelectorAll('.auth-tab');
            const forms = document.querySelectorAll('.auth-form');
            
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(f => f.classList.remove('active'));
            
            if (tab === 'login') {
                tabs[0].classList.add('active');
                forms[0].classList.add('active');
            } else {
                tabs[1].classList.add('active');
                forms[1].classList.add('active');
            }
            
            hideError();
            hideSuccess();
        }
        
        // Show/hide error messages
        function showError(message) {
            const errorEl = document.getElementById('authError');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
        
        function hideError() {
            document.getElementById('authError').style.display = 'none';
        }
        
        function showSuccess(message) {
            const successEl = document.getElementById('authSuccess');
            successEl.textContent = message;
            successEl.style.display = 'block';
        }
        
        function hideSuccess() {
            document.getElementById('authSuccess').style.display = 'none';
        }
        
        // Handle login
        async function handleLogin(event) {
            event.preventDefault();
            hideError();
            
            const btn = document.getElementById('loginBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            
            const formData = new FormData(event.target);
            const data = {
                email: formData.get('email'),
                password: formData.get('password')
            };
            
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Store token
                    localStorage.setItem('auth_token', result.data.token);
                    document.cookie = `login_token=${result.data.token}; max-age=${30*24*60*60}; path=/; SameSite=Strict`;
                    
                    showSuccess('Login successful! Redirecting...');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError(result.message || 'Login failed. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                showError('Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
        
        // Handle registration
        async function handleRegister(event) {
            event.preventDefault();
            hideError();
            
            const btn = document.getElementById('registerBtn');
            const originalText = btn.innerHTML;
            
            const formData = new FormData(event.target);
            const password = formData.get('password');
            const passwordConfirm = formData.get('password_confirm');
            
            if (password !== passwordConfirm) {
                showError('Passwords do not match');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
            
            const data = {
                username: formData.get('username'),
                email: formData.get('email'),
                password: password,
                full_name: formData.get('full_name')
            };
            
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=register`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Store token
                    localStorage.setItem('auth_token', result.data.token);
                    document.cookie = `login_token=${result.data.token}; max-age=${30*24*60*60}; path=/; SameSite=Strict`;
                    
                    showSuccess('Registration successful! Redirecting...');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showError(result.message || 'Registration failed. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                showError('Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
        
        // Handle logout
        async function handleLogout() {
            if (!confirm('Are you sure you want to logout?')) {
                return;
            }
            
            try {
                await fetch(`${API_BASE}/auth.php?action=logout`, {
                    method: 'POST'
                });
                
                localStorage.removeItem('auth_token');
                document.cookie = 'login_token=; max-age=0; path=/';
                
                window.location.reload();
            } catch (error) {
                alert('Logout failed. Please try again.');
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            hideLoading();
            
            // If authenticated, you can start loading content here
            if (isAuthenticated) {
                // TODO: Load content from API
                // loadContent();
            }
        });
        
        // Example function to load content (to be implemented)
        async function loadContent() {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await fetch(`${API_BASE}/content.php?action=list&limit=20&page=1`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // TODO: Render content
                    console.log('Content loaded:', result.data);
                }
            } catch (error) {
                console.error('Failed to load content:', error);
            }
        }
    </script>
</body>
</html>
