<?php
// Helper Functions

function getMovies($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
    $conn = getDbConnection();
    $offset = ($page - 1) * $limit;
    
    $where = ["status = 'active'"];
    $params = [];
    $types = "";
    
    if (!empty($filters['type'])) {
        $where[] = "type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    if (!empty($filters['category'])) {
        $where[] = "category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(title LIKE ? OR description LIKE ?)";
        $searchTerm = "%" . $filters['search'] . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $whereClause = implode(" AND ", $where);
    $sql = "SELECT * FROM movies WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getMovieById($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function getCategories() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalMovies($filters = []) {
    $conn = getDbConnection();
    
    $where = ["status = 'active'"];
    $params = [];
    $types = "";
    
    if (!empty($filters['type'])) {
        $where[] = "type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    if (!empty($filters['category'])) {
        $where[] = "category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(title LIKE ? OR description LIKE ?)";
        $searchTerm = "%" . $filters['search'] . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $whereClause = implode(" AND ", $where);
    $sql = "SELECT COUNT(*) as total FROM movies WHERE $whereClause";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

function createMovie($data) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("INSERT INTO movies (title, description, thumbnail, video_url, trailer_url, type, category, year, rating, duration, language, country, genre, cast_crew, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
    
    $stmt->bind_param("sssssssissssss", 
        $data['title'],
        $data['description'],
        $data['thumbnail'],
        $data['video_url'],
        $data['trailer_url'],
        $data['type'],
        $data['category'],
        $data['year'],
        $data['rating'],
        $data['duration'],
        $data['language'],
        $data['country'],
        $data['genre'],
        $data['cast_crew']
    );
    
    return $stmt->execute();
}

function updateMovie($id, $data) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("UPDATE movies SET title = ?, description = ?, thumbnail = ?, video_url = ?, trailer_url = ?, type = ?, category = ?, year = ?, rating = ?, duration = ?, language = ?, country = ?, genre = ?, cast_crew = ?, updated_at = NOW() WHERE id = ?");
    
    $stmt->bind_param("sssssssissssssi", 
        $data['title'],
        $data['description'],
        $data['thumbnail'],
        $data['video_url'],
        $data['trailer_url'],
        $data['type'],
        $data['category'],
        $data['year'],
        $data['rating'],
        $data['duration'],
        $data['language'],
        $data['country'],
        $data['genre'],
        $data['cast_crew'],
        $id
    );
    
    return $stmt->execute();
}

function deleteMovie($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE movies SET status = 'deleted' WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getFeaturedMovies($limit = 10) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM movies WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRecentMovies($limit = 10) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM movies WHERE status = 'active' ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function searchMovies($query) {
    $conn = getDbConnection();
    $searchTerm = "%" . $query . "%";
    $stmt = $conn->prepare("SELECT * FROM movies WHERE status = 'active' AND (title LIKE ? OR description LIKE ?) ORDER BY title ASC LIMIT 50");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAllUsers() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT id, username, email, role, status, created_at, last_login FROM users ORDER BY created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalUsers() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $row = $result->fetch_assoc();
    return $row['total'];
}

function getDashboardStats() {
    $conn = getDbConnection();
    
    $stats = [];
    
    // Total movies
    $result = $conn->query("SELECT COUNT(*) as total FROM movies WHERE status = 'active'");
    $stats['total_movies'] = $result->fetch_assoc()['total'];
    
    // Total series
    $result = $conn->query("SELECT COUNT(*) as total FROM movies WHERE status = 'active' AND type = 'series'");
    $stats['total_series'] = $result->fetch_assoc()['total'];
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $result->fetch_assoc()['total'];
    
    // Total categories
    $result = $conn->query("SELECT COUNT(*) as total FROM categories");
    $stats['total_categories'] = $result->fetch_assoc()['total'];
    
    return $stats;
}
