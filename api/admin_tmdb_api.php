<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

// Ensure the user is an admin
requireAdmin();

header('Content-Type: application/json');

$db = Database::getInstance();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$action = $input['action'] ?? '';
$apiKey = $input['apiKey'] ?? '';

if (empty($apiKey)) {
    echo json_encode(['success' => false, 'message' => 'API Key is missing.']);
    exit;
}

if ($action === 'generate') {
    $type = $input['type'] ?? '';
    $tmdbId = (int)($input['id'] ?? 0);
    $servers = $input['servers'] ?? [];

    if (empty($tmdbId) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Missing TMDB ID or content type.']);
        exit;
    }

    // Check if content already exists
    $existing = $db->fetchOne("SELECT id FROM content WHERE tmdb_id = ? AND content_type = ?", [$tmdbId, $type]);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'This content already exists in the database.']);
        exit;
    }

    if ($type === 'movie') {
        $apiUrl = "https://api.themoviedb.org/3/movie/{$tmdbId}?api_key={$apiKey}&append_to_response=videos,credits";
        $data = @json_decode(file_get_contents($apiUrl), true);

        if (!$data || isset($data['success']) && $data['success'] === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch movie data from TMDB. Check the ID and your API key.']);
            exit;
        }

        $trailer = getTrailerUrl($data['videos']['results'] ?? []);
        $genres = array_column($data['genres'] ?? [], 'name');

        $contentId = $db->insert('content', [
            'tmdb_id' => $data['id'],
            'title' => $data['title'],
            'original_title' => $data['original_title'],
            'content_type' => 'movie',
            'description' => $data['overview'],
            'poster_url' => 'https://image.tmdb.org/t/p/w500' . $data['poster_path'],
            'backdrop_url' => 'https://image.tmdb.org/t/p/original' . $data['backdrop_path'],
            'trailer_url' => $trailer,
            'release_date' => $data['release_date'],
            'year' => substr($data['release_date'], 0, 4),
            'runtime' => $data['runtime'],
            'rating' => $data['vote_average'],
            'genres' => json_encode($genres),
            'is_active' => 1
        ]);

        if ($contentId) {
            foreach($servers as $server) {
                $db->insert('servers', [
                    'content_id' => $contentId,
                    'server_name' => $server['name'],
                    'server_url' => $server['url']
                ]);
            }
            logAdminAction($_SESSION['user_id'], 'add_content', 'content', $contentId, "Added movie: {$data['title']}");
            echo json_encode(['success' => true, 'message' => "Movie '{$data['title']}' added successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save movie to database.']);
        }

} elseif ($action === 'search') {
    $query = $input['query'] ?? '';
    $type = $input['type'] ?? 'multi';

    if (empty($query)) {
        echo json_encode(['success' => false, 'message' => 'Search query is missing.']);
        exit;
    }

    $apiUrl = "https://api.themoviedb.org/3/search/{$type}?api_key={$apiKey}&query=" . urlencode($query);
    $response = @file_get_contents($apiUrl);

    if ($response === FALSE) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch search results from TMDB.']);
        exit;
    }

    $data = json_decode($response, true);
    echo json_encode(['success' => true, 'results' => $data['results']]);
    exit;

    } elseif ($type === 'series') {
        $seasonsToInclude = !empty($input['seasons']) ? explode(',', $input['seasons']) : [];

        $apiUrl = "https://api.themoviedb.org/3/tv/{$tmdbId}?api_key={$apiKey}&append_to_response=videos,credits";
        $data = @json_decode(file_get_contents($apiUrl), true);

        if (!$data || isset($data['success']) && $data['success'] === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch series data from TMDB. Check the ID and your API key.']);
            exit;
        }

        $trailer = getTrailerUrl($data['videos']['results'] ?? []);
        $genres = array_column($data['genres'] ?? [], 'name');

        $contentId = $db->insert('content', [
            'tmdb_id' => $data['id'],
            'title' => $data['name'],
            'original_title' => $data['original_name'],
            'content_type' => 'series',
            'description' => $data['overview'],
            'poster_url' => 'https://image.tmdb.org/t/p/w500' . $data['poster_path'],
            'backdrop_url' => 'https://image.tmdb.org/t/p/original' . $data['backdrop_path'],
            'trailer_url' => $trailer,
            'release_date' => $data['first_air_date'],
            'year' => substr($data['first_air_date'], 0, 4),
            'rating' => $data['vote_average'],
            'genres' => json_encode($genres),
            'is_active' => 1
        ]);

        if (!$contentId) {
            echo json_encode(['success' => false, 'message' => 'Failed to save series to database.']);
            exit;
        }

        $seasonsInSeries = $data['seasons'];
        foreach ($seasonsInSeries as $season) {
            if ($season['season_number'] == 0) continue; // Skip "Specials" season
            if (!empty($seasonsToInclude) && !in_array($season['season_number'], $seasonsToInclude)) continue;

            $seasonApiUrl = "https://api.themoviedb.org/3/tv/{$tmdbId}/season/{$season['season_number']}?api_key={$apiKey}";
            $seasonData = @json_decode(file_get_contents($seasonApiUrl), true);

            if (!$seasonData) continue;

            $seasonId = $db->insert('seasons', [
                'content_id' => $contentId,
                'season_number' => $seasonData['season_number'],
                'name' => $seasonData['name'],
                'poster_url' => 'https://image.tmdb.org/t/p/w500' . $seasonData['poster_path']
            ]);

            if ($seasonId) {
                foreach ($seasonData['episodes'] as $episode) {
                    $episodeId = $db->insert('episodes', [
                        'season_id' => $seasonId,
                        'episode_number' => $episode['episode_number'],
                        'title' => $episode['name'],
                        'description' => $episode['overview'],
                        'thumbnail_url' => 'https://image.tmdb.org/t/p/w500' . $episode['still_path'],
                        'release_date' => $episode['air_date'],
                        'runtime' => $episode['runtime'] ?? 0,
                    ]);

                    if($episodeId) {
                        foreach($servers as $server) {
                            $serverUrl = str_replace(
                                ['{season}', '{episode}'],
                                [$season['season_number'], $episode['episode_number']],
                                $server['url']
                            );
                            $db->insert('servers', [
                                'episode_id' => $episodeId,
                                'server_name' => $server['name'],
                                'server_url' => $serverUrl
                            ]);
                        }
                    }
                }
            }
        }
        logAdminAction($_SESSION['user_id'], 'add_content', 'content', $contentId, "Added series: {$data['name']}");
        echo json_encode(['success' => true, 'message' => "Series '{$data['name']}' and its seasons/episodes added successfully!"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid content type specified.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

function getTrailerUrl($videos) {
    foreach ($videos as $video) {
        if (strtolower($video['type']) === 'trailer' && strtolower($video['site']) === 'youtube') {
            return 'https://www.youtube.com/watch?v=' . $video['key'];
        }
    }
    return '';
}
