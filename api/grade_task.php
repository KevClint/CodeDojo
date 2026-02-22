<?php
/**
 * Grade Task API
 * Returns pass/fail details for a task submission and updates progress for logged-in users.
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/progress.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    $taskId = intval($_POST['task_id'] ?? 0);
    $htmlCode = (string) ($_POST['html_code'] ?? '');

    if ($taskId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Task ID is required'
        ]);
        exit;
    }

    $pdo = getDBConnection();
    ensureProgressSchema($pdo);

    $stmt = $pdo->prepare("
        SELECT id, lesson_id, title, instruction, grading_rules
        FROM practice_tasks
        WHERE id = :id
    ");
    $stmt->execute([':id' => $taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        echo json_encode([
            'success' => false,
            'message' => 'Task not found'
        ]);
        exit;
    }

    $rules = buildTaskRules($task);
    $grading = gradeTaskSubmission($htmlCode, $rules);

    $newBadges = [];
    $streak = 0;
    $userId = getActiveUserId();

    if ($userId !== null) {
        recordUserTaskAttempt($pdo, $userId, $taskId, $grading['passed'], $grading['score']);
        $newBadges = awardLessonBadges($pdo, $userId);
        $streak = calculateUserStreak($pdo, $userId);
    }

    echo json_encode([
        'success' => true,
        'passed' => $grading['passed'],
        'score' => $grading['score'],
        'checks' => $grading['checks'],
        'streak_days' => $streak,
        'new_badges' => $newBadges
    ]);
} catch (Throwable $e) {
    error_log("Grade Task Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Unable to grade this task right now'
    ]);
}

