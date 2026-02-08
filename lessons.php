<?php
/**
 * CodeDojo - Lessons Page
 * Browse and select practice tasks
 */

require_once 'config/database.php';

$pageTitle = 'Lessons & Practice Tasks';
$currentPage = 'lessons';

// Fetch all lessons with their tasks
$lessons = [];
try {
    $pdo = getDBConnection();
    
    // Get all lessons
    $sql = "SELECT * FROM lessons ORDER BY order_num ASC";
    $stmt = $pdo->query($sql);
    $lessons = $stmt->fetchAll();
    
    // Get tasks for each lesson
    foreach ($lessons as &$lesson) {
        $sql = "SELECT * FROM practice_tasks WHERE lesson_id = :lesson_id ORDER BY order_num ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':lesson_id' => $lesson['id']]);
        $lesson['tasks'] = $stmt->fetchAll();
    }
    unset($lesson); // Clear the reference to avoid issues with later loops
    
} catch (Exception $e) {
    error_log("Error loading lessons: " . $e->getMessage());
}

include 'includes/header.php';
?>

<div style="padding: var(--spacing-xl); max-width: 1400px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="margin-bottom: var(--spacing-xl);">
        <h1 style="font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: var(--spacing-sm);">
            📚 Lessons & Practice Tasks
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
        <!-- Lessons List -->
        <?php foreach ($lessons as $lesson): ?>
            <div style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
                <!-- Lesson Header -->
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
                
                <!-- Tasks Grid -->
                <?php if (empty($lesson['tasks'])): ?>
                    <p style="color: var(--text-muted); font-style: italic; padding: var(--spacing-md);">
                        No tasks available for this lesson yet.
                    </p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--spacing-md);">
                        <?php foreach ($lesson['tasks'] as $task): ?>
                            <a href="editor.php?task=<?php echo $task['id']; ?>" 
                               style="text-decoration: none; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: var(--spacing-md); transition: all var(--transition-normal); display: block;">
                                <div style="display: flex; align-items: start; gap: var(--spacing-sm); margin-bottom: var(--spacing-sm);">
                                    <span class="material-icons" style="color: var(--color-primary); font-size: 20px;">
                                        assignment
                                    </span>
                                    <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin: 0; flex: 1;">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h3>
                                </div>
                                
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
    
    <!-- Tips Section -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); color: white; margin-top: var(--spacing-xl);">
        <div style="display: flex; align-items: start; gap: var(--spacing-md);">
            <span class="material-icons" style="font-size: 32px;">tips_and_updates</span>
            <div>
                <h3 style="font-size: 20px; font-weight: 600; margin: 0 0 var(--spacing-sm) 0;">
                    Pro Tips for Learning
                </h3>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>Start with easier tasks and work your way up</li>
                    <li>Try to solve tasks without looking at hints first</li>
                    <li>Experiment! Change the code and see what happens</li>
                    <li>Save your favorite solutions for future reference</li>
                    <li>Don't rush – understanding is more important than speed</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Hover effect for task cards */
a[href*="editor.php?task"] {
    cursor: pointer;
}

a[href*="editor.php?task"]:hover {
    background: var(--bg-secondary) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--color-primary) !important;
}
</style>

<?php include 'includes/footer.php'; ?>
