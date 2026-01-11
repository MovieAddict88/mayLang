<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'register':
        if ($method !== 'POST') {
            errorResponse('Invalid request method', 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $username = sanitizeInput($data['username'] ?? '');
        $email = sanitizeEmail($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $fullName = sanitizeInput($data['full_name'] ?? '');
        
        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            errorResponse('All fields are required');
        }
        
        if (!validateEmail($email)) {
            errorResponse('Invalid email address');
        }
        
        if (strlen($password) < 6) {
            errorResponse('Password must be at least 6 characters');
        }
        
        if (strlen($username) < 3) {
            errorResponse('Username must be at least 3 characters');
        }
        
        // Check if user already exists
        if (getUserByEmail($email)) {
            errorResponse('Email already registered');
        }
        
        if (getUserByUsername($username)) {
            errorResponse('Username already taken');
        }
        
        // Create user
        $hashedPassword = hashPassword($password);
        $token = generateToken();
        $tokenExpires = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);
        
        $userId = $db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'full_name' => $fullName,
            'login_token' => $token,
            'token_expires' => $tokenExpires,
            'is_active' => 1,
            'is_admin' => 0
        ]);
        
        if ($userId) {
            successResponse('Registration successful', [
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'token' => $token
            ]);
        } else {
            errorResponse('Registration failed', 500);
        }
        break;
        
    case 'login':
        if ($method !== 'POST') {
            errorResponse('Invalid request method', 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $email = sanitizeEmail($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            errorResponse('Email and password are required');
        }
        
        $user = getUserByEmail($email);
        
        if (!$user || !verifyPassword($password, $user['password'])) {
            errorResponse('Invalid email or password', 401);
        }
        
        if ($user['is_active'] != 1) {
            errorResponse('Your account has been deactivated', 403);
        }
        
        // Generate new token
        $token = generateToken();
        updateUserLoginToken($user['id'], $token);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        successResponse('Login successful', [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'token' => $token,
            'is_admin' => $user['is_admin']
        ]);
        break;
        
    case 'verify':
        $token = $_GET['token'] ?? $_POST['token'] ?? $_COOKIE['login_token'] ?? '';
        
        if (empty($token)) {
            errorResponse('Token required', 401);
        }
        
        $user = getUserByToken($token);
        
        if (!$user) {
            errorResponse('Invalid or expired token', 401);
        }
        
        if ($user['is_active'] != 1) {
            errorResponse('Account deactivated', 403);
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        successResponse('Token valid', [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'is_admin' => $user['is_admin']
        ]);
        break;
        
    case 'logout':
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            // Invalidate token
            $db->update('users', [
                'login_token' => null,
                'token_expires' => null
            ], 'id = ?', [$userId]);
        }
        
        // Destroy session
        session_destroy();
        
        // Clear cookie
        setcookie('login_token', '', time() - 3600, '/', '', false, true);
        
        successResponse('Logged out successfully');
        break;
        
    default:
        errorResponse('Invalid action', 400);
}
