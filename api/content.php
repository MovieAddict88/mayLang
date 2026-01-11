<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

// Get parameters
$action = $_GET['action'] ?? 'list';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

switch ($action) {
    case 'list':
        // Build filters
        $where = ['is_active = 1'];
        $params = [];
        
        if (!empty($_GET['type'])) {
            $where[] = 'content_type = ?';
            $params[] = $_GET['type'];
        }
        
        if (!empty($_GET['genre'])) {
            $where[] = 'JSON_CONTAINS(genres, ?)';
            $params[] = json_encode($_GET['genre']);
        }
        
        if (!empty($_GET['year'])) {
            $where[] = 'year = ?';
            $params[] = (int)$_GET['year'];
        }
        
        if (!empty($_GET['country'])) {
            $where[] = 'JSON_CONTAINS(countries, ?)';
            $params[] = json_encode($_GET['country']);
        }
        
        if (!empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $where[] = '(title LIKE ? OR description LIKE ?)';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($_GET['featured']) && $_GET['featured'] == '1') {
            $where[] = 'is_featured = 1';
        }
        
        if (isset($_GET['trending']) && $_GET['trending'] == '1') {
            $where[] = 'is_trending = 1';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get sorting
        $sortBy = $_GET['sort'] ?? 'created_at';
        $sortOrder = strtoupper($_GET['order'] ?? 'DESC');
        
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }
        
        $allowedSorts = ['created_at', 'title', 'year', 'rating', 'views', 'popularity'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        
        $orderBy = "$sortBy $sortOrder";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM content WHERE $whereClause";
        $totalResult = $db->fetchOne($countSql, $params);
        $total = $totalResult['total'];
        
        // Get content
        $params[] = $limit;
        $params[] = $offset;
        
        $sql = "SELECT * FROM content WHERE $whereClause ORDER BY $orderBy LIMIT ? OFFSET ?";
        $content = $db->fetchAll($sql, $params);
        
        // Decode JSON fields
        foreach ($content as &$item) {
            $item['genres'] = json_decode($item['genres'] ?? '[]', true);
            $item['countries'] = json_decode($item['countries'] ?? '[]', true);
            $item['languages'] = json_decode($item['languages'] ?? '[]', true);
            $item['cast'] = json_decode($item['cast'] ?? '[]', true);
            $item['directors'] = json_decode($item['directors'] ?? '[]', true);
        }
        
        $pagination = getPaginationData($total, $page, $limit);
        
        successResponse('Content retrieved successfully', [
            'content' => $content,
            'pagination' => $pagination
        ]);
        break;
        
    case 'detail':
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            errorResponse('Invalid content ID');
        }
        
        $content = $db->fetchOne(
            "SELECT * FROM content WHERE id = ? AND is_active = 1",
            [$id]
        );
        
        if (!$content) {
            errorResponse('Content not found', 404);
        }
        
        // Decode JSON fields
        $content['genres'] = json_decode($content['genres'] ?? '[]', true);
        $content['countries'] = json_decode($content['countries'] ?? '[]', true);
        $content['languages'] = json_decode($content['languages'] ?? '[]', true);
        $content['cast'] = json_decode($content['cast'] ?? '[]', true);
        $content['directors'] = json_decode($content['directors'] ?? '[]', true);
        
        // Get seasons and episodes if series
        if ($content['content_type'] === 'series') {
            $seasons = $db->fetchAll(
                "SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number ASC",
                [$id]
            );
            
            foreach ($seasons as &$season) {
                $season['episodes'] = $db->fetchAll(
                    "SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number ASC",
                    [$season['id']]
                );
            }
            
            $content['seasons'] = $seasons;
        }
        
        // Get streaming sources
        $content['sources'] = $db->fetchAll(
            "SELECT * FROM streaming_sources WHERE content_id = ? ORDER BY priority ASC",
            [$id]
        );
        
        // Increment views
        incrementContentViews($id);
        
        successResponse('Content retrieved successfully', $content);
        break;
        
    case 'featured':
        $content = $db->fetchAll(
            "SELECT * FROM content 
             WHERE is_active = 1 AND is_featured = 1 
             ORDER BY popularity DESC 
             LIMIT ?",
            [$limit]
        );
        
        foreach ($content as &$item) {
            $item['genres'] = json_decode($item['genres'] ?? '[]', true);
            $item['countries'] = json_decode($item['countries'] ?? '[]', true);
        }
        
        successResponse('Featured content retrieved', $content);
        break;
        
    case 'trending':
        $content = $db->fetchAll(
            "SELECT * FROM content 
             WHERE is_active = 1 AND is_trending = 1 
             ORDER BY views DESC, popularity DESC 
             LIMIT ?",
            [$limit]
        );
        
        foreach ($content as &$item) {
            $item['genres'] = json_decode($item['genres'] ?? '[]', true);
            $item['countries'] = json_decode($item['countries'] ?? '[]', true);
        }
        
        successResponse('Trending content retrieved', $content);
        break;
        
    case 'genres':
        // Get all unique genres
        $content = $db->fetchAll("SELECT DISTINCT genres FROM content WHERE is_active = 1");
        $genres = [];
        
        foreach ($content as $item) {
            $itemGenres = json_decode($item['genres'] ?? '[]', true);
            $genres = array_merge($genres, $itemGenres);
        }
        
        $genres = array_unique($genres);
        sort($genres);
        
        successResponse('Genres retrieved', $genres);
        break;
        
    case 'countries':
        // Get all unique countries
        $content = $db->fetchAll("SELECT DISTINCT countries FROM content WHERE is_active = 1");
        $countries = [];
        
        foreach ($content as $item) {
            $itemCountries = json_decode($item['countries'] ?? '[]', true);
            $countries = array_merge($countries, $itemCountries);
        }
        
        $countries = array_unique($countries);
        sort($countries);
        
        successResponse('Countries retrieved', $countries);
        break;
        
    case 'years':
        $years = $db->fetchAll(
            "SELECT DISTINCT year FROM content 
             WHERE is_active = 1 AND year IS NOT NULL 
             ORDER BY year DESC"
        );
        
        $yearList = array_column($years, 'year');
        
        successResponse('Years retrieved', $yearList);
        break;
        
    case 'search':
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            successResponse('Search results', []);
            break;
        }
        
        $searchTerm = '%' . $query . '%';
        
        $content = $db->fetchAll(
            "SELECT * FROM content 
             WHERE is_active = 1 
             AND (title LIKE ? OR description LIKE ? OR meta_keywords LIKE ?)
             ORDER BY 
                CASE WHEN title LIKE ? THEN 1 ELSE 2 END,
                popularity DESC
             LIMIT ?",
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]
        );
        
        foreach ($content as &$item) {
            $item['genres'] = json_decode($item['genres'] ?? '[]', true);
        }
        
        successResponse('Search results', $content);
        break;
        
    default:
        errorResponse('Invalid action');
}
