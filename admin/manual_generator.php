<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

// TODO: This file will contain manual content entry functionality
// For now, redirecting to dashboard
header('Location: dashboard.php');
exit();
