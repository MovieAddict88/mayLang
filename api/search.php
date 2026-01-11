<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($query)) {
    $results = searchMovies($query);
    echo json_encode($results);
} else {
    echo json_encode([]);
}
