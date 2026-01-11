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
        
        <div id="tmdb-generator" class="tab-content active">
            <!-- API Key Selection -->
            <div class="card">
                <h2>🔑 API Key Management</h2>
                <div class="form-group">
                    <label for="api-key-select">Select TMDB API Key (for backup detection avoidance)</label>
                    <select id="api-key-select" onchange="switchApiKey()">
                        <option value="primary">Primary Key (***61)</option>
                        <option value="backup1">Backup Key 1 (***69)</option>
                        <option value="backup2">Backup Key 2 (***3f)</option>
                        <option value="backup3">Backup Key 3 (***8d)</option>
                    </select>
                    <small class="api-status">Current: <span id="current-api-status">Primary (Active)</span></small>
                </div>
            </div>
            
            <div class="grid grid-2">
                <div class="card">
                    <h2>🎬 Movie Generator</h2>
                    <div class="form-group">
                        <label>TMDB Movie ID</label>
                        <input type="number" id="movie-tmdb-id" placeholder="e.g., 550 (Fight Club)">
                    </div>
                    <div class="form-group">
                        <label>Additional Servers</label>
                        <div id="movie-servers" class="server-list">
                                                    <div class="server-item">
                            <input type="text" placeholder="Server Name" class="server-name">
                            <input type="url" placeholder="Video URL" class="server-url">
                            <button class="paste-btn" onclick="pasteFromClipboard(this)">📋 Paste</button>
                            <button class="btn btn-danger btn-small" onclick="removeServer(this)">Remove</button>
                        </div>
                        </div>
                        <button class="btn btn-secondary btn-small" onclick="addServer('movie-servers')">+ Add Server</button>
                    </div>
                    <button class="btn btn-primary" onclick="generateFromTMDB('movie')">
                        <span class="loading" id="movie-loading" style="display: none;"></span>
                        Generate Movie
                    </button>
                </div>

                <div class="card">
                    <h2>📺 TV Series Generator</h2>
                    <div class="form-group">
                        <label>TMDB TV Series ID</label>
                        <input type="number" id="series-tmdb-id" placeholder="e.g., 1399 (Game of Thrones)">
                    </div>
                    <div class="form-group">
                        <label>Seasons to Include</label>
                        <input type="text" id="series-seasons" placeholder="e.g., 1,2,3 or leave empty for all">
                    </div>
                    <div class="form-group">
                        <label>Additional Servers</label>
                        <div id="series-servers" class="server-list">
                                                    <div class="server-item">
                            <input type="text" placeholder="Server Name" class="server-name">
                            <input type="url" placeholder="Video URL Template (use {season} {episode})" class="server-url">
                            <button class="paste-btn" onclick="pasteFromClipboard(this)">📋 Paste</button>
                            <button class="btn btn-danger btn-small" onclick="removeServer(this)">Remove</button>
                        </div>
                        </div>
                        <button class="btn btn-secondary btn-small" onclick="addServer('series-servers')">+ Add Server</button>
                    </div>
                    <button class="btn btn-primary" onclick="generateFromTMDB('series')">
                        <span class="loading" id="series-loading" style="display: none;"></span>
                        Generate Series
                    </button>
                </div>
            </div>

            <div class="card">
                <h2>🔍 TMDB Search & Preview</h2>
                <div class="grid">
                    <div class="form-group">
                        <label>Search Query</label>
                        <input type="text" id="tmdb-search" placeholder="Search for movies or TV shows...">
                    </div>
                    <div class="form-group">
                        <label>Content Type</label>
                        <select id="search-type" onchange="handleSearchTypeChange()">
                            <option value="search">🔍 Search Mode</option>
                            <option value="hollywood">🎬 Hollywood</option>
                            <option value="anime">🇯🇵 Anime</option>
                            <option value="animation">🎨 Animation</option>
                            <option value="kids">🧸 Kids / Family</option>
                            <option value="kdrama">🇰🇷 K-Drama (Korean)</option>
                            <option value="cdrama">🇨🇳 C-Drama (Chinese)</option>
                            <option value="jdrama">🇯🇵 J-Drama (Japanese)</option>
                            <option value="pinoy">🇵🇭 Pinoy Series (Filipino)</option>
                            <option value="thai">🇹🇭 Thai Drama</option>
                            <option value="indian">🇮🇳 Indian Series</option>
                            <option value="turkish">🇹🇷 Turkish Drama</option>
                            <option value="korean-variety">🎭 Korean Variety Shows</option>
                        </select>
                    </div>
                    <div class="form-group" id="search-input-group">
                        <label>Search Query</label>
                        <input type="text" id="tmdb-search-input" placeholder="Search for movies or TV shows...">
                        <div class="form-group">
                            <label>Search Type</label>
                            <select id="search-subtype">
                                <option value="multi">All</option>
                                <option value="movie">Movies</option>
                                <option value="tv">TV Shows</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="regional-browse-group" style="display: none;">
                        <label>Content Type</label>
                        <select id="regional-content-type">
                            <option value="tv">📺 TV Series/Dramas</option>
                            <option value="movie">🎬 Movies</option>
                            <option value="both">🎭 Both Movies & Series</option>
                        </select>
                        <label>Select Year to Browse</label>
                        <select id="year-filter" onchange="loadRegionalContent()">
                            <option value="">-- Select Year --</option>
                            <option value="2025">2025 (Latest)</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                            <option value="2019">2019</option>
                            <option value="2018">2018</option>
                            <option value="2017">2017</option>
                            <option value="2016">2016</option>
                            <option value="2015">2015</option>
                            <option value="2014">2014</option>
                            <option value="2013">2013</option>
                            <option value="2012">2012</option>
                            <option value="2011">2011</option>
                            <option value="2010">2010</option>
                            <option value="2009">2009</option>
                            <option value="2008">2008</option>
                            <option value="2007">2007</option>
                            <option value="2006">2006</option>
                            <option value="2005">2005</option>
                            <option value="2004">2004</option>
                            <option value="2003">2003</option>
                            <option value="2002">2002</option>
                            <option value="2001">2001</option>
                            <option value="2000">2000</option>
                            <option value="1999">1999</option>
                            <option value="1998">1998</option>
                            <option value="1997">1997</option>
                            <option value="1996">1996</option>
                            <option value="1995">1995</option>
                            <option value="all-recent">All Recent (2020-2025)</option>
                            <option value="all-2010s">All 2010s (2010-2019)</option>
                            <option value="all-2000s">All 2000s (2000-2009)</option>
                            <option value="all-classic">All Classic (1990-1999)</option>
                            <option value="all-time">All Time (1990-2025)</option>
                        </select>
                        <small style="display: block; margin-top: 8px; color: var(--text-secondary);">
                            Choose any year (1995-2025) or decade collection for comprehensive results
                        </small>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="searchTMDB()">
                    <span class="loading" id="search-loading" style="display: none;"></span>
                    Search TMDB
                </button>
                <div id="search-results" class="preview-grid"></div>
            </div>
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

        const TMDB_API_KEYS = {
            'primary': '<?php echo $tmdbApiKey; ?>',
            'backup1': '<?php echo getSetting('tmdb_api_key_backup1', ''); ?>',
            'backup2': '<?php echo getSetting('tmdb_api_key_backup2', ''); ?>',
            'backup3': '<?php echo getSetting('tmdb_api_key_backup3', ''); ?>'
        };

        let currentApiKey = 'primary';
        const TMDB_BASE_URL = 'https://api.themoviedb.org/3';
        const TMDB_IMAGE_BASE = 'https://image.tmdb.org/t/p/w500';

        function getTMDBApiKey() {
            return TMDB_API_KEYS[currentApiKey] || TMDB_API_KEYS.primary;
        }

        function switchApiKey() {
            const select = document.getElementById('api-key-select');
            currentApiKey = select.value;
            updateApiDropdown();
        }

        function updateApiDropdown() {
            const select = document.getElementById('api-key-select');
            const status = document.getElementById('current-api-status');

            if (select) {
                select.value = currentApiKey;
            }

            if (status) {
                const keyName = currentApiKey.charAt(0).toUpperCase() + currentApiKey.slice(1);
                status.textContent = `${keyName} (Active)`;
            }
        }

        function showStatus(type, message) {
            const container = document.querySelector('.main-content');
            let alertBox = container.querySelector('.alert-dynamic');
            if (alertBox) {
                alertBox.remove();
            }

            const alertType = type === 'error' ? 'alert-error' : 'alert-success';
            const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';

            alertBox = document.createElement('div');
            alertBox.className = `alert ${alertType} alert-dynamic`;
            alertBox.innerHTML = `<i class="fas ${icon}"></i> ${message}`;

            container.insertBefore(alertBox, container.firstChild);

            setTimeout(() => {
                alertBox.remove();
            }, 5000);
        }

        async function fetchTMDB(endpoint, params = {}) {
            const apiKey = getTMDBApiKey();
            const url = new URL(`${TMDB_BASE_URL}${endpoint}`);
            url.searchParams.append('api_key', apiKey);
            for (const key in params) {
                url.searchParams.append(key, params[key]);
            }

            const response = await fetch(url);
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.status_message || 'TMDB API error');
            }
            return response.json();
        }

        function generateFromTMDB(type) {
            const id = document.getElementById(`${type}-tmdb-id`).value;
            if (!id) {
                showStatus('error', `Please enter a TMDB ${type} ID.`);
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'fetch_by_id';
            form.appendChild(actionInput);

            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'content_type';
            typeInput.value = type;
            form.appendChild(typeInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'tmdb_id';
            idInput.value = id;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        }

        async function searchTMDB() {
            const query = document.getElementById('tmdb-search-input').value;
            const type = document.getElementById('search-subtype').value;
            if (!query) {
                showStatus('error', 'Please enter a search query.');
                return;
            }

            const loading = document.getElementById('search-loading');
            loading.style.display = 'inline-block';

            const resultsContainer = document.getElementById('search-results');
            resultsContainer.innerHTML = '';

            try {
                const data = await fetchTMDB(`/search/${type}`, { query });
                if (data.results) {
                    displaySearchResults(data.results);
                }
            } catch (error) {
                showStatus('error', `Search failed: ${error.message}`);
            } finally {
                loading.style.display = 'none';
            }
        }

        function displaySearchResults(results) {
            const resultsContainer = document.getElementById('search-results');
            resultsContainer.innerHTML = '';

            results.forEach(item => {
                if (!item.poster_path) return;

                const card = document.createElement('div');
                card.className = 'card';
                card.style.textAlign = 'center';

                const title = item.title || item.name;
                const year = (item.release_date || item.first_air_date || '').substring(0, 4);
                const type = item.media_type || (item.title ? 'movie' : 'tv');

                card.innerHTML = `
                    <img src="${TMDB_IMAGE_BASE}${item.poster_path}" alt="${title}" style="max-width: 100%; border-radius: 8px;">
                    <h4 style="margin-top: 10px;">${title} (${year})</h4>
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="generateFromSearchResult('${type}', '${item.id}')">
                        Generate
                    </button>
                `;
                resultsContainer.appendChild(card);
            });
        }

        function generateFromSearchResult(type, id) {
             const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'fetch_by_id';
            form.appendChild(actionInput);

            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'content_type';
            typeInput.value = type;
            form.appendChild(typeInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'tmdb_id';
            idInput.value = id;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        }

        function handleSearchTypeChange() {
            const searchType = document.getElementById('search-type').value;
            const searchGroup = document.getElementById('search-input-group');
            const regionalGroup = document.getElementById('regional-browse-group');

            if (searchType === 'search') {
                searchGroup.style.display = 'block';
                regionalGroup.style.display = 'none';
            } else {
                searchGroup.style.display = 'none';
                regionalGroup.style.display = 'block';
            }
        }
    </script>
</body>
</html>
