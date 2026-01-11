<?php
// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Sanitization functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitizeEmail($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Response functions
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function successResponse($message, $data = null) {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    jsonResponse($response);
}

function errorResponse($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

// User functions
function getUserById($userId) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM users WHERE id = ?",
        [$userId]
    );
}

function getUserByEmail($email) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM users WHERE email = ?",
        [$email]
    );
}

function getUserByUsername($username) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM users WHERE username = ?",
        [$username]
    );
}

function getUserByToken($token) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM users WHERE login_token = ? AND token_expires > NOW()",
        [$token]
    );
}

function updateUserLoginToken($userId, $token) {
    $db = Database::getInstance();
    $expires = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);
    return $db->update(
        'users',
        [
            'login_token' => $token,
            'token_expires' => $expires,
            'last_login' => date('Y-m-d H:i:s')
        ],
        'id = ?',
        [$userId]
    );
}

// Settings functions
function getSetting($key, $default = null) {
    $db = Database::getInstance();
    $setting = $db->fetchOne(
        "SELECT setting_value FROM settings WHERE setting_key = ?",
        [$key]
    );
    return $setting ? $setting['setting_value'] : $default;
}

function updateSetting($key, $value) {
    $db = Database::getInstance();
    return $db->query(
        "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
         ON DUPLICATE KEY UPDATE setting_value = ?",
        [$key, $value, $value]
    );
}

function getAllSettings() {
    $db = Database::getInstance();
    $settings = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
    $result = [];
    foreach ($settings as $setting) {
        $result[$setting['setting_key']] = $setting['setting_value'];
    }
    return $result;
}

// Content functions
function getContentById($id) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM content WHERE id = ? AND is_active = 1",
        [$id]
    );
}

function searchContent($query, $limit = 20, $offset = 0) {
    $db = Database::getInstance();
    $searchTerm = "%{$query}%";
    return $db->fetchAll(
        "SELECT * FROM content 
         WHERE is_active = 1 
         AND (title LIKE ? OR description LIKE ?) 
         ORDER BY popularity DESC 
         LIMIT ? OFFSET ?",
        [$searchTerm, $searchTerm, $limit, $offset]
    );
}

function getContentByFilters($filters = [], $limit = 20, $offset = 0) {
    $db = Database::getInstance();
    $where = ["is_active = 1"];
    $params = [];
    
    if (!empty($filters['type'])) {
        $where[] = "content_type = ?";
        $params[] = $filters['type'];
    }
    
    if (!empty($filters['genre'])) {
        $where[] = "JSON_CONTAINS(genres, ?)";
        $params[] = json_encode($filters['genre']);
    }
    
    if (!empty($filters['year'])) {
        $where[] = "year = ?";
        $params[] = $filters['year'];
    }
    
    if (!empty($filters['country'])) {
        $where[] = "JSON_CONTAINS(countries, ?)";
        $params[] = json_encode($filters['country']);
    }
    
    $whereClause = implode(' AND ', $where);
    $orderBy = !empty($filters['sort']) ? $filters['sort'] : 'popularity DESC';
    
    $params[] = $limit;
    $params[] = $offset;
    
    return $db->fetchAll(
        "SELECT * FROM content 
         WHERE {$whereClause} 
         ORDER BY {$orderBy}
         LIMIT ? OFFSET ?",
        $params
    );
}

function incrementContentViews($contentId) {
    $db = Database::getInstance();
    return $db->query(
        "UPDATE content SET views = views + 1 WHERE id = ?",
        [$contentId]
    );
}

// Logging function
function logAdminAction($userId, $action, $entityType = null, $entityId = null, $description = null) {
    $db = Database::getInstance();
    return $db->insert('admin_logs', [
        'user_id' => $userId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'description' => $description,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Time formatting
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j, Y', $timestamp);
}

// File upload handling
function uploadImage($file, $directory = 'uploads/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = BASE_PATH . '/' . $directory;
    
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $destination = $uploadPath . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return '/' . $directory . $filename;
    }
    
    return false;
}

// Pagination helper
function getPaginationData($total, $page, $perPage) {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}
