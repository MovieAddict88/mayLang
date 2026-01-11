<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_POST['content_type'] ?? 'movie';
    $title = sanitizeInput($_POST['title'] ?? '');
    $originalTitle = sanitizeInput($_POST['original_title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $posterUrl = sanitizeInput($_POST['poster_url'] ?? '');
    $backdropUrl = sanitizeInput($_POST['backdrop_url'] ?? '');
    $trailerUrl = sanitizeInput($_POST['trailer_url'] ?? '');
    $releaseDate = $_POST['release_date'] ?? null;
    $year = (int)($_POST['year'] ?? date('Y'));
    $runtime = (int)($_POST['runtime'] ?? 0);
    $rating = (float)($_POST['rating'] ?? 0);
    $imdbRating = (float)($_POST['imdb_rating'] ?? 0);
    $ageRating = sanitizeInput($_POST['age_rating'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'Released');
    $genres = $_POST['genres'] ?? [];
    $countries = $_POST['countries'] ?? [];
    $languages = $_POST['languages'] ?? [];
    $streamingSources = $_POST['streaming_sources'] ?? [];
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isTrending = isset($_POST['is_trending']) ? 1 : 0;
    
    if (empty($title)) {
        $message = 'Title is required';
        $messageType = 'error';
    } else {
        try {
            $db->beginTransaction();
            
            // Insert content
            $contentId = $db->insert('content', [
                'title' => $title,
                'original_title' => $originalTitle,
                'content_type' => $contentType,
                'description' => $description,
                'poster_url' => $posterUrl,
                'backdrop_url' => $backdropUrl,
                'trailer_url' => $trailerUrl,
                'release_date' => $releaseDate,
                'year' => $year,
                'runtime' => $runtime,
                'rating' => $rating,
                'imdb_rating' => $imdbRating,
                'genres' => json_encode($genres),
                'countries' => json_encode($countries),
                'languages' => json_encode($languages),
                'age_rating' => $ageRating,
                'status' => $status,
                'is_featured' => $isFeatured,
                'is_trending' => $isTrending,
                'is_active' => 1
            ]);
            
            // Insert streaming sources
            if ($contentId && !empty($streamingSources)) {
                foreach ($streamingSources as $source) {
                    if (!empty($source['url'])) {
                        $db->insert('streaming_sources', [
                            'content_id' => $contentId,
                            'source_name' => sanitizeInput($source['name'] ?? 'Default'),
                            'source_url' => sanitizeInput($source['url']),
                            'quality' => sanitizeInput($source['quality'] ?? 'HD'),
                            'language' => sanitizeInput($source['language'] ?? 'English'),
                            'priority' => (int)($source['priority'] ?? 0)
                        ]);
                    }
                }
            }
            
            $db->commit();
            
            logAdminAction($_SESSION['user_id'], 'add_content', 'content', $contentId, "Manually added: $title");
            
            $message = 'Content added successfully!';
            $messageType = 'success';
            
            // Clear form
            $_POST = [];
        } catch (Exception $e) {
            $db->rollback();
            $message = 'Failed to add content: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$commonGenres = ['Action', 'Adventure', 'Animation', 'Comedy', 'Crime', 'Documentary', 'Drama', 'Family', 'Fantasy', 'Horror', 'Mystery', 'Romance', 'Sci-Fi', 'Thriller', 'War', 'Western'];
$commonCountries = ['United States', 'United Kingdom', 'Canada', 'France', 'Germany', 'Japan', 'South Korea', 'India', 'China', 'Australia'];
$commonLanguages = ['English', 'Spanish', 'French', 'German', 'Japanese', 'Korean', 'Mandarin', 'Hindi', 'Italian', 'Portuguese'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Content Entry - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b20710;
            --background: #0a0a0a;
            --surface: #1a1a1a;
            --surface-light: #2d2d2d;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --success: #46d369;
            --danger: #f40612;
            --shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: clamp(200px, 20vw, 280px);
            background: var(--surface);
            border-right: 1px solid var(--surface-light);
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: clamp(20px, 3vw, 30px);
            border-bottom: 1px solid var(--surface-light);
        }
        
        .sidebar-header h2 {
            color: var(--primary);
            font-size: clamp(1.2rem, 2.5vw, 1.5rem);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-menu {
            padding: clamp(15px, 2vw, 20px) 0;
        }
        
        .menu-item {
            padding: clamp(12px, 2.5vw, 15px) clamp(20px, 3vw, 30px);
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: clamp(10px, 2vw, 15px);
            transition: all 0.3s ease;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .menu-item:hover, .menu-item.active {
            background: var(--surface-light);
            color: var(--primary);
        }
        
        .menu-item i {
            width: clamp(20px, 4vw, 24px);
        }
        
        .main-content {
            margin-left: clamp(200px, 20vw, 280px);
            padding: clamp(20px, 3vw, 30px);
        }
        
        .page-header {
            background: var(--surface);
            padding: clamp(20px, 4vw, 30px);
            border-radius: clamp(12px, 2vw, 16px);
            margin-bottom: clamp(20px, 4vw, 30px);
            box-shadow: var(--shadow);
        }
        
        .page-header h1 {
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-bottom: 10px;
        }
        
        .card {
            background: var(--surface);
            border-radius: clamp(12px, 2vw, 16px);
            padding: clamp(20px, 4vw, 30px);
            margin-bottom: clamp(20px, 4vw, 30px);
            box-shadow: var(--shadow);
        }
        
        .form-group {
            margin-bottom: clamp(20px, 4vw, 25px);
        }
        
        .form-group label {
            display: block;
            margin-bottom: clamp(8px, 2vw, 10px);
            font-weight: 600;
            color: var(--text-secondary);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: clamp(12px, 2.5vw, 15px);
            background: var(--background);
            border: 2px solid var(--surface-light);
            border-radius: clamp(8px, 1.5vw, 10px);
            color: var(--text);
            font-size: clamp(0.9rem, 2vw, 1rem);
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.15);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(250px, 100%), 1fr));
            gap: clamp(15px, 3vw, 20px);
        }
        
        .btn {
            padding: clamp(12px, 2.5vw, 15px) clamp(20px, 4vw, 30px);
            border-radius: clamp(8px, 1.5vw, 10px);
            border: none;
            cursor: pointer;
            font-size: clamp(0.9rem, 2vw, 1rem);
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: inherit;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--text);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(229, 9, 20, 0.4);
        }
        
        .btn-secondary {
            background: var(--surface-light);
            color: var(--text);
        }
        
        .alert {
            padding: clamp(15px, 3vw, 20px);
            border-radius: clamp(8px, 1.5vw, 10px);
            margin-bottom: clamp(20px, 4vw, 25px);
            border-left: 4px solid;
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .alert-success {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
            border-left-color: var(--success);
        }
        
        .alert-error {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
            border-left-color: var(--danger);
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
            font-weight: normal;
        }
        
        .source-repeater {
            margin-top: 20px;
        }
        
        .source-item {
            background: var(--background);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid var(--surface-light);
        }
        
        .menu-toggle {
            display: none;
            position: fixed;
            top: clamp(15px, 3vw, 20px);
            left: clamp(15px, 3vw, 20px);
            z-index: 1001;
            background: var(--primary);
            border: none;
            color: var(--text);
            width: clamp(40px, 8vw, 50px);
            height: clamp(40px, 8vw, 50px);
            border-radius: 50%;
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            cursor: pointer;
            box-shadow: var(--shadow);
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>
                <i class="fas fa-film"></i>
                <?php echo SITE_NAME; ?>
            </h2>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="tmdb_generator.php" class="menu-item">
                <i class="fas fa-search"></i>
                <span>TMDB Generator</span>
            </a>
            <a href="manual_generator.php" class="menu-item active">
                <i class="fas fa-edit"></i>
                <span>Manual Entry</span>
            </a>
            <a href="bulk_generator.php" class="menu-item">
                <i class="fas fa-layer-group"></i>
                <span>Bulk Operations</span>
            </a>
            <a href="data_management.php" class="menu-item">
                <i class="fas fa-database"></i>
                <span>Data Management</span>
            </a>
            <a href="settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="menu-item" style="margin-top: 20px; color: var(--danger);">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>
    
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Manual Content Entry</h1>
            <p>Add movies, TV series, or live TV manually</p>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="content_type">Content Type *</label>
                    <select id="content_type" name="content_type" required>
                        <option value="movie">Movie</option>
                        <option value="series">TV Series</option>
                        <option value="live">Live TV</option>
                    </select>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required placeholder="Enter title">
                    </div>
                    
                    <div class="form-group">
                        <label for="original_title">Original Title</label>
                        <input type="text" id="original_title" name="original_title" placeholder="Original language title">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter description or synopsis"></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="poster_url">Poster URL</label>
                        <input type="url" id="poster_url" name="poster_url" placeholder="https://...">
                    </div>
                    
                    <div class="form-group">
                        <label for="backdrop_url">Backdrop URL</label>
                        <input type="url" id="backdrop_url" name="backdrop_url" placeholder="https://...">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="trailer_url">Trailer URL (YouTube)</label>
                    <input type="url" id="trailer_url" name="trailer_url" placeholder="https://youtube.com/watch?v=...">
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="release_date">Release Date</label>
                        <input type="date" id="release_date" name="release_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" min="1900" max="2100" value="<?php echo date('Y'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="runtime">Runtime (minutes)</label>
                        <input type="number" id="runtime" name="runtime" min="0">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="rating">Rating (0-10)</label>
                        <input type="number" step="0.1" id="rating" name="rating" min="0" max="10">
                    </div>
                    
                    <div class="form-group">
                        <label for="imdb_rating">IMDB Rating</label>
                        <input type="number" step="0.1" id="imdb_rating" name="imdb_rating" min="0" max="10">
                    </div>
                    
                    <div class="form-group">
                        <label for="age_rating">Age Rating</label>
                        <select id="age_rating" name="age_rating">
                            <option value="">Select...</option>
                            <option value="G">G - General Audiences</option>
                            <option value="PG">PG - Parental Guidance</option>
                            <option value="PG-13">PG-13</option>
                            <option value="R">R - Restricted</option>
                            <option value="NC-17">NC-17</option>
                            <option value="TV-Y">TV-Y</option>
                            <option value="TV-14">TV-14</option>
                            <option value="TV-MA">TV-MA</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Genres</label>
                    <div class="checkbox-group">
                        <?php foreach ($commonGenres as $genre): ?>
                        <label>
                            <input type="checkbox" name="genres[]" value="<?php echo $genre; ?>">
                            <?php echo $genre; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Countries</label>
                    <div class="checkbox-group">
                        <?php foreach ($commonCountries as $country): ?>
                        <label>
                            <input type="checkbox" name="countries[]" value="<?php echo $country; ?>">
                            <?php echo $country; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Languages</label>
                    <div class="checkbox-group">
                        <?php foreach ($commonLanguages as $language): ?>
                        <label>
                            <input type="checkbox" name="languages[]" value="<?php echo $language; ?>">
                            <?php echo $language; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Streaming Sources</label>
                    <div class="source-repeater" id="sources">
                        <div class="source-item">
                            <div class="form-grid">
                                <div>
                                    <input type="text" name="streaming_sources[0][name]" placeholder="Source Name (e.g., Server 1)" class="form-control">
                                </div>
                                <div>
                                    <input type="url" name="streaming_sources[0][url]" placeholder="Streaming URL" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addSource()">
                        <i class="fas fa-plus"></i> Add Another Source
                    </button>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_featured"> Mark as Featured
                    </label>
                    <label style="margin-left: 20px;">
                        <input type="checkbox" name="is_trending"> Mark as Trending
                    </label>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Content
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        let sourceCount = 1;
        
        function addSource() {
            const container = document.getElementById('sources');
            const newSource = document.createElement('div');
            newSource.className = 'source-item';
            newSource.innerHTML = `
                <div class="form-grid">
                    <div>
                        <input type="text" name="streaming_sources[${sourceCount}][name]" placeholder="Source Name" class="form-control">
                    </div>
                    <div>
                        <input type="url" name="streaming_sources[${sourceCount}][url]" placeholder="Streaming URL" class="form-control">
                    </div>
                </div>
            `;
            container.appendChild(newSource);
            sourceCount++;
        }
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.menu-toggle');
            
            if (window.innerWidth <= 1024 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
