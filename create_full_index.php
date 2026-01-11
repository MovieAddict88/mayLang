<?php
/**
 * Script to create full index.php from index.html
 * Adds PHP authentication at the top
 */

$htmlContent = file_get_contents('index.html');

// PHP code to prepend
$phpCode = <<<'PHP'
<?php
require_once 'includes/config.php';

// Check if user is logged in
$isLoggedIn = false;
$userId = null;
$userName = '';
$userEmail = '';

if (isset($_COOKIE['login_token']) && !empty($_COOKIE['login_token'])) {
    require_once 'includes/Database.php';
    $db = Database::getInstance();
    
    $user = $db->fetchOne(
        "SELECT u.* FROM users u 
         JOIN user_sessions s ON u.id = s.user_id 
         WHERE s.token = ? AND s.expires_at > NOW() AND u.is_active = 1",
        [$_COOKIE['login_token']]
    );
    
    if ($user) {
        $isLoggedIn = true;
        $userId = $user['id'];
        $userName = $user['username'];
        $userEmail = $user['email'];
        
        // Update session activity
        $db->query(
            "UPDATE user_sessions SET last_activity = NOW() WHERE token = ?",
            [$_COOKIE['login_token']]
        );
    }
}
?>
PHP;

// Insert PHP at the beginning
$fullContent = $phpCode . "\n" . $htmlContent;

// Modify the JavaScript API calls - add after opening <script> tag before DOMContentLoaded
$apiCodeInsertion = <<<'JS'

        // API Configuration
        const API_BASE = '/api';
        const AUTH_TOKEN = getCookie('login_token');
        
        // Helper to get cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        // API fetch helper with authentication
        async function apiFetch(endpoint, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${AUTH_TOKEN}`
                }
            };
            
            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };
            
            try {
                const response = await fetch(`${API_BASE}${endpoint}`, mergedOptions);
                const data = await response.json();
                
                if (!data.success && data.message === 'Unauthorized') {
                    // Token expired, redirect to login
                    window.location.reload();
                }
                
                return data;
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, error: error.message };
            }
        }
        
        // Override the loadContentFromPlaylist function to use API
        const originalLoadContent = window.loadContentFromPlaylist;
        window.loadContentFromPlaylist = async function() {
            try {
                // Fetch from API instead of JSON
                const data = await apiFetch('/content.php?action=list&limit=100');
                
                if (data.success && data.data) {
                    // Transform API response to match expected format
                    const transformedData = data.data.map(item => ({
                        Title: item.title,
                        Description: item.description,
                        PosterURL: item.poster_url,
                        BackdropURL: item.backdrop_url,
                        TrailerURL: item.trailer_url,
                        Type: item.content_type,
                        Year: item.year,
                        Runtime: item.runtime,
                        Rating: item.rating,
                        IMDbRating: item.imdb_rating,
                        Genres: JSON.parse(item.genres || '[]'),
                        Countries: JSON.parse(item.countries || '[]'),
                        Languages: JSON.parse(item.languages || '[]'),
                        AgeRating: item.age_rating,
                        StreamingSources: item.streaming_sources || []
                    }));
                    
                    // Store in original format for compatibility
                    allContentFromAPI = transformedData;
                    
                    // Call original function with transformed data
                    window.displayContent(transformedData);
                    
                    return transformedData;
                }
            } catch (error) {
                console.error('Error loading content from API:', error);
                // Fallback to original method if API fails
                if (originalLoadContent) {
                    return originalLoadContent();
                }
            }
        };
        
        // Override watch later functions to use API
        async function apiToggleWatchLater(contentId) {
            const data = await apiFetch('/user_actions.php?action=watch_later_toggle', {
                method: 'POST',
                body: JSON.stringify({ content_id: contentId })
            });
            return data;
        }
        
        // Override like/dislike to use API
        async function apiReact(contentId, reactionType) {
            const data = await apiFetch('/user_actions.php?action=react', {
                method: 'POST',
                body: JSON.stringify({ 
                    content_id: contentId,
                    reaction_type: reactionType
                })
            });
            return data;
        }
        
        // Override progress tracking to use API
        async function apiSaveProgress(contentId, progress) {
            const data = await apiFetch('/user_actions.php?action=update_progress', {
                method: 'POST',
                body: JSON.stringify({ 
                    content_id: contentId,
                    progress_seconds: progress
                })
            });
            return data;
        }

JS;

// Find the position to insert API code (after first <script> tag)
$scriptPos = strpos($fullContent, '<script>');
if ($scriptPos !== false) {
    $insertPos = $scriptPos + strlen('<script>');
    $fullContent = substr_replace($fullContent, $apiCodeInsertion, $insertPos, 0);
}

// Write the final file
file_put_contents('index.php', $fullContent);

echo "✅ Full index.php created successfully!\n";
echo "📄 File size: " . number_format(strlen($fullContent)) . " bytes\n";
echo "📝 Lines: ~" . substr_count($fullContent, "\n") . "\n";
echo "\n🎬 Features included:\n";
echo "  - Full streaming interface\n";
echo "  - PHP authentication\n";
echo "  - API integration\n";
echo "  - All original features\n";

PHP;
