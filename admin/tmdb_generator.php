<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
// Get TMDB API keys
$tmdbApiKey = getSetting('tmdb_api_key', '');
$tmdbApiKey1 = getSetting('tmdb_api_key_backup1', '');
$tmdbApiKey2 = getSetting('tmdb_api_key_backup2', '');
$tmdbApiKey3 = getSetting('tmdb_api_key_backup3', '');
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
        
        .server-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .server-item {
            display: grid;
            grid-template-columns: 1fr 2fr auto;
            gap: 10px;
            align-items: center;
        }

        .btn-small {
            padding: 8px 12px;
            font-size: 0.8rem;
        }

        #search-results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .search-result-card {
            background: var(--surface-light);
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            gap: 15px;
            box-shadow: var(--shadow);
        }

        .search-result-card img {
            width: 100px;
            object-fit: cover;
        }

        .result-info {
            padding: 15px;
            flex-grow: 1;
        }

        .loading {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid var(--text);
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        <?php else: ?>
        <div id="tmdb-generator" class="tab-content active">
            <!-- API Key Selection -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-key"></i> API Key Management</h2>
                </div>
                <div class="form-group">
                    <label for="api-key-select">Select TMDB API Key</label>
                    <select id="api-key-select" class="form-control" onchange="switchApiKey()">
                        <option value="primary">Primary Key (***<?php echo substr($tmdbApiKey, -4); ?>)</option>
                        <?php if (!empty($tmdbApiKey1)): ?><option value="backup1">Backup Key 1 (***<?php echo substr($tmdbApiKey1, -4); ?>)</option><?php endif; ?>
                        <?php if (!empty($tmdbApiKey2)): ?><option value="backup2">Backup Key 2 (***<?php echo substr($tmdbApiKey2, -4); ?>)</option><?php endif; ?>
                        <?php if (!empty($tmdbApiKey3)): ?><option value="backup3">Backup Key 3 (***<?php echo substr($tmdbApiKey3, -4); ?>)</option><?php endif; ?>
                    </select>
                    <p class="form-text">Current: <span id="current-api-status">Primary Key (***<?php echo substr($tmdbApiKey, -4); ?>)</span></p>
                </div>
            </div>
            
            <div class="form-grid">
                <!-- Movie Generator -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-film"></i> Movie Generator</h2>
                    </div>
                    <div class="form-group">
                        <label for="movie-tmdb-id">TMDB Movie ID</label>
                        <input type="number" id="movie-tmdb-id" class="form-control" placeholder="e.g., 550 (Fight Club)">
                    </div>
                    <div class="form-group">
                        <label>Additional Servers</label>
                        <div id="movie-servers" class="server-list">
                            <!-- Server items will be added here -->
                        </div>
                        <button class="btn btn-primary" onclick="addServer('movie-servers')"><i class="fas fa-plus"></i> Add Server</button>
                    </div>
                    <button class="btn btn-success" onclick="generateFromTMDB('movie')">
                        <span class="loading" id="movie-loading" style="display: none;"></span>
                        <i class="fas fa-cogs"></i> Generate Movie
                    </button>
                </div>

                <!-- TV Series Generator -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-tv"></i> TV Series Generator</h2>
                    </div>
                    <div class="form-group">
                        <label for="series-tmdb-id">TMDB TV Series ID</label>
                        <input type="number" id="series-tmdb-id" class="form-control" placeholder="e.g., 1399 (Game of Thrones)">
                    </div>
                    <div class="form-group">
                        <label for="series-seasons">Seasons to Include</label>
                        <input type="text" id="series-seasons" class="form-control" placeholder="e.g., 1,2,3 or leave empty for all">
                    </div>
                    <div class="form-group">
                        <label>Additional Servers</label>
                        <div id="series-servers" class="server-list">
                            <!-- Server items will be added here -->
                        </div>
                        <button class="btn btn-primary" onclick="addServer('series-servers')"><i class="fas fa-plus"></i> Add Server</button>
                    </div>
                    <button class="btn btn-success" onclick="generateFromTMDB('series')">
                        <span class="loading" id="series-loading" style="display: none;"></span>
                        <i class="fas fa-cogs"></i> Generate Series
                    </button>
                </div>
            </div>

            <!-- TMDB Search -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-search"></i> TMDB Search & Preview</h2>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="tmdb-search-query">Search Query</label>
                        <input type="text" id="tmdb-search-query" class="form-control" placeholder="Search for movies or TV shows...">
                    </div>
                    <div class="form-group">
                        <label for="search-subtype">Content Type</label>
                        <select id="search-subtype" class="form-control">
                            <option value="multi">All</option>
                            <option value="movie">Movies</option>
                            <option value="tv">TV Shows</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="searchTMDB()">
                    <span class="loading" id="search-loading" style="display: none;"></span>
                    <i class="fas fa-search"></i> Search TMDB
                </button>
                <div id="search-results"></div>
            </div>
        </div>
        <div id="status-container" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>
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

        // TMDB Generator Script
        const TMDB_API_KEYS = {
            'primary': '<?php echo $tmdbApiKey; ?>',
            'backup1': '<?php echo $tmdbApiKey1; ?>',
            'backup2': '<?php echo $tmdbApiKey2; ?>',
            'backup3': '<?php echo $tmdbApiKey3; ?>'
        };
        let currentApiKey = 'primary';

        function switchApiKey() {
            const select = document.getElementById('api-key-select');
            currentApiKey = select.value;
            const status = document.getElementById('current-api-status');
            status.textContent = `${select.options[select.selectedIndex].text} (Active)`;
            console.log(`Switched to ${currentApiKey} API Key`);
        }

        function addServer(containerId) {
            const container = document.getElementById(containerId);
            const serverItem = document.createElement('div');
            serverItem.className = 'server-item';
            serverItem.innerHTML = `
                <input type="text" placeholder="Server Name" class="server-name">
                <input type="url" placeholder="${containerId.includes('series') ? 'Video URL Template (use {season} {episode})' : 'Video URL'}" class="server-url">
                <button class="btn btn-danger btn-small" onclick="removeServer(this)">Remove</button>
            `;
            container.appendChild(serverItem);
        }

        function removeServer(button) {
            button.parentElement.remove();
        }

        async function generateFromTMDB(type) {
            const id = document.getElementById(`${type}-tmdb-id`).value;
            const seasons = type === 'series' ? document.getElementById('series-seasons').value : '';
            const serversContainer = document.getElementById(`${type}-servers`);
            const servers = Array.from(serversContainer.querySelectorAll('.server-item')).map(item => ({
                name: item.querySelector('.server-name').value,
                url: item.querySelector('.server-url').value
            })).filter(s => s.name && s.url);

            if (!id) {
                showStatus('TMDB ID is required.', 'error');
                return;
            }

            const loadingSpinner = document.getElementById(`${type}-loading`);
            loadingSpinner.style.display = 'inline-block';

            try {
                const response = await fetch('../api/admin_tmdb_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'generate',
                        type: type,
                        id: id,
                        seasons: seasons,
                        servers: servers,
                        apiKey: TMDB_API_KEYS[currentApiKey]
                    })
                });
                const result = await response.json();
                if (result.success) {
                    showStatus(result.message, 'success');
                } else {
                    showStatus(result.message, 'error');
                }
            } catch (error) {
                showStatus('An error occurred: ' + error.message, 'error');
            } finally {
                loadingSpinner.style.display = 'none';
            }
        }

        async function searchTMDB() {
            const query = document.getElementById('tmdb-search-query').value;
            const type = document.getElementById('search-subtype').value;
            if (!query) return;

            const loadingSpinner = document.getElementById('search-loading');
            loadingSpinner.style.display = 'inline-block';
            const resultsContainer = document.getElementById('search-results');
            resultsContainer.innerHTML = '';

            try {
                const response = await fetch('../api/admin_tmdb_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'search',
                        query: query,
                        type: type,
                        apiKey: TMDB_API_KEYS[currentApiKey]
                    })
                });
                const result = await response.json();

                if(result.success && result.results) {
                    result.results.slice(0, 10).forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'search-result-card';
                        const title = item.title || item.name;
                        const releaseDate = item.release_date || item.first_air_date || 'N/A';
                        const posterPath = item.poster_path ? `https://image.tmdb.org/t/p/w200${item.poster_path}` : 'https://via.placeholder.com/100x150';
                        const mediaType = item.media_type || (item.title ? 'movie' : 'tv');

                        card.innerHTML = `
                            <img src="${posterPath}" alt="${title}">
                            <div class="result-info">
                                <strong>${title}</strong>
                                <p>(${(new Date(releaseDate).getFullYear() || 'N/A')}) - ${mediaType.toUpperCase()}</p>
                                <button class="btn btn-primary btn-small" onclick="selectContent('${mediaType}', ${item.id})">Select</button>
                            </div>
                        `;
                        resultsContainer.appendChild(card);
                    });
                } else {
                    resultsContainer.innerHTML = `<p>${result.message || 'Error searching TMDB.'}</p>`;
                }
            } catch (error) {
                resultsContainer.innerHTML = '<p>An error occurred while searching.</p>';
            } finally {
                loadingSpinner.style.display = 'none';
            }
        }

        function selectContent(type, id) {
            if (type === 'movie') {
                document.getElementById('movie-tmdb-id').value = id;
                showStatus(`Selected movie ID: ${id}. You can now generate the movie.`, 'success');
            } else if (type === 'tv') { // TMDB search uses 'tv' for series
                document.getElementById('series-tmdb-id').value = id;
                showStatus(`Selected series ID: ${id}. You can now generate the series.`, 'success');
            }
        }

        function showStatus(message, type = 'info') {
            const container = document.getElementById('status-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
            alert.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
            container.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

    </script>
</body>
</html>
