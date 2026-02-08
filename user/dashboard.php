<?php
/**
 * CodeDojo - User Dashboard
 * Shows user's progress and practice sessions
 */

require_once 'auth_check.php';

// Get user's recent practices
try {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT id, title, is_completed, updated_at 
        FROM user_practice 
        ORDER BY updated_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $userPractices = $stmt->fetchAll();
    
    // Get statistics
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM user_practice");
    $stmt->execute();
    $totalResult = $stmt->fetch();
    $totalPractices = $totalResult['total'] ?? 0;
    
    $stmt = $db->prepare("SELECT COUNT(*) as completed FROM user_practice WHERE is_completed = TRUE");
    $stmt->execute();
    $completedResult = $stmt->fetch();
    $completedPractices = $completedResult['completed'] ?? 0;
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $userPractices = [];
    $totalPractices = 0;
    $completedPractices = 0;
}

$userName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - CodeDojo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        
        .navbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 18px;
            color: #1e293b;
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-link {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #667eea;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        
        .user-role {
            font-size: 12px;
            color: #64748b;
        }
        
        .logout-btn {
            padding: 8px 16px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #ee5a52;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        
        .welcome-section {
            margin-bottom: 40px;
        }
        
        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .welcome-text {
            color: #64748b;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 14px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .practices-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        
        .practices-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .practice-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .practice-item {
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }
        
        .practice-item:hover {
            background: #f8fafc;
        }
        
        .practice-item:last-child {
            border-bottom: none;
        }
        
        .practice-info {
            flex: 1;
        }
        
        .practice-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .practice-date {
            font-size: 12px;
            color: #94a3b8;
        }
        
        .practice-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 16px;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-draft {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .practice-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: #94a3b8;
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-text {
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .empty-subtext {
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 24px;
        }
        
        .action-links {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        
        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 20px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .action-link:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <span style="font-size: 24px;">🥋</span>
            CodeDojo
        </div>
        <div class="navbar-menu">
            <a href="../lessons.php" class="nav-link">
                <span class="material-icons">school</span> Lessons
            </a>
            <a href="../my_practice.php" class="nav-link">
                <span class="material-icons">code</span> Practice
            </a>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role">User</div>
                </div>
                <a href="logout.php">
                    <button class="logout-btn">
                        <span class="material-icons">logout</span>
                        Logout
                    </button>
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p class="welcome-text">Track your progress and continue learning with CodeDojo</p>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-value"><?php echo $totalPractices; ?></div>
                <div class="stat-label">Total Practices</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $completedPractices; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?php echo $totalPractices > 0 ? round(($completedPractices / $totalPractices) * 100) : 0; ?>%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
        </div>
        
        <!-- Recent Practices -->
        <div>
            <h2 class="section-title">
                <span class="material-icons">history</span>
                Recent Practices
            </h2>
            
            <div class="practices-section">
                <?php if (empty($userPractices)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <div class="empty-text">No practices yet</div>
                        <div class="empty-subtext">Start your learning journey by creating your first practice</div>
                        <div class="action-links">
                            <a href="../lessons.php" class="action-link">
                                <span class="material-icons">school</span>
                                View Lessons
                            </a>
                            <a href="../my_practice.php" class="action-link">
                                <span class="material-icons">add_circle</span>
                                Create Practice
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="practice-list">
                        <?php foreach ($userPractices as $practice): ?>
                            <div class="practice-item">
                                <div class="practice-info">
                                    <div class="practice-title"><?php echo htmlspecialchars($practice['title']); ?></div>
                                    <div class="practice-date">
                                        Updated: <?php echo date('M d, Y', strtotime($practice['updated_at'])); ?>
                                    </div>
                                </div>
                                <span class="practice-status <?php echo $practice['is_completed'] ? 'status-completed' : 'status-draft'; ?>">
                                    <span class="material-icons" style="font-size: 14px;">
                                        <?php echo $practice['is_completed'] ? 'check_circle' : 'edit'; ?>
                                    </span>
                                    <?php echo $practice['is_completed'] ? 'Completed' : 'Draft'; ?>
                                </span>
                                <div class="practice-actions">
                                    <a href="../editor.php?id=<?php echo $practice['id']; ?>" class="btn btn-primary btn-small">
                                        <span class="material-icons">edit</span>
                                        Edit
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0;">
                        <a href="../my_practice.php" class="btn btn-secondary">
                            View All Practices
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
