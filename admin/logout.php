<?php
require_once '../includes/config.php';

// Destroy session
session_destroy();

// Clear cookies
setcookie('login_token', '', time() - 3600, '/', '', false, true);

// Redirect to login
header('Location: index.php');
exit();
