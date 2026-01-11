<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$message = '';
$messageType = '';
$contentData = null;

// Get TMDB API key
$tmdbApiKey = getSetting('tmdb_api_key', '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'fetch_by_id') {
        $tmdbId = sanitizeInput($_POST['tmdb_id'] ?? '');
        $contentType = $_POST['content_type'] ?? 'movie';
        
        if (empty($tmdbId)) {
            $message = 'Please enter a TMDB ID';
            $messageType = 'error';
        } elseif (empty($tmdbApiKey)) {
            $message = 'Please add TMDB API key in Settings first';
            $messageType = 'error';
        } else {
            // Fetch from TMDB
            $url = "https://api.themoviedb.org/3/{$contentType}/{$tmdbId}?api_key={$tmdbApiKey}&append_to_response=credits,videos,keywords";
            $response = @file_get_contents($url);
            
            if ($response) {
                $contentData = json_decode($response, true);
                if (isset($contentData['id'])) {
                    $message = 'Content fetched successfully! Review and save below.';
                    $messageType = 'success';
                } else {
                    $message = 'Content not found. Check the TMDB ID.';
                    $messageType = 'error';
                    $contentData = null;
                }
            } else {
                $message = 'Failed to fetch from TMDB. Check your API key.';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'save_content') {
        // Save content to database
        $contentType = $_POST['content_type'] ?? 'movie';
        $tmdbId = $_POST['tmdb_id'] ?? '';
        $title = sanitizeInput($_POST['title'] ?? '');
        $originalTitle = sanitizeInput($_POST['original_title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $posterUrl = sanitizeInput($_POST['poster_url'] ?? '');
        $backdropUrl = sanitizeInput($_POST['backdrop_url'] ?? '');
        $trailerUrl = sanitizeInput($_POST['trailer_url'] ?? '');
        $releaseDate = $_POST['release_date'] ?? null;
        $year = (int)($_POST['year'] ?? 0);
        $runtime = (int)($_POST['runtime'] ?? 0);
        $rating = (float)($_POST['rating'] ?? 0);
        $genres = $_POST['genres'] ?? [];
        $countries = $_POST['countries'] ?? [];
        $languages = $_POST['languages'] ?? [];
        $ageRating = sanitizeInput($_POST['age_rating'] ?? '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isTrending = isset($_POST['is_trending']) ? 1 : 0;
        
        if (empty($title)) {
            $message = 'Title is required';
            $messageType = 'error';
        } else {
            // Check if content already exists
            $existing = $db->fetchOne(
                "SELECT id FROM content WHERE tmdb_id = ? AND content_type = ?",
                [$tmdbId, $contentType]
            );
            
            if ($existing) {
                $message = 'This content already exists in the database!';
                $messageType = 'error';
            } else {
                $contentId = $db->insert('content', [
                    'tmdb_id' => $tmdbId,
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
                    'genres' => json_encode($genres),
                    'countries' => json_encode($countries),
                    'languages' => json_encode($languages),
                    'age_rating' => $ageRating,
                    'is_featured' => $isFeatured,
                    'is_trending' => $isTrending,
                    'is_active' => 1
                ]);
                
                if ($contentId) {
                    logAdminAction($_SESSION['user_id'], 'add_content', 'content', $contentId, "Added content: $title");
                    $message = 'Content saved successfully!';
                    $messageType = 'success';
                    $contentData = null;
                } else {
                    $message = 'Failed to save content. Please try again.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMDB Generator - Admin Panel</title>
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
        
        .page-header p {
            color: var(--text-secondary);
            font-size: clamp(0.9rem, 2vw, 1rem);
        }
        
        .card {
            background: var(--surface);
            border-radius: clamp(12px, 2vw, 16px);
            padding: clamp(20px, 4vw, 30px);
            margin-bottom: clamp(20px, 4vw, 30px);
            box-shadow: var(--shadow);
        }
        
        .card-header {
            margin-bottom: clamp(20px, 4vw, 25px);
            padding-bottom: 15px;
            border-bottom: 2px solid var(--surface-light);
        }
        
        .card-header h2 {
            font-size: clamp(1.2rem, 2.5vw, 1.5rem);
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .btn-success {
            background: var(--success);
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
        
        .preview-section {
            background: var(--background);
            padding: clamp(20px, 4vw, 25px);
            border-radius: clamp(8px, 1.5vw, 10px);
            margin-top: 20px;
        }
        
        .preview-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .preview-poster {
            width: 100%;
            border-radius: 8px;
            box-shadow: var(--shadow);
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
            
            .preview-grid {
                grid-template-columns: 1fr;
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
            <a href="tmdb_generator.php" class="menu-item active">
                <i class="fas fa-search"></i>
                <span>TMDB Generator</span>
            </a>
            <a href="manual_generator.php" class="menu-item">
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
            <h1><i class="fas fa-search"></i> TMDB Content Generator</h1>
            <p>Import content directly from The Movie Database (TMDB)</p>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($tmdbApiKey)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>TMDB API Key Required!</strong> Please add your TMDB API key in <a href="settings.php" style="color: var(--primary);">Settings</a> to use this feature.
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-search"></i> Fetch from TMDB</h2>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="fetch_by_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="content_type">Content Type</label>
                        <select id="content_type" name="content_type" required>
                            <option value="movie">Movie</option>
                            <option value="tv">TV Series</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tmdb_id">TMDB ID</label>
                        <input type="text" id="tmdb_id" name="tmdb_id" placeholder="e.g., 550 for Fight Club" required>
                        <small style="color: var(--text-secondary); display: block; margin-top: 5px;">
                            Find ID on TMDB website URL (e.g., themoviedb.org/movie/<strong>550</strong>)
                        </small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download"></i>
                    Fetch Content
                </button>
            </form>
        </div>
        
        <?php if ($contentData): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-eye"></i> Preview & Save</h2>
            </div>
            
            <div class="preview-section">
                <div class="preview-grid">
                    <?php if (!empty($contentData['poster_path'])): ?>
                    <img src="https://image.tmdb.org/t/p/w500<?php echo $contentData['poster_path']; ?>" 
                         alt="Poster" class="preview-poster">
                    <?php endif; ?>
                    
                    <div>
                        <h3 style="margin-bottom: 10px;">
                            <?php echo htmlspecialchars($contentData['title'] ?? $contentData['name'] ?? 'Untitled'); ?>
                        </h3>
                        <p style="color: var(--text-secondary); margin-bottom: 15px;">
                            <?php echo htmlspecialchars($contentData['overview'] ?? 'No description available'); ?>
                        </p>
                        <p style="color: var(--text-secondary);">
                            <strong>Rating:</strong> <?php echo $contentData['vote_average'] ?? 'N/A'; ?>/10<br>
                            <strong>Release:</strong> <?php echo $contentData['release_date'] ?? $contentData['first_air_date'] ?? 'Unknown'; ?><br>
                            <strong>Runtime:</strong> <?php echo $contentData['runtime'] ?? $contentData['episode_run_time'][0] ?? 'N/A'; ?> min
                        </p>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="save_content">
                <input type="hidden" name="content_type" value="<?php echo $_POST['content_type'] ?? 'movie'; ?>">
                <input type="hidden" name="tmdb_id" value="<?php echo $contentData['id']; ?>">
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo htmlspecialchars($contentData['title'] ?? $contentData['name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="original_title">Original Title</label>
                    <input type="text" id="original_title" name="original_title" 
                           value="<?php echo htmlspecialchars($contentData['original_title'] ?? $contentData['original_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($contentData['overview'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" 
                               value="<?php echo date('Y', strtotime($contentData['release_date'] ?? $contentData['first_air_date'] ?? 'now')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="runtime">Runtime (minutes)</label>
                        <input type="number" id="runtime" name="runtime" 
                               value="<?php echo $contentData['runtime'] ?? $contentData['episode_run_time'][0] ?? 0; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <input type="number" step="0.1" id="rating" name="rating" 
                               value="<?php echo $contentData['vote_average'] ?? 0; ?>">
                    </div>
                </div>
                
                <input type="hidden" name="poster_url" 
                       value="https://image.tmdb.org/t/p/w500<?php echo $contentData['poster_path'] ?? ''; ?>">
                <input type="hidden" name="backdrop_url" 
                       value="https://image.tmdb.org/t/p/original<?php echo $contentData['backdrop_path'] ?? ''; ?>">
                <input type="hidden" name="release_date" 
                       value="<?php echo $contentData['release_date'] ?? $contentData['first_air_date'] ?? ''; ?>">
                
                <?php
                $genres = array_column($contentData['genres'] ?? [], 'name');
                foreach ($genres as $genre) {
                    echo '<input type="hidden" name="genres[]" value="' . htmlspecialchars($genre) . '">';
                }
                ?>
                
                <div style="margin-top: 20px;">
                    <label>
                        <input type="checkbox" name="is_featured"> Mark as Featured
                    </label>
                    <label style="margin-left: 20px;">
                        <input type="checkbox" name="is_trending"> Mark as Trending
                    </label>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Save to Database
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
    
    <script>
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
