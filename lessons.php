<?php
/**
 * CodeDojo - Lessons Page
 * Browse and select practice tasks
 */

require_once 'config/database.php';
require_once 'config/progress.php';

$pageTitle = 'Lessons & Practice Tasks';
$currentPage = 'lessons';

$lessons = [];
$taskProgressMap = [];
$lessonBadgeMap = [];

try {
    $pdo = getDBConnection();
    ensureProgressSchema($pdo);

    $sql = "SELECT * FROM lessons ORDER BY order_num ASC";
    $stmt = $pdo->query($sql);
    $lessons = $stmt->fetchAll();

    foreach ($lessons as &$lesson) {
        $sql = "SELECT * FROM practice_tasks WHERE lesson_id = :lesson_id ORDER BY order_num ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':lesson_id' => $lesson['id']]);
        $lesson['tasks'] = $stmt->fetchAll();
    }
    unset($lesson);

    $userId = getActiveUserId();
    if ($userId !== null) {
        $snapshot = getUserProgressSnapshot($pdo, $userId);
        $taskProgressMap = $snapshot['task_progress'];
        foreach ($snapshot['badges'] as $badge) {
            $lessonBadgeMap[(int) $badge['lesson_id']] = true;
        }
    }
} catch (Throwable $e) {
    error_log("Error loading lessons: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div style="padding: var(--spacing-xl); max-width: 1400px; margin: 0 auto;">
    <div style="margin-bottom: var(--spacing-xl);">
        <h1 style="font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: var(--spacing-sm);">
            &#x1F4DA; Lessons & Practice Tasks
        </h1>
        <p style="font-size: 16px; color: var(--text-secondary);">
            Choose a task below to start practicing. Each task builds on previous concepts.
        </p>
    </div>

    <?php if (empty($lessons)): ?>
        <div class="empty-state">
            <span class="material-icons">school</span>
            <h3>No lessons available yet</h3>
            <p>Check back soon for new learning content!</p>
        </div>
    <?php else: ?>
        <?php foreach ($lessons as $lesson): ?>
            <div style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--border-color);">
                    <div>
                        <h2 style="font-size: 24px; font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-xs);">
                            <?php echo htmlspecialchars($lesson['title']); ?>
                        </h2>
                        <p style="font-size: 15px; color: var(--text-secondary); margin: 0;">
                            <?php echo htmlspecialchars($lesson['description']); ?>
                        </p>
                    </div>
                    <span class="task-difficulty <?php echo htmlspecialchars($lesson['difficulty']); ?>">
                        <?php echo htmlspecialchars($lesson['difficulty']); ?>
                    </span>
                </div>

                <?php if (!empty($lessonBadgeMap[(int) $lesson['id']])): ?>
                    <div style="margin-bottom: var(--spacing-md); color: #166534; background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600;">
                        Mastery Badge Earned
                    </div>
                <?php endif; ?>

                <?php if (empty($lesson['tasks'])): ?>
                    <p style="color: var(--text-muted); font-style: italic; padding: var(--spacing-md);">
                        No tasks available for this lesson yet.
                    </p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-md);">
                        <?php foreach ($lesson['tasks'] as $task): ?>
                            <?php $progress = $taskProgressMap[(int) $task['id']] ?? null; ?>
                            <a href="editor.php?task=<?php echo $task['id']; ?>"
                               style="text-decoration: none; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: var(--spacing-md); transition: all var(--transition-normal); display: block;">
                                <div style="display: flex; align-items: start; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    <span class="material-icons" style="color: var(--color-primary); font-size: 20px;">assignment</span>
                                    <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin: 0; flex: 1;">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h3>
                                </div>

                                <?php if (!empty($progress)): ?>
                                    <div style="display: flex; gap: var(--spacing-xs); flex-wrap: wrap; margin-bottom: var(--spacing-sm);">
                                        <span style="font-size: 11px; padding: 3px 8px; border-radius: 999px; background: <?php echo !empty($progress['is_completed']) ? '#dcfce7' : '#e2e8f0'; ?>; color: <?php echo !empty($progress['is_completed']) ? '#166534' : '#334155'; ?>;">
                                            <?php echo !empty($progress['is_completed']) ? 'Completed' : 'In Progress'; ?>
                                        </span>
                                        <span style="font-size: 11px; padding: 3px 8px; border-radius: 999px; background: #e0e7ff; color: #3730a3;">
                                            Best score: <?php echo (int) $progress['best_score']; ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                                    <?php
                                    $instruction = htmlspecialchars($task['instruction']);
                                    echo strlen($instruction) > 100 ? substr($instruction, 0, 100) . '...' : $instruction;
                                    ?>
                                </p>

                                <div style="margin-top: var(--spacing-sm); display: flex; align-items: center; gap: var(--spacing-xs); color: var(--color-primary); font-size: 14px; font-weight: 500;">
                                    <span>Start Task</span>
                                    <span class="material-icons" style="font-size: 16px;">arrow_forward</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); color: white; margin-top: var(--spacing-xl);">
        <div style="display: flex; align-items: start; gap: var(--spacing-md);">
            <span class="material-icons" style="font-size: 32px;">tips_and_updates</span>
            <div>
                <h3 style="font-size: 20px; font-weight: 600; margin: 0 0 var(--spacing-sm) 0;">Pro Tips for Learning</h3>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Start with easier tasks and work your way up</li>
                    <li>Try to solve tasks without looking at hints first</li>
                    <li>Experiment! Change the code and see what happens</li>
                    <li>Save your favorite solutions for future reference</li>
                    <li>Don't rush - understanding is more important than speed</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
a[href*="editor.php?task"] { cursor: pointer; }
a[href*="editor.php?task"]:hover {
    background: var(--bg-secondary) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--color-primary) !important;
}
</style>

<?php include 'includes/footer.php'; ?>
