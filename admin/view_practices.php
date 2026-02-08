<?php
/**
 * CodeDojo - View User Practices
 * Admin page to view all user practice submissions
 */

require_once 'auth_check.php';
require_once '../config/database.php';

$pageTitle = 'User Practices';
$pdo = getDBConnection();

// Get all user practices with task info
$sql = "SELECT up.*, pt.title as task_title, l.title as lesson_title 
        FROM user_practice up 
        LEFT JOIN practice_tasks pt ON up.task_id = pt.id 
        LEFT JOIN lessons l ON pt.lesson_id = l.id 
        ORDER BY up.created_at DESC";
$practices = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CodeDojo Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
</head>
<body>
    <div class="app-container">
        <!-- Admin Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <span class="material-icons">admin_panel_settings</span>
                    <span>Admin Panel</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-item">
                        <span class="material-icons">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="manage_lessons.php" class="nav-item">
                        <span class="material-icons">book</span>
                        <span>Manage Lessons</span>
                    </a>
                    <a href="manage_tasks.php" class="nav-item">
                        <span class="material-icons">assignment</span>
                        <span>Manage Tasks</span>
                    </a>
                    <a href="view_practices.php" class="nav-item active">
                        <span class="material-icons">folder</span>
                        <span>User Practices</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Site</div>
                    <a href="../index.php" class="nav-item" target="_blank">
                        <span class="material-icons">public</span>
                        <span>View Site</span>
                    </a>
                    <a href="logout.php" class="nav-item">
                        <span class="material-icons">logout</span>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>User Practices</h1>
                </div>
            </header>
            
            <div style="padding: var(--spacing-xl); max-width: 1400px;">
                <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
                    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: var(--text-primary);">
                        All User Submissions (<?php echo count($practices); ?>)
                    </h2>
                    
                    <?php if (empty($practices)): ?>
                        <p style="color: var(--text-secondary); text-align: center; padding: var(--spacing-xl);">
                            No user practices yet. Students will appear here once they start saving their work.
                        </p>
                    <?php else: ?>
                        <div style="display: grid; gap: var(--spacing-md);">
                            <?php foreach ($practices as $practice): ?>
                                <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 8px; padding: var(--spacing-md);">
                                    <div style="display: flex; justify-content: space-between; align-items: start; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                                        <div style="flex: 1;">
                                            <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-xs);">
                                                <?php echo htmlspecialchars($practice['title']); ?>
                                            </h3>
                                            <div style="display: flex; gap: var(--spacing-md); font-size: 13px; color: var(--text-secondary);">
                                                <?php if ($practice['task_title']): ?>
                                                    <span>📝 Task: <?php echo htmlspecialchars($practice['task_title']); ?></span>
                                                <?php endif; ?>
                                                <?php if ($practice['lesson_title']): ?>
                                                    <span>📚 Lesson: <?php echo htmlspecialchars($practice['lesson_title']); ?></span>
                                                <?php endif; ?>
                                                <span>📅 <?php echo date('M j, Y g:i A', strtotime($practice['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <button onclick="viewPractice(<?php echo $practice['id']; ?>)" class="btn btn-primary btn-small">
                                            <span class="material-icons">visibility</span>
                                            View
                                        </button>
                                    </div>
                                    
                                    <div style="background: var(--bg-code); color: var(--text-code); padding: var(--spacing-md); border-radius: 6px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 150px; overflow-y: auto;">
                                        <?php echo htmlspecialchars(substr($practice['html_code'], 0, 300)); ?><?php echo strlen($practice['html_code']) > 300 ? '...' : ''; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/theme.js"></script>
    <script>
        function viewPractice(id) {
            // Open practice in new window
            window.open('../editor.php?practice=' + id, '_blank');
        }
    </script>
</body>
</html>
