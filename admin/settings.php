<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();
$message = '';
$messageType = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_settings') {
        $tmdbKey = sanitizeInput($_POST['tmdb_api_key'] ?? '');
        $youtubeKey = sanitizeInput($_POST['youtube_api_key'] ?? '');
        
        updateSetting('tmdb_api_key', $tmdbKey);
        updateSetting('youtube_api_key', $youtubeKey);
        
        logAdminAction($_SESSION['user_id'], 'update_settings', 'settings', null, 'Updated API keys');
        
        $message = 'Settings updated successfully!';
        $messageType = 'success';
    }
    elseif ($_POST['action'] === 'add_server') {
        $name = sanitizeInput($_POST['server_name'] ?? '');
        $baseUrl = sanitizeInput($_POST['server_url'] ?? '');
        $priority = (int)($_POST['priority'] ?? 0);
        $serverType = sanitizeInput($_POST['server_type'] ?? 'all');
        
        if (!empty($name) && !empty($baseUrl)) {
            $db->insert('embed_servers', [
                'name' => $name,
                'base_url' => $baseUrl,
                'priority' => $priority,
                'server_type' => $serverType,
                'is_active' => 1
            ]);
            
            logAdminAction($_SESSION['user_id'], 'add_server', 'embed_servers', null, "Added server: $name");
            
            $message = 'Embed server added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        }
    }
    elseif ($_POST['action'] === 'delete_server') {
        $serverId = (int)$_POST['server_id'];
        $db->delete('embed_servers', 'id = ?', [$serverId]);
        
        logAdminAction($_SESSION['user_id'], 'delete_server', 'embed_servers', $serverId, 'Deleted embed server');
        
        $message = 'Server deleted successfully!';
        $messageType = 'success';
    }
    elseif ($_POST['action'] === 'toggle_server') {
        $serverId = (int)$_POST['server_id'];
        $isActive = (int)$_POST['is_active'];
        
        $db->update('embed_servers', ['is_active' => $isActive], 'id = ?', [$serverId]);
        
        logAdminAction($_SESSION['user_id'], 'toggle_server', 'embed_servers', $serverId, 'Toggled server status');
        
        $message = 'Server status updated!';
        $messageType = 'success';
    }
}

// Get current settings
$tmdbApiKey = getSetting('tmdb_api_key', '');
$youtubeApiKey = getSetting('youtube_api_key', '');

