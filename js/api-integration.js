/**
 * CineCraze API Integration
 * Replaces JSON file fetching with database API calls
 */

const API_BASE = '/api';
const AUTH_TOKEN_KEY = 'login_token';

// Get authentication token from cookie
function getAuthToken() {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${AUTH_TOKEN_KEY}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// API fetch wrapper with authentication
async function apiFetch(endpoint, options = {}) {
    const token = getAuthToken();
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };
    
    try {
        const response = await fetch(`${API_BASE}${endpoint}`, mergedOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        // Check if unauthorized
        if (!data.success && (data.message === 'Unauthorized' || data.message === 'Invalid token')) {
            console.warn('❌ Session expired or unauthorized');
            window.location.reload();
            return { success: false, error: 'Unauthorized' };
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', endpoint, error);
        return { success: false, error: error.message };
    }
}

// Fetch all content from API
window.fetchDataFromAPI = async function() {
    try {
        console.log('📡 Fetching content from API...');
        
        const response = await apiFetch('/content.php?action=list&limit=1000');
        
        if (response.success && response.data) {
            // Transform API response to match expected CineCraze format
            const transformedData = response.data.map(item => {
                // Parse JSON fields
                const genres = typeof item.genres === 'string' ? JSON.parse(item.genres || '[]') : (item.genres || []);
                const countries = typeof item.countries === 'string' ? JSON.parse(item.countries || '[]') : (item.countries || []);
                const languages = typeof item.languages === 'string' ? JSON.parse(item.languages || '[]') : (item.languages || []);
                
                return {
                    Title: item.title,
                    Description: item.description || '',
                    PosterURL: item.poster_url || '',
                    BackdropURL: item.backdrop_url || item.poster_url || '',
                    TrailerURL: item.trailer_url || '',
                    Type: item.content_type || 'movie',
                    Year: item.year || new Date().getFullYear(),
                    Runtime: item.runtime || 0,
                    Rating: parseFloat(item.rating) || 0,
                    IMDbRating: parseFloat(item.imdb_rating || item.rating) || 0,
                    Genres: genres,
                    Countries: countries,
                    Languages: languages,
                    AgeRating: item.age_rating || '',
                    Status: item.status || 'Released',
                    StreamingSources: item.streaming_sources || [],
                    isFeatured: item.is_featured || false,
                    isTrending: item.is_trending || false
                };
            });
            
            console.log(`✅ Loaded ${transformedData.length} items from API`);
            
            return transformedData;
        } else {
            console.error('❌ API returned error:', response.error || response.message);
            return null;
        }
    } catch (error) {
        console.error('❌ Failed to fetch from API:', error);
        return null;
    }
};

// Search content via API
window.apiSearch = async function(query) {
    try {
        if (!query || query.trim() === '') return [];
        
        const response = await apiFetch(`/content.php?action=search&q=${encodeURIComponent(query)}`);
        
        if (response.success && response.data) {
            return response.data.map(item => ({
                Title: item.title,
                Description: item.description || '',
                PosterURL: item.poster_url || '',
                Type: item.content_type || 'movie',
                Year: item.year,
                Rating: parseFloat(item.rating) || 0
            }));
        }
        return [];
    } catch (error) {
        console.error('Search API Error:', error);
        return [];
    }
};

// Watch Later Toggle via API
window.apiWatchLaterToggle = async function(contentId, contentData) {
    try {
        const response = await apiFetch('/user_actions.php?action=watch_later_toggle', {
            method: 'POST',
            body: JSON.stringify({
                content_id: contentId,
                content_data: contentData // Optional: pass content details
            })
        });
        
        if (response.success) {
            return response.in_watch_later; // true if added, false if removed
        }
        return null;
    } catch (error) {
        console.error('Watch Later API Error:', error);
        return null;
    }
};

// Like/Dislike via API
window.apiReact = async function(contentId, reactionType) {
    try {
        const response = await apiFetch('/user_actions.php?action=react', {
            method: 'POST',
            body: JSON.stringify({
                content_id: contentId,
                reaction_type: reactionType // 'like' or 'dislike'
            })
        });
        
        if (response.success) {
            return {
                likes: parseInt(response.likes) || 0,
                dislikes: parseInt(response.dislikes) || 0,
                user_reaction: response.user_reaction || null
            };
        }
        return null;
    } catch (error) {
        console.error('React API Error:', error);
        return null;
    }
};

// Save watch progress via API
window.apiSaveProgress = async function(contentId, progressSeconds) {
    try {
        const response = await apiFetch('/user_actions.php?action=update_progress', {
            method: 'POST',
            body: JSON.stringify({
                content_id: contentId,
                progress_seconds: parseInt(progressSeconds)
            })
        });
        
        return response.success || false;
    } catch (error) {
        console.error('Progress API Error:', error);
        return false;
    }
};

// Get user's watch later list
window.apiGetWatchLater = async function() {
    try {
        const response = await apiFetch('/user_actions.php?action=get_watch_later');
        
        if (response.success && response.data) {
            return response.data;
        }
        return [];
    } catch (error) {
        console.error('Get Watch Later API Error:', error);
        return [];
    }
};

// Get content details by ID
window.apiGetContentDetail = async function(contentId) {
    try {
        const response = await apiFetch(`/content.php?action=detail&id=${contentId}`);
        
        if (response.success && response.data) {
            const item = response.data;
            return {
                Title: item.title,
                Description: item.description || '',
                PosterURL: item.poster_url || '',
                BackdropURL: item.backdrop_url || item.poster_url || '',
                TrailerURL: item.trailer_url || '',
                Type: item.content_type || 'movie',
                Year: item.year,
                Runtime: item.runtime || 0,
                Rating: parseFloat(item.rating) || 0,
                IMDbRating: parseFloat(item.imdb_rating || item.rating) || 0,
                Genres: JSON.parse(item.genres || '[]'),
                Countries: JSON.parse(item.countries || '[]'),
                Languages: JSON.parse(item.languages || '[]'),
                AgeRating: item.age_rating || '',
                Status: item.status || 'Released',
                StreamingSources: item.streaming_sources || []
            };
        }
        return null;
    } catch (error) {
        console.error('Get Content Detail API Error:', error);
        return null;
    }
};

// Override original functions if they exist
if (typeof window.fetchData === 'function') {
    const originalFetchData = window.fetchData;
    
    window.fetchData = async function() {
        console.log('🔄 Attempting to load from API...');
        
        const apiData = await fetchDataFromAPI();
        
        if (apiData && apiData.length > 0) {
            window.cineData = apiData;
            console.log('✅ Using API data');
            return;
        }
        
        console.log('⚠️ API failed, falling back to original method');
        await originalFetchData();
    };
}

// Integrate with watch later if function exists
if (typeof window.toggleWatchLater === 'function') {
    const originalToggleWatchLater = window.toggleWatchLater;
    
    window.toggleWatchLater = async function(content, buttonElement) {
        const contentId = content.Title;
        const result = await apiWatchLaterToggle(contentId, content);
        
        if (result !== null) {
            if (result) {
                alert(`"${content.Title}" added to Watch Later.`);
                if (buttonElement) buttonElement.classList.add('active');
            } else {
                alert(`"${content.Title}" removed from Watch Later.`);
                if (buttonElement) buttonElement.classList.remove('active');
            }
            return;
        }
        
        // Fallback to original method
        await originalToggleWatchLater(content, buttonElement);
    };
}

// Integrate with like/dislike if functions exist
if (typeof window.handleLike === 'function') {
    const originalHandleLike = window.handleLike;
    
    window.handleLike = async function() {
        if (!window.currentContentInfo || !window.currentContentInfo.Title) return;
        
        const contentId = window.currentContentInfo.Title;
        const result = await apiReact(contentId, 'like');
        
        if (result) {
            // Update UI with API response
            if (window.elements && window.elements.likeCountSpan) {
                window.elements.likeCountSpan.textContent = formatCount(result.likes);
                window.elements.dislikeCountSpan.textContent = formatCount(result.dislikes);
                
                window.elements.likeBtn.classList.remove('active');
                window.elements.dislikeBtn.classList.remove('active');
                
                if (result.user_reaction === 'like') {
                    window.elements.likeBtn.classList.add('active');
                } else if (result.user_reaction === 'dislike') {
                    window.elements.dislikeBtn.classList.add('active');
                }
            }
            return;
        }
        
        // Fallback to original method
        originalHandleLike();
    };
}

if (typeof window.handleDislike === 'function') {
    const originalHandleDislike = window.handleDislike;
    
    window.handleDislike = async function() {
        if (!window.currentContentInfo || !window.currentContentInfo.Title) return;
        
        const contentId = window.currentContentInfo.Title;
        const result = await apiReact(contentId, 'dislike');
        
        if (result) {
            // Update UI with API response
            if (window.elements && window.elements.likeCountSpan) {
                window.elements.likeCountSpan.textContent = formatCount(result.likes);
                window.elements.dislikeCountSpan.textContent = formatCount(result.dislikes);
                
                window.elements.likeBtn.classList.remove('active');
                window.elements.dislikeBtn.classList.remove('active');
                
                if (result.user_reaction === 'like') {
                    window.elements.likeBtn.classList.add('active');
                } else if (result.user_reaction === 'dislike') {
                    window.elements.dislikeBtn.classList.add('active');
                }
            }
            return;
        }
        
        // Fallback to original method
        originalHandleDislike();
    };
}

// Helper function for formatting counts
function formatCount(count) {
    if (count >= 1000000) return (count / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (count >= 1000) return (count / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
    return count.toString();
}

console.log('✅ API Integration Script Loaded');
console.log('📡 Using API Base:', API_BASE);
console.log('🔑 Auth Token:', getAuthToken() ? 'Present' : 'Missing');
