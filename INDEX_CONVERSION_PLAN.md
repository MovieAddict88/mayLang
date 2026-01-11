# Index.html to Index.php Full Conversion Plan

## Current Status
- ❌ Only basic auth modal implemented
- ❌ Missing ALL streaming features
- ❌ Missing player integration
- ❌ Missing content display
- ❌ Missing filters and search

## Full Feature List from index.html (8499 lines)

### 1. Header & Navigation
- [x] Logo
- [x] Search bar with live search
- [ ] Search results dropdown
- [x] User profile menu
- [ ] Theme toggle
- [ ] Mobile menu

### 2. Hero Carousel
- [ ] Featured content slider
- [ ] Auto-play carousel
- [ ] Navigation controls
- [ ] Indicators
- [ ] Play/Watch buttons

### 3. Content Browsing
- [ ] Grid view
- [ ] List view
- [ ] View toggle buttons
- [ ] Content cards with:
  - Poster images
  - Title
  - Rating stars
  - Year, country, duration
  - Type badges (Movie/Series/Live)
  - Watch later button
  - Quick play button

### 4. Filters & Sorting
- [ ] Content type filter (All/Movies/Series/Live)
- [ ] Genre filter (dropdown with all genres)
- [ ] Country filter with autocomplete
- [ ] Year filter
- [ ] Sort by (Title, Year, Rating, Views)
- [ ] Sort order (ASC/DESC)

### 5. Video Player Modal
- [ ] Plyr player integration
- [ ] Shaka Player for adaptive streaming
- [ ] Server selector dropdown
- [ ] Episode selector (for series)
- [ ] Season selector (for series)
- [ ] Video info display
- [ ] Close button
- [ ] Fullscreen support
- [ ] Quality selection
- [ ] Speed control

### 6. User Interactions
- [ ] Watch later add/remove
- [ ] Like/dislike buttons
- [ ] Progress tracking (continue watching)
- [ ] Share buttons
- [ ] Report content

### 7. Pagination
- [ ] Load more button
- [ ] Lazy loading
- [ ] Infinite scroll option
- [ ] Page indicators

### 8. Watch Later Page
- [ ] Separate page for watch later
- [ ] Remove from watch later
- [ ] Empty state message

### 9. IndexedDB Caching
- [ ] Cache content locally
- [ ] Offline support
- [ ] Auto-refresh cache
- [ ] Cache management

### 10. Notifications
- [ ] Toast notifications
- [ ] Success/error messages
- [ ] Loading indicators

## API Integration Points

### Replace JSON with API Calls

**Old (JSON):**
```javascript
fetch('playlists/playlist.json')
```

**New (API):**
```javascript
fetch('/api/content.php?action=list&limit=20&page=1')
```

### API Endpoints Needed
1. `GET /api/content.php?action=list` - Get content with filters
2. `GET /api/content.php?action=detail&id=X` - Get content details
3. `GET /api/content.php?action=featured` - Get featured content
4. `GET /api/content.php?action=search&q=query` - Search
5. `POST /api/user_actions.php?action=watch_later_add` - Add to watch later
6. `POST /api/user_actions.php?action=react` - Like/dislike
7. `POST /api/user_actions.php?action=update_progress` - Save progress

## JavaScript Functions to Implement

1. `loadContent()` - Load content from API
2. `applyFilters()` - Apply filters and reload
3. `searchContent()` - Search functionality
4. `openPlayer()` - Open video player
5. `addToWatchLater()` - Add to watch later
6. `likeContent()` - Like/dislike
7. `saveProgress()` - Save watch progress
8. `loadMore()` - Pagination
9. `initCarousel()` - Initialize carousel
10. `cacheContent()` - IndexedDB caching

## Implementation Steps

### Step 1: Copy All CSS from index.html ✅
- All styles already present in index.html
- Need to copy to index.php

### Step 2: Copy All HTML Structure ✅
- Header
- Carousel
- Filters
- Content grid/list
- Player modal
- Footer

### Step 3: Integrate Authentication ✅
- Auth modal (already done)
- Check if user is logged in
- Force login before showing content
- One-time login with tokens

### Step 4: Replace JavaScript Functions
- Remove JSON fetch
- Add API fetch
- Add authentication headers
- Handle token refresh

### Step 5: Add IndexedDB
- Cache content locally
- Offline support
- Background sync

### Step 6: Testing
- Test all features
- Test responsive design
- Test authentication flow
- Test API integration

## File Size Consideration
- Original index.html: 8499 lines
- New index.php: ~9000-10000 lines
- Includes PHP auth + all HTML/CSS/JS

## Quick Implementation (Now!)

Since this is a LOT of code, I'll create the COMPLETE index.php with:
1. ALL HTML/CSS from index.html
2. PHP authentication at top
3. Modified JavaScript to use API
4. All original features maintained

**ETA: Creating NOW!**
