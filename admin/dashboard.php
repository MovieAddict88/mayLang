<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = Database::getInstance();

// Get statistics
$stats = [
    'total_users' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'],
    'active_users' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'],
    'total_content' => $db->fetchOne("SELECT COUNT(*) as count FROM content")['count'],
    'total_movies' => $db->fetchOne("SELECT COUNT(*) as count FROM content WHERE content_type = 'movie'")['count'],
    'total_series' => $db->fetchOne("SELECT COUNT(*) as count FROM content WHERE content_type = 'series'")['count'],
    'total_live' => $db->fetchOne("SELECT COUNT(*) as count FROM content WHERE content_type = 'live'")['count'],
    'total_views' => $db->fetchOne("SELECT SUM(views) as total FROM content")['total'] ?? 0,
];

// Get recent users
$recentUsers = $db->fetchAll(
    "SELECT id, username, email, created_at, is_active, last_login 
     FROM users 
     ORDER BY created_at DESC 
     LIMIT 10"
);

// Get recent content
$recentContent = $db->fetchAll(
    "SELECT id, title, content_type, views, created_at 
     FROM content 
     ORDER BY created_at DESC 
     LIMIT 10"
);

// Get recent admin activities
$recentActivities = $db->fetchAll(
    "SELECT al.*, u.username 
     FROM admin_logs al 
     LEFT JOIN users u ON al.user_id = u.id 
     ORDER BY al.created_at DESC 
     LIMIT 15"
);

