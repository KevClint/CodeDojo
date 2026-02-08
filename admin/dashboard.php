<?php
/**
 * CodeDojo - Admin Dashboard
 * Main admin panel with statistics and quick actions
 */

require_once 'auth_check.php';
require_once '../config/database.php';

$pageTitle = 'Admin Dashboard';

// Get statistics
try {
    $pdo = getDBConnection();
    
    $totalLessons = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
    $totalTasks = $pdo->query("SELECT COUNT(*) FROM practice_tasks")->fetchColumn();
    $totalPractices = $pdo->query("SELECT COUNT(*) FROM user_practice")->fetchColumn();
    $recentPractices = $pdo->query("SELECT COUNT(*) FROM user_practice WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
} catch (Exception $e) {
    $totalLessons = 0;
    $totalTasks = 0;
    $totalPractices = 0;
    $recentPractices = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CodeDojo</title>
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
                    <a href="dashboard.php" class="nav-item active">
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
                    <a href="view_practices.php" class="nav-item">
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
                    <h1>⚙️ Admin Control Panel</h1>
                </div>
                <div class="header-right">
                    <span style="color: var(--text-secondary); font-size: 14px;">
                        Administrator
                    </span>
                </div>
            </header>
            
            <div style="padding: var(--spacing-xl); max-width: 1400px;">
                <!-- Statistics Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
                    <!-- Total Lessons -->
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); color: white;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md);">
                            <span class="material-icons" style="font-size: 40px; opacity: 0.9;">book</span>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">TOTAL</span>
                        </div>
                        <div style="font-size: 36px; font-weight: 700; margin-bottom: var(--spacing-xs);"><?php echo $totalLessons; ?></div>
                        <div style="font-size: 14px; opacity: 0.9;">Lessons</div>
                    </div>
                    
                    <!-- Total Tasks -->
                    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); color: white;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md);">
                            <span class="material-icons" style="font-size: 40px; opacity: 0.9;">assignment</span>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">TOTAL</span>
                        </div>
                        <div style="font-size: 36px; font-weight: 700; margin-bottom: var(--spacing-xs);"><?php echo $totalTasks; ?></div>
                        <div style="font-size: 14px; opacity: 0.9;">Practice Tasks</div>
                    </div>
                    
                    <!-- Total Practices -->
                    <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); color: white;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md);">
                            <span class="material-icons" style="font-size: 40px; opacity: 0.9;">folder</span>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">ALL TIME</span>
                        </div>
                        <div style="font-size: 36px; font-weight: 700; margin-bottom: var(--spacing-xs);"><?php echo $totalPractices; ?></div>
                        <div style="font-size: 14px; opacity: 0.9;">User Practices</div>
                    </div>
                    
                    <!-- Today's Practices -->
                    <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); color: white;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md);">
                            <span class="material-icons" style="font-size: 40px; opacity: 0.9;">today</span>
                            <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">TODAY</span>
                        </div>
                        <div style="font-size: 36px; font-weight: 700; margin-bottom: var(--spacing-xs);"><?php echo $recentPractices; ?></div>
                        <div style="font-size: 14px; opacity: 0.9;">New Practices</div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 2px solid #667eea; margin-bottom: var(--spacing-xl);">
                    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: #667eea; display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons">admin_panel_settings</span>
                        Manage Platform Content
                    </h2>
                    <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md); font-size: 14px;">
                        Use these tools to create lessons, manage practice tasks, and review user submissions.
                    </p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md);">
                        <a href="manage_lessons.php" class="btn btn-primary" style="justify-content: center; padding: 16px; background: #667eea;">
                            <span class="material-icons">book</span>
                            Manage Lessons
                        </a>
                        <a href="manage_tasks.php" class="btn btn-primary" style="justify-content: center; padding: 16px; background: #764ba2;">
                            <span class="material-icons">assignment</span>
                            Manage Tasks
                        </a>
                        <a href="view_practices.php" class="btn btn-primary" style="justify-content: center; padding: 16px; background: #4facfe;">
                            <span class="material-icons">visibility</span>
                            View User Practices
                        </a>
                    </div>
                </div>
                
                <!-- Tips for Admins -->
                <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
                    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: var(--text-primary); display: flex; align-items: center; gap: var(--spacing-sm);">
                        <span class="material-icons" style="color: var(--color-info);">info</span>
                        Admin Help & Information
                    </h2>
                    <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md); font-size: 14px;">
                        <strong>This is the Administrator Control Panel.</strong> Here you can manage lessons, tasks, and review user submissions. This is NOT the same as the User Dashboard which shows user-specific practice progress.
                    </p>
                    <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 20px; margin-bottom: var(--spacing-md);">
                        <li><strong>Manage Lessons:</strong> Create, edit, or delete learning modules</li>
                        <li><strong>Manage Tasks:</strong> Create or modify practice tasks for lessons</li>
                        <li><strong>View Practices:</strong> Review and monitor all user practice submissions</li>
                        <li><strong>Statistics:</strong> See overview of all lessons, tasks, and user submissions</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/theme.js"></script>
</body>
</html>
