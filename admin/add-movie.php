<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$message = '';
$error = '';
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => sanitizeInput($_POST['title'] ?? ''),
        'description' => sanitizeInput($_POST['description'] ?? ''),
        'thumbnail' => sanitizeInput($_POST['thumbnail'] ?? ''),
        'video_url' => sanitizeInput($_POST['video_url'] ?? ''),
        'trailer_url' => sanitizeInput($_POST['trailer_url'] ?? ''),
        'type' => sanitizeInput($_POST['type'] ?? 'movie'),
        'category' => sanitizeInput($_POST['category'] ?? ''),
        'year' => (int)($_POST['year'] ?? 0),
        'rating' => (float)($_POST['rating'] ?? 0),
        'duration' => (int)($_POST['duration'] ?? 0),
        'language' => sanitizeInput($_POST['language'] ?? ''),
        'country' => sanitizeInput($_POST['country'] ?? ''),
        'genre' => sanitizeInput($_POST['genre'] ?? ''),
        'cast_crew' => sanitizeInput($_POST['cast_crew'] ?? '')
    ];
    
    if (empty($data['title'])) {
        $error = 'Title is required';
    } elseif (createMovie($data)) {
        $message = 'Content added successfully!';
        // Clear form
        $_POST = [];
    } else {
        $error = 'Failed to add content';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Content - <?php echo SITE_NAME; ?></title>
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
        
        .top-nav {
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-light) 100%);
            border-bottom: 2px solid var(--primary);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        
        .back-btn {
            padding: 10px 20px;
            background: var(--surface-light);
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: var(--primary);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        
        .section {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow);
            border: 1px solid var(--surface-light);
        }
        
        .section-title {
            font-size: 28px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(70, 211, 105, 0.1);
            color: #46d369;
            border: 1px solid #46d369;
        }
        
        .alert-error {
            background: rgba(244, 6, 18, 0.1);
            color: #f40612;
            border: 1px solid #f40612;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--surface-light);
            border-radius: 8px;
            background: var(--background);
            color: var(--text);
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 30px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: var(--surface-light);
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background: var(--surface);
        }
        
        @media (max-width: 768px) {
            .section {
                padding: 25px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .section {
                padding: 20px;
            }
            
            .section-title {
                font-size: 22px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="nav-container">
            <a href="/admin/index.php" class="logo">
                <i class="fas fa-film"></i>
                <?php echo SITE_NAME; ?> Admin
            </a>
            <a href="/admin/index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </nav>
    
    <div class="container">
        <div class="section">
            <h1 class="section-title">
                <i class="fas fa-plus-circle"></i>
                Add New Content
            </h1>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="movie" <?php echo ($_POST['type'] ?? '') === 'movie' ? 'selected' : ''; ?>>Movie</option>
                            <option value="series" <?php echo ($_POST['type'] ?? '') === 'series' ? 'selected' : ''; ?>>TV Series</option>
                            <option value="live" <?php echo ($_POST['type'] ?? '') === 'live' ? 'selected' : ''; ?>>Live TV</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['slug']); ?>" <?php echo ($_POST['category'] ?? '') === $cat['slug'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" name="year" min="1900" max="2100" value="<?php echo htmlspecialchars($_POST['year'] ?? date('Y')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Rating (0-10)</label>
                        <input type="number" name="rating" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($_POST['rating'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <input type="number" name="duration" min="0" value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Language</label>
                        <input type="text" name="language" value="<?php echo htmlspecialchars($_POST['language'] ?? ''); ?>" placeholder="English">
                    </div>
                    
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>" placeholder="USA">
                    </div>
                </div>
                
                <div class="form-group full">
                    <label>Thumbnail URL *</label>
                    <input type="url" name="thumbnail" required value="<?php echo htmlspecialchars($_POST['thumbnail'] ?? ''); ?>" placeholder="https://example.com/poster.jpg">
                </div>
                
                <div class="form-group full">
                    <label>Video URL *</label>
                    <input type="url" name="video_url" required value="<?php echo htmlspecialchars($_POST['video_url'] ?? ''); ?>" placeholder="https://youtube.com/watch?v=... or direct video URL">
                </div>
                
                <div class="form-group full">
                    <label>Trailer URL</label>
                    <input type="url" name="trailer_url" value="<?php echo htmlspecialchars($_POST['trailer_url'] ?? ''); ?>" placeholder="https://youtube.com/watch?v=...">
                </div>
                
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" placeholder="Enter movie description..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group full">
                    <label>Genre</label>
                    <input type="text" name="genre" value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>" placeholder="Action, Drama, Thriller">
                </div>
                
                <div class="form-group full">
                    <label>Cast & Crew</label>
                    <input type="text" name="cast_crew" value="<?php echo htmlspecialchars($_POST['cast_crew'] ?? ''); ?>" placeholder="Actor 1, Actor 2, Director Name">
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Content
                    </button>
                    <a href="/admin/index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
