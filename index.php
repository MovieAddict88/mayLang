<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get filters
$searchQuery = $_GET['search'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$filters = [];
if (!empty($searchQuery)) $filters['search'] = $searchQuery;
if (!empty($typeFilter)) $filters['type'] = $typeFilter;
if (!empty($categoryFilter)) $filters['category'] = $categoryFilter;

// Get movies and categories
$movies = getMovies($filters, $page);
$categories = getCategories();
$featuredMovies = getFeaturedMovies(5);
$recentMovies = getRecentMovies(20);
$totalMovies = getTotalMovies($filters);
$totalPages = ceil($totalMovies / ITEMS_PER_PAGE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Movies & TV Shows</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
    <style>
        :root {
            --primary: #e50914;
            --primary-dark: #b20710;
            --dark: #0f0f0f;
            --dark-2: #1a1a1a;
            --dark-3: #222222;
            --light: #f5f5f5;
            --gray: #8c8c8c;
            --radius: 8px;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', 'Segoe UI', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--light);
            overflow-x: hidden;
        }
        
        /* Header */
        header {
            background: var(--dark);
            padding: 15px 5%;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--dark-3);
            box-shadow: var(--shadow);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .search-container {
            flex: 1;
            max-width: 500px;
            margin: 0 20px;
        }
        
        .search-container input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 30px;
            border: none;
            background-color: var(--dark-3);
            color: var(--light);
            font-size: 16px;
        }
        
        .search-container input:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--primary);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-menu a {
            color: var(--light);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .user-menu a:hover {
            background: var(--primary);
        }
        
        /* Filters */
        .filters {
            background: var(--dark-2);
            padding: 20px 5%;
            margin-top: 70px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filters select {
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid var(--dark-3);
            background: var(--dark);
            color: var(--light);
            font-size: 14px;
            cursor: pointer;
        }
        
        /* Featured Carousel */
        .featured-section {
            padding: 40px 5%;
            background: var(--dark-2);
        }
        
        .featured-carousel {
            position: relative;
            height: 500px;
            border-radius: var(--radius);
            overflow: hidden;
        }
        
        .carousel-item {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s;
        }
        
        .carousel-item.active {
            opacity: 1;
        }
        
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .carousel-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
        }
        
        .carousel-content h2 {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .carousel-content p {
            font-size: 16px;
            margin-bottom: 20px;
            max-width: 600px;
        }
        
        .carousel-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Content Grid */
        .content-section {
            padding: 40px 5%;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .content-card {
            background: var(--dark-2);
            border-radius: var(--radius);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .content-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .card-info {
            padding: 15px;
        }
        
        .card-info h3 {
            font-size: 16px;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .card-meta {
            display: flex;
            gap: 10px;
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 8px;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
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
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .pagination a {
            padding: 10px 15px;
            background: var(--dark-2);
            color: var(--light);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .pagination a:hover,
        .pagination a.active {
            background: var(--primary);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--dark-2);
            border-radius: var(--radius);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.5);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            z-index: 10;
        }
        
        .modal-player {
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
        }
        
        .modal-info {
            padding: 30px;
        }
        
        .modal-info h2 {
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .modal-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--gray);
        }
        
        .modal-description {
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
            
            .featured-carousel {
                height: 400px;
            }
            
            .carousel-content h2 {
                font-size: 36px;
            }
        }
        
        @media (max-width: 768px) {
            header {
                padding: 10px 3%;
            }
            
            .logo {
                font-size: 20px;
            }
            
            .search-container {
                margin: 0 10px;
            }
            
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .featured-carousel {
                height: 300px;
            }
            
            .carousel-content {
                padding: 20px;
            }
            
            .carousel-content h2 {
                font-size: 24px;
            }
            
            .carousel-content p {
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .content-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .content-card img {
                height: 200px;
            }
            
            .filters {
                padding: 15px 3%;
            }
            
            .content-section {
                padding: 20px 3%;
            }
        }
        
        @media (max-width: 320px) {
            .logo {
                font-size: 16px;
            }
            
            .search-container {
                display: none;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .featured-carousel {
                height: 200px;
            }
            
            .carousel-content h2 {
                font-size: 18px;
            }
        }
        
        /* Smartwatch (200px) */
        @media (max-width: 250px) {
            header {
                flex-direction: column;
                padding: 5px;
            }
            
            .logo {
                font-size: 14px;
            }
            
            .user-menu {
                font-size: 10px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 5px;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 12px;
            }
        }
        
        /* 4K and larger */
        @media (min-width: 2560px) {
            .content-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
            
            .featured-carousel {
                height: 700px;
            }
            
            .carousel-content h2 {
                font-size: 64px;
            }
            
            .carousel-content p {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <a href="/index.php" class="logo">
            <i class="fas fa-film"></i>
            <?php echo SITE_NAME; ?>
        </a>
        
        <div class="search-container">
            <form action="/index.php" method="GET">
                <input type="text" name="search" placeholder="Search movies, series..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </form>
        </div>
        
        <div class="user-menu">
            <?php if (isLoggedIn()): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php if (isAdmin()): ?>
                    <a href="/admin/index.php"><i class="fas fa-cog"></i> Admin</a>
                <?php endif; ?>
                <a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Filters -->
    <div class="filters">
        <form action="/index.php" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%;">
            <select name="type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="movie" <?php echo $typeFilter === 'movie' ? 'selected' : ''; ?>>Movies</option>
                <option value="series" <?php echo $typeFilter === 'series' ? 'selected' : ''; ?>>Series</option>
                <option value="live" <?php echo $typeFilter === 'live' ? 'selected' : ''; ?>>Live TV</option>
            </select>
            
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['slug']); ?>" <?php echo $categoryFilter === $cat['slug'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <!-- Featured Section -->
    <?php if (!empty($featuredMovies) && empty($searchQuery)): ?>
    <section class="featured-section">
        <div class="featured-carousel">
            <?php foreach ($featuredMovies as $index => $movie): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                <div class="carousel-content">
                    <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                    <p><?php echo htmlspecialchars(substr($movie['description'], 0, 150)) . '...'; ?></p>
                    <div class="carousel-buttons">
                        <button class="btn btn-primary" onclick="openModal(<?php echo $movie['id']; ?>)">
                            <i class="fas fa-play"></i> Play Now
                        </button>
                        <button class="btn btn-secondary" onclick="showInfo(<?php echo $movie['id']; ?>)">
                            <i class="fas fa-info-circle"></i> More Info
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Content Section -->
    <section class="content-section">
        <h2 class="section-title">
            <i class="fas fa-film"></i>
            <?php 
            if (!empty($searchQuery)) {
                echo 'Search Results for "' . htmlspecialchars($searchQuery) . '"';
            } elseif (!empty($typeFilter)) {
                echo ucfirst($typeFilter);
            } elseif (!empty($categoryFilter)) {
                echo 'Category: ' . htmlspecialchars($categoryFilter);
            } else {
                echo 'All Content';
            }
            ?>
        </h2>
        
        <?php if (empty($movies)): ?>
            <p style="text-align: center; padding: 40px; color: var(--gray);">No content found.</p>
        <?php else: ?>
            <div class="content-grid">
                <?php foreach ($movies as $movie): ?>
                <div class="content-card" onclick="openModal(<?php echo $movie['id']; ?>)">
                    <img src="<?php echo htmlspecialchars($movie['thumbnail']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    <div class="card-info">
                        <div class="card-meta">
                            <span class="badge badge-<?php echo $movie['type']; ?>">
                                <?php echo strtoupper($movie['type']); ?>
                            </span>
                            <?php if ($movie['year']): ?>
                                <span><?php echo $movie['year']; ?></span>
                            <?php endif; ?>
                            <?php if ($movie['rating']): ?>
                                <span><i class="fas fa-star" style="color: #ffc107;"></i> <?php echo $movie['rating']; ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($typeFilter) ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($typeFilter) ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?>" 
                       class="<?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo !empty($typeFilter) ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    
    <!-- Video Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">×</button>
            <div id="modalPlayer" class="modal-player"></div>
            <div class="modal-info" id="modalInfo"></div>
        </div>
    </div>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        // Carousel
        let currentSlide = 0;
        const carouselItems = document.querySelectorAll('.carousel-item');
        
        function nextSlide() {
            if (carouselItems.length === 0) return;
            carouselItems[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % carouselItems.length;
            carouselItems[currentSlide].classList.add('active');
        }
        
        if (carouselItems.length > 0) {
            setInterval(nextSlide, 5000);
        }
        
        // Modal
        let player = null;
        
        function openModal(movieId) {
            fetch(`/api/movie.php?id=${movieId}`)
                .then(res => res.json())
                .then(data => {
                    const modal = document.getElementById('videoModal');
                    const modalPlayer = document.getElementById('modalPlayer');
                    const modalInfo = document.getElementById('modalInfo');
                    
                    // Set up video player
                    if (data.video_url) {
                        if (data.video_url.includes('youtube.com') || data.video_url.includes('youtu.be')) {
                            // YouTube video
                            const videoId = data.video_url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/)[1];
                            modalPlayer.innerHTML = `<div class="plyr__video-embed"><iframe src="https://www.youtube.com/embed/${videoId}?origin=https://example.com&iv_load_policy=3&modestbranding=1&playsinline=1&showinfo=0&rel=0&enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe></div>`;
                        } else {
                            // Direct video URL
                            modalPlayer.innerHTML = `<video controls><source src="${data.video_url}" type="video/mp4"></video>`;
                        }
                        player = new Plyr(modalPlayer.querySelector('video, .plyr__video-embed'));
                    }
                    
                    // Set up info
                    modalInfo.innerHTML = `
                        <h2>${data.title}</h2>
                        <div class="modal-meta">
                            <span class="badge badge-${data.type}">${data.type.toUpperCase()}</span>
                            ${data.year ? `<span>${data.year}</span>` : ''}
                            ${data.rating ? `<span><i class="fas fa-star" style="color: #ffc107;"></i> ${data.rating}</span>` : ''}
                            ${data.duration ? `<span><i class="fas fa-clock"></i> ${data.duration} min</span>` : ''}
                            ${data.language ? `<span><i class="fas fa-language"></i> ${data.language}</span>` : ''}
                        </div>
                        <div class="modal-description">${data.description || ''}</div>
                        ${data.genre ? `<p><strong>Genre:</strong> ${data.genre}</p>` : ''}
                        ${data.cast_crew ? `<p><strong>Cast:</strong> ${data.cast_crew}</p>` : ''}
                    `;
                    
                    modal.classList.add('active');
                });
        }
        
        function closeModal() {
            const modal = document.getElementById('videoModal');
            modal.classList.remove('active');
            if (player) {
                player.destroy();
                player = null;
            }
            document.getElementById('modalPlayer').innerHTML = '';
        }
        
        function showInfo(movieId) {
            openModal(movieId);
        }
        
        // Close modal on outside click
        document.getElementById('videoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