$currentUser = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b20710;
            --secondary: #221f1f;
            --background: #0a0a0a;
            --surface: #1a1a1a;
            --surface-light: #2d2d2d;
            --surface-hover: #333333;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --text-muted: #808080;
            --success: #46d369;
            --warning: #ffa500;
            --danger: #f40612;
            --info: #00d4ff;
            --shadow: 0 4px 16px rgba(0,0,0,0.3);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.4);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }
        
        /* Sidebar */
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
            cursor: pointer;
        }
        
        .menu-item:hover, .menu-item.active {
            background: var(--surface-light);
            color: var(--primary);
        }
        
        .menu-item i {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            width: clamp(20px, 4vw, 24px);
        }
        
        /* Main content */
        .main-content {
            margin-left: clamp(200px, 20vw, 280px);
            padding: clamp(20px, 3vw, 30px);
        }
        
        /* Top bar */
        .top-bar {
            background: var(--surface);
            padding: clamp(15px, 3vw, 20px) clamp(20px, 4vw, 30px);
            border-radius: clamp(12px, 2vw, 16px);
            margin-bottom: clamp(20px, 4vw, 30px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: var(--shadow);
        }
        
        .welcome-text h1 {
            font-size: clamp(1.3rem, 3vw, 1.8rem);
            margin-bottom: 5px;
        }
        
        .welcome-text p {
            color: var(--text-secondary);
            font-size: clamp(0.85rem, 2vw, 0.95rem);
        }
        
        .top-bar-actions {
            display: flex;
            gap: clamp(10px, 2vw, 15px);
            align-items: center;
        }
        
        .btn {
            padding: clamp(10px, 2vw, 12px) clamp(16px, 3vw, 20px);
            border-radius: clamp(8px, 1.5vw, 10px);
            border: none;
            cursor: pointer;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        
        .btn-secondary:hover {
            background: var(--surface-hover);
        }
        
        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(250px, 100%), 1fr));
            gap: clamp(15px, 3vw, 20px);
            margin-bottom: clamp(25px, 4vw, 35px);
        }
        
        .stat-card {
            background: var(--surface);
            padding: clamp(20px, 4vw, 25px);
            border-radius: clamp(12px, 2vw, 16px);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card.primary { border-left-color: var(--primary); }
        .stat-card.success { border-left-color: var(--success); }
        .stat-card.info { border-left-color: var(--info); }
        .stat-card.warning { border-left-color: var(--warning); }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(10px, 2vw, 15px);
        }
        
        .stat-card-title {
            font-size: clamp(0.85rem, 2vw, 0.95rem);
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .stat-card-icon {
            width: clamp(40px, 8vw, 50px);
            height: clamp(40px, 8vw, 50px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1.2rem, 3vw, 1.5rem);
        }
        
        .stat-card.primary .stat-card-icon {
            background: rgba(229, 9, 20, 0.1);
            color: var(--primary);
        }
        
        .stat-card.success .stat-card-icon {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
        }
        
        .stat-card.info .stat-card-icon {
            background: rgba(0, 212, 255, 0.1);
            color: var(--info);
        }
        
        .stat-card.warning .stat-card-icon {
            background: rgba(255, 165, 0, 0.1);
            color: var(--warning);
        }
        
        .stat-card-value {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        /* Data tables */
        .data-section {
            background: var(--surface);
            padding: clamp(20px, 4vw, 25px);
            border-radius: clamp(12px, 2vw, 16px);
            margin-bottom: clamp(20px, 4vw, 30px);
            box-shadow: var(--shadow);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: clamp(15px, 3vw, 20px);
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .section-title {
            font-size: clamp(1.1rem, 2.5vw, 1.3rem);
            font-weight: 600;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: clamp(0.85rem, 2vw, 0.95rem);
        }
        
        table th {
            background: var(--surface-light);
            padding: clamp(12px, 2.5vw, 15px);
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        
        table td {
            padding: clamp(12px, 2.5vw, 15px);
            border-bottom: 1px solid var(--surface-light);
        }
        
        table tr:hover {
            background: var(--surface-light);
        }
        
        .badge {
            padding: clamp(4px, 1vw, 6px) clamp(10px, 2vw, 12px);
            border-radius: clamp(6px, 1vw, 8px);
            font-size: clamp(0.75rem, 1.5vw, 0.85rem);
            font-weight: 500;
            display: inline-block;
        }
        
        .badge-success {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
        }
        
        .badge-danger {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
        }
        
        .badge-info {
            background: rgba(0, 212, 255, 0.1);
            color: var(--info);
        }
        
        .badge-warning {
            background: rgba(255, 165, 0, 0.1);
            color: var(--warning);
        }
        
        .action-btns {
            display: flex;
            gap: clamp(5px, 1vw, 8px);
        }
        
        .btn-sm {
            padding: clamp(6px, 1.5vw, 8px) clamp(10px, 2vw, 12px);
            font-size: clamp(0.75rem, 1.5vw, 0.85rem);
        }
        
        /* Mobile menu toggle */
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
        
        /* Responsive design */
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
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            table th, table td {
                padding: 10px 8px;
            }
        }
        
        @media (max-width: 480px) {
            .action-btns {
                flex-direction: column;
            }
        }
        
        /* Smart watch support */
        @media (max-width: 320px) {
            .main-content {
                padding: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile menu toggle -->
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>
                <i class="fas fa-film"></i>
                <?php echo SITE_NAME; ?>
            </h2>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active">
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
    
    <!-- Main content -->
    <main class="main-content">
        <!-- Top bar -->
        <div class="top-bar">
            <div class="welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($currentUser); ?>! 👋</h1>
                <p>Here's what's happening with your platform today</p>
            </div>
            <div class="top-bar-actions">
                <a href="../index.php" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    View Site
                </a>
            </div>
        </div>
        
        <!-- Stats cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-card-header">
                    <span class="stat-card-title">Total Users</span>
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['total_users']); ?></div>
                <p class="stat-card-subtitle" style="color: var(--text-secondary); font-size: clamp(0.8rem, 1.8vw, 0.9rem);">
                    <?php echo number_format($stats['active_users']); ?> active
                </p>
            </div>
            
            <div class="stat-card success">
                <div class="stat-card-header">
                    <span class="stat-card-title">Total Content</span>
                    <div class="stat-card-icon">
                        <i class="fas fa-film"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['total_content']); ?></div>
                <p class="stat-card-subtitle" style="color: var(--text-secondary); font-size: clamp(0.8rem, 1.8vw, 0.9rem);">
                    Movies, Series & Live TV
                </p>
            </div>
            
            <div class="stat-card info">
                <div class="stat-card-header">
                    <span class="stat-card-title">Movies</span>
                    <div class="stat-card-icon">
                        <i class="fas fa-video"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['total_movies']); ?></div>
                <p class="stat-card-subtitle" style="color: var(--text-secondary); font-size: clamp(0.8rem, 1.8vw, 0.9rem);">
                    Total movies
                </p>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-card-header">
                    <span class="stat-card-title">Total Views</span>
                    <div class="stat-card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['total_views']); ?></div>
                <p class="stat-card-subtitle" style="color: var(--text-secondary); font-size: clamp(0.8rem, 1.8vw, 0.9rem);">
                    All-time views
                </p>
            </div>
        </div>
        
        <!-- Recent users -->
        <div class="data-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Recent Users
                </h2>
                <a href="users.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'Never'; ?></td>
                            <td><?php echo timeAgo($user['created_at']); ?></td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-sm btn-secondary" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent content -->
        <div class="data-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-film"></i>
                    Recent Content
                </h2>
                <a href="tmdb_generator.php" class="btn btn-sm btn-primary">Add New</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Views</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentContent as $content): ?>
                        <tr>
                            <td><?php echo $content['id']; ?></td>
                            <td><?php echo htmlspecialchars($content['title']); ?></td>
                            <td>
                                <?php
                                $typeClass = $content['content_type'] === 'movie' ? 'info' : 
                                           ($content['content_type'] === 'series' ? 'success' : 'warning');
                                ?>
                                <span class="badge badge-<?php echo $typeClass; ?>">
                                    <?php echo ucfirst($content['content_type']); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($content['views']); ?></td>
                            <td><?php echo timeAgo($content['created_at']); ?></td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-sm btn-secondary" onclick="editContent(<?php echo $content['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteContent(<?php echo $content['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent activities -->
        <div class="data-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Admin Activities
                </h2>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivities as $activity): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['username']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($activity['action']); ?></span></td>
                            <td><?php echo htmlspecialchars($activity['description'] ?? '-'); ?></td>
                            <td><?php echo timeAgo($activity['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        function editUser(id) {
            // TODO: Implement user edit
            alert('Edit user functionality coming soon');
        }
        
        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                // TODO: Implement user delete
                alert('Delete user functionality coming soon');
            }
        }
        
        function editContent(id) {
            // TODO: Implement content edit
            alert('Edit content functionality coming soon');
        }
        
        function deleteContent(id) {
            if (confirm('Are you sure you want to delete this content?')) {
                // TODO: Implement content delete
                alert('Delete content functionality coming soon');
            }
        }
        
        // Close sidebar when clicking outside on mobile
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
