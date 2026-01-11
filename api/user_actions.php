<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

// Check authentication
if (!isLoggedIn()) {
    errorResponse('Authentication required', 401);
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'watch_later_add':
        $contentId = (int)($_POST['content_id'] ?? 0);
        
        if ($contentId <= 0) {
            errorResponse('Invalid content ID');
        }
        
        // Check if already exists
        $exists = $db->fetchOne(
            "SELECT id FROM watch_later WHERE user_id = ? AND content_id = ?",
            [$userId, $contentId]
        );
        
        if ($exists) {
            successResponse('Already in watch later list');
        }
        
        $id = $db->insert('watch_later', [
            'user_id' => $userId,
            'content_id' => $contentId
        ]);
        
        if ($id) {
            successResponse('Added to watch later');
        } else {
            errorResponse('Failed to add to watch later', 500);
        }
        break;
        
    case 'watch_later_remove':
        $contentId = (int)($_POST['content_id'] ?? 0);
        
        if ($contentId <= 0) {
            errorResponse('Invalid content ID');
        }
        
        $deleted = $db->delete(
            'watch_later',
            'user_id = ? AND content_id = ?',
            [$userId, $contentId]
        );
        
        if ($deleted) {
            successResponse('Removed from watch later');
        } else {
            errorResponse('Failed to remove from watch later', 500);
        }
        break;
        
    case 'watch_later_list':
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        $totalResult = $db->fetchOne(
            "SELECT COUNT(*) as total FROM watch_later WHERE user_id = ?",
            [$userId]
        );
        $total = $totalResult['total'];
        
        $items = $db->fetchAll(
            "SELECT c.*, wl.created_at as added_at 
             FROM watch_later wl
             JOIN content c ON wl.content_id = c.id
             WHERE wl.user_id = ? AND c.is_active = 1
             ORDER BY wl.created_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        
        foreach ($items as &$item) {
            $item['genres'] = json_decode($item['genres'] ?? '[]', true);
        }
        
        $pagination = getPaginationData($total, $page, $limit);
        
        successResponse('Watch later list retrieved', [
            'items' => $items,
            'pagination' => $pagination
        ]);
        break;
        
    case 'react':
        $contentId = (int)($_POST['content_id'] ?? 0);
        $reaction = $_POST['reaction'] ?? '';
        
        if ($contentId <= 0) {
            errorResponse('Invalid content ID');
        }
        
        if (!in_array($reaction, ['like', 'dislike'])) {
            errorResponse('Invalid reaction type');
        }
        
        // Check if already reacted
        $existing = $db->fetchOne(
            "SELECT id, reaction FROM user_reactions WHERE user_id = ? AND content_id = ?",
            [$userId, $contentId]
        );
        
        $db->beginTransaction();
        
        try {
            if ($existing) {
                $oldReaction = $existing['reaction'];
                
                if ($oldReaction === $reaction) {
                    // Remove reaction
                    $db->delete('user_reactions', 'id = ?', [$existing['id']]);
                    
                    // Decrement count
                    if ($reaction === 'like') {
                        $db->query("UPDATE content SET likes = likes - 1 WHERE id = ?", [$contentId]);
                    } else {
                        $db->query("UPDATE content SET dislikes = dislikes - 1 WHERE id = ?", [$contentId]);
                    }
                    
                    $db->commit();
                    successResponse('Reaction removed');
                } else {
                    // Change reaction
                    $db->update('user_reactions', ['reaction' => $reaction], 'id = ?', [$existing['id']]);
                    
                    // Update counts
                    if ($reaction === 'like') {
                        $db->query("UPDATE content SET likes = likes + 1, dislikes = dislikes - 1 WHERE id = ?", [$contentId]);
                    } else {
                        $db->query("UPDATE content SET dislikes = dislikes + 1, likes = likes - 1 WHERE id = ?", [$contentId]);
                    }
                    
                    $db->commit();
                    successResponse('Reaction updated');
                }
            } else {
                // Add new reaction
                $db->insert('user_reactions', [
                    'user_id' => $userId,
                    'content_id' => $contentId,
                    'reaction' => $reaction
                ]);
                
                // Increment count
                if ($reaction === 'like') {
                    $db->query("UPDATE content SET likes = likes + 1 WHERE id = ?", [$contentId]);
                } else {
                    $db->query("UPDATE content SET dislikes = dislikes + 1 WHERE id = ?", [$contentId]);
                }
                
                $db->commit();
                successResponse('Reaction added');
            }
        } catch (Exception $e) {
            $db->rollback();
            errorResponse('Failed to update reaction', 500);
        }
        break;
        
    case 'update_progress':
        $contentId = (int)($_POST['content_id'] ?? 0);
        $episodeId = !empty($_POST['episode_id']) ? (int)$_POST['episode_id'] : null;
        $progress = (int)($_POST['progress'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);
        
        if ($contentId <= 0) {
            errorResponse('Invalid content ID');
        }
        
        // Check if watch history exists
        $existing = $db->fetchOne(
            "SELECT id FROM watch_history 
             WHERE user_id = ? AND content_id = ? AND (episode_id = ? OR (episode_id IS NULL AND ? IS NULL))",
            [$userId, $contentId, $episodeId, $episodeId]
        );
        
        if ($existing) {
            $db->update(
                'watch_history',
                [
                    'progress' => $progress,
                    'duration' => $duration,
                    'last_watched' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$existing['id']]
            );
        } else {
            $db->insert('watch_history', [
                'user_id' => $userId,
                'content_id' => $contentId,
                'episode_id' => $episodeId,
                'progress' => $progress,
                'duration' => $duration
            ]);
        }
        
        successResponse('Progress updated');
        break;
        
    case 'get_progress':
        $contentId = (int)($_GET['content_id'] ?? 0);
        $episodeId = !empty($_GET['episode_id']) ? (int)$_GET['episode_id'] : null;
        
        if ($contentId <= 0) {
            errorResponse('Invalid content ID');
        }
        
        $progress = $db->fetchOne(
            "SELECT progress, duration, last_watched 
             FROM watch_history 
             WHERE user_id = ? AND content_id = ? AND (episode_id = ? OR (episode_id IS NULL AND ? IS NULL))
             ORDER BY last_watched DESC
             LIMIT 1",
            [$userId, $contentId, $episodeId, $episodeId]
        );
        
        if ($progress) {
            successResponse('Progress retrieved', $progress);
        } else {
            successResponse('No progress found', [
                'progress' => 0,
                'duration' => 0,
                'last_watched' => null
            ]);
        }
        break;
        
    case 'watch_history':
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        $totalResult = $db->fetchOne(
            "SELECT COUNT(DISTINCT content_id) as total FROM watch_history WHERE user_id = ?",
            [$userId]
        );
        $total = $totalResult['total'];
        
        $history = $db->fetchAll(
            "SELECT c.*, wh.progress, wh.duration, wh.last_watched 
             FROM watch_history wh
             JOIN content c ON wh.content_id = c.id
             WHERE wh.user_id = ? AND c.is_active = 1
             GROUP BY c.id
             ORDER BY wh.last_watched DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        
        foreach ($history as &$item) {
            $item['genres'] = json_decode($item['genres'] ?? '[]', true);
            $item['progress_percentage'] = $item['duration'] > 0 ? 
                round(($item['progress'] / $item['duration']) * 100, 2) : 0;
        }
        
        $pagination = getPaginationData($total, $page, $limit);
        
        successResponse('Watch history retrieved', [
            'history' => $history,
            'pagination' => $pagination
        ]);
        break;
        
    case 'check_reaction':
        $contentId = (int)($_GET['content_id'] ?? 0);
        
        if ($contentId <= 0) {
            errorResponse('Invalid content ID');
        }
        
        $reaction = $db->fetchOne(
            "SELECT reaction FROM user_reactions WHERE user_id = ? AND content_id = ?",
            [$userId, $contentId]
        );
        
        successResponse('Reaction status retrieved', [
            'has_reaction' => $reaction !== null,
            'reaction' => $reaction ? $reaction['reaction'] : null
        ]);
        break;
        
    default:
        errorResponse('Invalid action');
}
