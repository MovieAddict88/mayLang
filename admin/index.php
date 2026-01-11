<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$stats = getDashboardStats();
$recentMovies = getRecentMovies(10);
$allUsers = getAllUsers();

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_movie'])) {
        $movieId = (int)$_POST['movie_id'];
        if (deleteMovie($movieId)) {
            $message = 'Movie deleted successfully';
        } else {
            $error = 'Failed to delete movie';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b8070f;
            --background: #0a0a0a;
            --surface: #1a1a1a;
            --surface-light: #2d2d2d;
            --text: #ffffff;
            --text-secondary: #b3b3b3;
            --success: #46d369;
            --warning: #ffa500;
            --danger: #f40612;
            --radius: 16px;
            --shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }
        
        /* Header Navigation */
        .top-nav {
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-light) 100%);
            border-bottom: 2px solid var(--primary);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-menu {
            display: flex;
            gap: 5px;
            flex: 1;
            justify-content: center;
        }
        
        .nav-item {
            padding: 10px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .nav-item:hover,
        .nav-item.active {
            background: var(--primary);
            color: var(--text);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info a {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .user-info a:hover {
            background: var(--surface-light);
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-light) 100%);
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: transform 0.3s;
            border: 1px solid var(--surface-light);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .stat-icon.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }
        
        .stat-icon.success {
            background: linear-gradient(135deg, var(--success), #2d9948);
        }
        
        .stat-icon.warning {
            background: linear-gradient(135deg, var(--warning), #cc8400);
        }
        
        .stat-icon.danger {
            background: linear-gradient(135deg, var(--danger), #c0050f);
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        /* Section */
        .section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-light);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }
        
        /* Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: var(--surface-light);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid var(--surface-light);
        }
        
        tr:hover {
            background: var(--surface-light);
        }
        
        .movie-thumb {
            width: 60px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-movie {
            background: #007bff;
        }
        
        .badge-series {
            background: #28a745;
        }
        
        .badge-live {
            background: var(--primary);
        }
        
        .badge-admin {
            background: var(--danger);
        }
        
        .badge-user {
            background: var(--text-secondary);
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(70, 211, 105, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .alert-error {
            background: rgba(244, 6, 18, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        /* Tabs */
        .tabs {
            display: none;
        }
        
        .tabs.active {
            display: block;
        }
        
        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--surface-light);
            border-radius: 8px;
            background: var(--background);
            color: var(--text);
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .nav-menu {
                gap: 3px;
            }
            
            .nav-item {
                padding: 8px 15px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                height: auto;
                padding: 15px;
                gap: 15px;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                width: 100%;
            }
            
            .nav-item {
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .container {
                padding: 20px 15px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-item {
                font-size: 12px;
                padding: 8px 10px;
            }
            
            .nav-item .nav-label {
                display: none;
            }
            
            .section {
                padding: 20px;
            }
            
            .table-responsive {
                font-size: 12px;
            }
            
            th, td {
                padding: 10px 8px;
            }
        }
        
        @media (max-width: 320px) {
            .logo {
                font-size: 18px;
            }
            
            .nav-menu {
                gap: 5px;
            }
            
            .nav-item {
                min-width: 80px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-value {
                font-size: 28px;
            }
        }
        
        /* 4K */
        @media (min-width: 2560px) {
            .container {
                max-width: 2000px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .section-title {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <a href="/admin/index.php" class="logo">
                <i class="fas fa-film"></i>
                <?php echo SITE_NAME; ?> Admin
            </a>
            
            <div class="nav-menu">
                <a href="/admin/index.php?tab=dashboard" class="nav-item <?php echo empty($_GET['tab']) || $_GET['tab'] === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="/admin/index.php?tab=movies" class="nav-item <?php echo $_GET['tab'] === 'movies' ? 'active' : ''; ?>">
                    <i class="fas fa-film"></i>
                    <span class="nav-label">Movies</span>
                </a>
                <a href="/admin/add-movie.php" class="nav-item">
                    <i class="fas fa-plus"></i>
                    <span class="nav-label">Add Content</span>
                </a>
                <a href="/admin/index.php?tab=users" class="nav-item <?php echo $_GET['tab'] === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span class="nav-label">Users</span>
                </a>
                <a href="/admin/index.php?tab=settings" class="nav-item <?php echo $_GET['tab'] === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span class="nav-label">Settings</span>
                </a>
            </div>
            
            <div class="user-info">
                <a href="/index.php" title="View Site">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <a href="/logout.php" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php
        $currentTab = $_GET['tab'] ?? 'dashboard';
        
        if ($currentTab === 'dashboard' || empty($currentTab)):
        ?>
            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-film"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_movies']; ?></div>
                    <div class="stat-label">Total Movies</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_series']; ?></div>
                    <div class="stat-label">TV Series</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_categories']; ?></div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
            
            <!-- Recent Movies -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-clock"></i>
                        Recent Content
                    </h2>
                    <a href="/admin/add-movie.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Year</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMovies as $movie): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" alt="" class="movie-thumb">
                                </td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $movie['type']; ?>">
                                        <?php echo strtoupper($movie['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($movie['category']); ?></td>
                                <td><?php echo $movie['year']; ?></td>
                                <td>
                                    <?php if ($movie['rating']): ?>
                                        <i class="fas fa-star" style="color: #ffc107;"></i>
                                        <?php echo $movie['rating']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/edit-movie.php?id=<?php echo $movie['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                        <button type="submit" name="delete_movie" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        <?php elseif ($currentTab === 'users'): ?>
            <!-- Users Management -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-users"></i>
                        User Management
                    </h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role']; ?>">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo ucfirst($user['status']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        <?php elseif ($currentTab === 'movies'): ?>
            <!-- All Movies -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-film"></i>
                        All Content
                    </h2>
                    <a href="/admin/add-movie.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Year</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $allMovies = getMovies([], 1, 100);
                            foreach ($allMovies as $movie): 
                            ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" alt="" class="movie-thumb">
                                </td>
                                <td><?php echo htmlspecialchars($movie['title']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $movie['type']; ?>">
                                        <?php echo strtoupper($movie['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($movie['category']); ?></td>
                                <td><?php echo $movie['year']; ?></td>
                                <td>
                                    <?php if ($movie['rating']): ?>
                                        <i class="fas fa-star" style="color: #ffc107;"></i>
                                        <?php echo $movie['rating']; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/edit-movie.php?id=<?php echo $movie['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                        <button type="submit" name="delete_movie" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        <?php else: ?>
            <!-- Settings -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-cog"></i>
                    Settings
                </h2>
                <p style="color: var(--text-secondary); margin-top: 20px;">Settings page coming soon...</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
