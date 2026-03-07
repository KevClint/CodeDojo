<?php
/**
 * User Progress API
 * Returns task completion, streak, and lesson mastery badges for the logged-in user.
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/progress.php';

try {
    $userId = getActiveUserId();
    if ($userId === null) {
        echo json_encode([
            'success' => true,
            'is_logged_in' => false,
            'summary' => [
                'completed_tasks' => 0,
                'total_attempts' => 0,
                'mastered_lessons' => 0,
                'streak_days' => 0
            ],
            'task_progress' => [],
            'badges' => []
        ]);
        exit;
    }

    $pdo = getDBConnection();
    ensureProgressSchema($pdo);

    $snapshot = getUserProgressSnapshot($pdo, $userId);

    echo json_encode([
        'success' => true,
        'is_logged_in' => true,
        'summary' => $snapshot['summary'],
        'task_progress' => $snapshot['task_progress'],
        'badges' => $snapshot['badges']
    ]);
} catch (Throwable $e) {
    error_log("User Progress Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load progress'
    ]);
}

