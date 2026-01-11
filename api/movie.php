<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $movie = getMovieById($id);
    if ($movie) {
        echo json_encode($movie);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid movie ID']);
}