// Get all embed servers
$embedServers = $db->fetchAll(
    "SELECT * FROM embed_servers ORDER BY priority ASC, name ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
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
        .form-group select {
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
        
        .form-group input:focus,
        .form-group select:focus {
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
        
        .btn-danger {
            background: var(--danger);
            color: var(--text);
        }
        
        .btn-sm {
            padding: clamp(8px, 2vw, 10px) clamp(15px, 3vw, 20px);
            font-size: clamp(0.8rem, 1.8vw, 0.9rem);
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
        
        .server-list {
            margin-top: clamp(20px, 4vw, 30px);
        }
        
        .server-item {
            background: var(--surface-light);
            padding: clamp(15px, 3vw, 20px);
            border-radius: clamp(8px, 1.5vw, 10px);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .server-info {
            flex: 1;
            min-width: 200px;
        }
        
        .server-info h4 {
            font-size: clamp(1rem, 2.2vw, 1.1rem);
            margin-bottom: 5px;
        }
        
        .server-info p {
            color: var(--text-secondary);
            font-size: clamp(0.85rem, 1.8vw, 0.9rem);
            word-break: break-all;
        }
        
        .server-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .badge {
            padding: clamp(4px, 1vw, 6px) clamp(10px, 2vw, 12px);
            border-radius: clamp(6px, 1vw, 8px);
            font-size: clamp(0.75rem, 1.5vw, 0.85rem);
            font-weight: 500;
        }
        
        .badge-success {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
        }
        
        .badge-danger {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
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
        
        @media (max-width: 768px) {
            .server-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .server-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
        
        @media (max-width: 320px) {
            .main-content {
                padding: 10px;
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
            <a href="settings.php" class="menu-item active">
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
            <h1><i class="fas fa-cog"></i> Settings</h1>
            <p>Configure API keys and embed servers</p>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <!-- API Keys Section -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-key"></i> API Keys</h2>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="form-group">
                    <label for="tmdb_api_key">
                        <i class="fas fa-film"></i>
                        TMDB API Key
                    </label>
                    <input 
                        type="text" 
                        id="tmdb_api_key" 
                        name="tmdb_api_key" 
                        value="<?php echo htmlspecialchars($tmdbApiKey); ?>"
                        placeholder="Enter your TMDB API key"
                    >
                    <small style="color: var(--text-secondary); display: block; margin-top: 8px;">
                        Get your API key from <a href="https://www.themoviedb.org/settings/api" target="_blank" style="color: var(--primary);">TMDB Settings</a>
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="youtube_api_key">
                        <i class="fab fa-youtube"></i>
                        YouTube API Key
                    </label>
                    <input 
                        type="text" 
                        id="youtube_api_key" 
                        name="youtube_api_key" 
                        value="<?php echo htmlspecialchars($youtubeApiKey); ?>"
                        placeholder="Enter your YouTube API key"
                    >
                    <small style="color: var(--text-secondary); display: block; margin-top: 8px;">
                        Get your API key from <a href="https://console.cloud.google.com/" target="_blank" style="color: var(--primary);">Google Cloud Console</a>
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Settings
                </button>
            </form>
        </div>
        
        <!-- Embed Servers Section -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-server"></i> Embed Servers</h2>
            </div>
            
            <!-- Add Server Form -->
            <form method="POST" action="" style="margin-bottom: 30px;">
                <input type="hidden" name="action" value="add_server">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="server_name">Server Name</label>
                        <input 
                            type="text" 
                            id="server_name" 
                            name="server_name" 
                            placeholder="e.g., VidSrc"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="server_url">Base URL</label>
                        <input 
                            type="text" 
                            id="server_url" 
                            name="server_url" 
                            placeholder="e.g., https://vidsrc.to/embed/"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <input 
                            type="number" 
                            id="priority" 
                            name="priority" 
                            value="0"
                            min="0"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="server_type">Server Type</label>
                        <select id="server_type" name="server_type">
                            <option value="all">All</option>
                            <option value="movie">Movies Only</option>
                            <option value="series">Series Only</option>
                            <option value="live">Live TV Only</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Add Server
                </button>
            </form>
            
            <!-- Server List -->
            <div class="server-list">
                <h3 style="margin-bottom: 20px; font-size: clamp(1.1rem, 2.5vw, 1.3rem);">
                    Active Servers (<?php echo count($embedServers); ?>)
                </h3>
                
                <?php if (empty($embedServers)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 20px;">
                    No embed servers configured yet. Add one above to get started.
                </p>
                <?php else: ?>
                <?php foreach ($embedServers as $server): ?>
                <div class="server-item">
                    <div class="server-info">
                        <h4><?php echo htmlspecialchars($server['name']); ?></h4>
                        <p><?php echo htmlspecialchars($server['base_url']); ?></p>
                        <p style="margin-top: 5px;">
                            Priority: <?php echo $server['priority']; ?> | 
                            Type: <?php echo ucfirst($server['server_type']); ?>
                        </p>
                    </div>
                    <div class="server-actions">
                        <span class="badge badge-<?php echo $server['is_active'] ? 'success' : 'danger'; ?>">
                            <?php echo $server['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_server">
                            <input type="hidden" name="server_id" value="<?php echo $server['id']; ?>">
                            <input type="hidden" name="is_active" value="<?php echo $server['is_active'] ? 0 : 1; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-<?php echo $server['is_active'] ? 'times' : 'check'; ?>"></i>
                                <?php echo $server['is_active'] ? 'Disable' : 'Enable'; ?>
                            </button>
                        </form>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this server?');">
                            <input type="hidden" name="action" value="delete_server">
                            <input type="hidden" name="server_id" value="<?php echo $server['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
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
