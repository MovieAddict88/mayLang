# API Integration Guide for Index.php

## ✅ COMPLETED

### index.php Status
- **Created**: Full 8537-line index.php
- **PHP Auth**: Added at top (lines 1-39)
- **HTML/CSS**: Complete from index.html (lines 40-8537)
- **All Features**: Maintained from original

## 🔧 JavaScript Modifications Needed

Since index.php is 8500+ lines, modifying inline is not practical. Instead, I'll create:

### 1. External API Integration Script

Create `/js/api-integration.js`:

```javascript
// API Configuration
const API_BASE = '/api';

// Get auth token from cookie
function getAuthToken() {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; login_token=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// API fetch wrapper with authentication
async function apiFetch(endpoint, options = {}) {
    const token = getAuthToken();
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            ...(token && { 'Authorization': `Bearer ${token}` })
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
        
        if (!data.success && (data.message === 'Unauthorized' || data.message === 'Invalid token')) {
            // Token expired, reload to show login
            window.location.reload();
            return { success: false, error: 'Unauthorized' };
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: error.message };
    }
}

// Override the fetchData function to use API instead of JSON
window.fetchDataFromAPI = async function() {
    try {
        console.log('📡 Fetching content from API...');
        const response = await apiFetch('/content.php?action=list&limit=1000');
        
        if (response.success && response.data) {
            // Transform API data to match expected format
            const transformedData = response.data.map(item => ({
                Title: item.title,
                Description: item.description,
                PosterURL: item.poster_url,
                BackdropURL: item.backdrop_url || item.poster_url,
                TrailerURL: item.trailer_url,
                Type: item.content_type,
                Year: item.year,
                Runtime: item.runtime,
                Rating: item.rating,
                IMDbRating: item.imdb_rating || item.rating,
                Genres: JSON.parse(item.genres || '[]'),
                Countries: JSON.parse(item.countries || '[]'),
                Languages: JSON.parse(item.languages || '[]'),
                AgeRating: item.age_rating,
                Status: item.status,
                StreamingSources: item.streaming_sources || []
            }));
            
            console.log(`✅ Loaded ${transformedData.length} items from API`);
            
            // Store globally (for compatibility with existing code)
            window.cineData = transformedData;
            
            return transformedData;
        } else {
            console.error('❌ API returned error:', response);
            return null;
        }
    } catch (error) {
        console.error('❌ Failed to fetch from API:', error);
        return null;
    }
};

// Watch Later API Integration
window.apiWatchLaterToggle = async function(contentId, contentData) {
    try {
        const response = await apiFetch('/user_actions.php?action=watch_later_toggle', {
            method: 'POST',
            body: JSON.stringify({ content_id: contentId })
        });
        
        if (response.success) {
            return response.in_watch_later;
        }
        return null;
    } catch (error) {
        console.error('Watch Later API Error:', error);
        return null;
    }
};

// Like/Dislike API Integration
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
                likes: response.likes || 0,
                dislikes: response.dislikes || 0,
                user_reaction: response.user_reaction
            };
        }
        return null;
    } catch (error) {
        console.error('React API Error:', error);
        return null;
    }
};

// Progress Tracking API Integration
window.apiSaveProgress = async function(contentId, progressSeconds) {
    try {
        const response = await apiFetch('/user_actions.php?action=update_progress', {
            method: 'POST',
            body: JSON.stringify({
                content_id: contentId,
                progress_seconds: progressSeconds
            })
        });
        
        return response.success;
    } catch (error) {
        console.error('Progress API Error:', error);
        return false;
    }
};

// Search API Integration
window.apiSearch = async function(query) {
    try {
        const response = await apiFetch(`/content.php?action=search&q=${encodeURIComponent(query)}`);
        
        if (response.success && response.data) {
            return response.data.map(item => ({
                Title: item.title,
                Description: item.description,
                PosterURL: item.poster_url,
                Type: item.content_type,
                Year: item.year,
                Rating: item.rating
            }));
        }
        return [];
    } catch (error) {
        console.error('Search API Error:', error);
        return [];
    }
};

console.log('✅ API Integration loaded');
```

### 2. Modify index.php to include the script

Add before closing `</body>` tag:

```html
<script src="/js/api-integration.js"></script>
<script>
// Override fetchData to use API
const originalFetchData = fetchData;
fetchData = async function() {
    const apiData = await fetchDataFromAPI();
    if (apiData && apiData.length > 0) {
        cineData = apiData;
        return;
    }
    // Fallback to original method
    await originalFetchData();
};
</script>
```

## 📝 Implementation Steps

1. ✅ Created full index.php (8537 lines)
2. ⏳ Create `/js/api-integration.js`
3. ⏳ Add script tag to index.php
4. ⏳ Test authentication flow
5. ⏳ Test API integration
6. ⏳ Test all features

## 🎯 Quick Summary

**What's Done:**
- Full index.php with PHP auth at top
- All HTML/CSS from index.html preserved
- Original JavaScript preserved

**What's Needed:**
- External API integration script
- Override fetch functions
- Test everything

**Files:**
- `/home/engine/project/index.php` - ✅ Created (8537 lines)
- `/home/engine/project/js/api-integration.js` - ⏳ Need to create
- Original `index.html` - Still available for reference

## 🚀 Next Step

Create the `/js/api-integration.js` file and link it in index.php!
