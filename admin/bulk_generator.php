<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

// TODO: This file will contain bulk content operations functionality
// For now, redirecting to dashboard
header('Location: dashboard.php');
exit();
