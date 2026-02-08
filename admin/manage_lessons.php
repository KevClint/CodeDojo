<?php
/**
 * CodeDojo - Manage Lessons
 * Admin page to create, edit, and delete lessons
 */

require_once 'auth_check.php';
require_once '../config/database.php';

$pageTitle = 'Manage Lessons';
$success = '';
$error = '';

$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Add new lesson
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $difficulty = $_POST['difficulty'] ?? 'beginner';
        $order_num = intval($_POST['order_num'] ?? 0);
        
        // Validate difficulty level
        $valid_difficulties = ['beginner', 'intermediate', 'advanced'];
        if (!in_array($difficulty, $valid_difficulties)) {
            $difficulty = 'beginner';
        }
        
        if (!empty($title)) {
            try {
                $sql = "INSERT INTO lessons (title, description, difficulty, order_num) VALUES (:title, :description, :difficulty, :order_num)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':difficulty' => $difficulty,
                    ':order_num' => $order_num
                ]);
                $success = 'Lesson created successfully!';
            } catch (Exception $e) {
                $error = 'Error creating lesson: ' . $e->getMessage();
            }
        } else {
            $error = 'Title is required';
        }
    }
    
    elseif ($action === 'edit') {
        // Edit existing lesson
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $difficulty = $_POST['difficulty'] ?? 'beginner';
        $order_num = intval($_POST['order_num'] ?? 0);
        
        // Validate difficulty level
        $valid_difficulties = ['beginner', 'intermediate', 'advanced'];
        if (!in_array($difficulty, $valid_difficulties)) {
            $difficulty = 'beginner';
        }
        
        if ($id > 0 && !empty($title)) {
            try {
                $sql = "UPDATE lessons SET title = :title, description = :description, difficulty = :difficulty, order_num = :order_num WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':title' => $title,
                    ':description' => $description,
                    ':difficulty' => $difficulty,
                    ':order_num' => $order_num
                ]);
                $success = 'Lesson updated successfully!';
            } catch (Exception $e) {
                $error = 'Error updating lesson: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'delete') {
        // Delete lesson
        $id = intval($_POST['id'] ?? 0);
        
        if ($id > 0) {
            try {
                $sql = "DELETE FROM lessons WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                $success = 'Lesson deleted successfully!';
            } catch (Exception $e) {
                $error = 'Error deleting lesson: ' . $e->getMessage();
            }
        }
    }
}

// Get lesson to edit if ID provided
$editLesson = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $sql = "SELECT * FROM lessons WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $editId]);
    $editLesson = $stmt->fetch();
}

// Get all lessons
$sql = "SELECT l.*, COUNT(pt.id) as task_count FROM lessons l LEFT JOIN practice_tasks pt ON l.id = pt.lesson_id GROUP BY l.id ORDER BY l.order_num ASC";
$lessons = $pdo->query($sql)->fetchAll();
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
                    <a href="manage_lessons.php" class="nav-item active">
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
                    <h1>Manage Lessons</h1>
                </div>
                <div class="header-right">
                    <a href="?action=add" class="btn btn-primary">
                        <span class="material-icons">add</span>
                        New Lesson
                    </a>
                </div>
            </header>
            
            <div style="padding: var(--spacing-xl); max-width: 1400px;">
                <?php if ($success): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons">check_circle</span>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons">error</span>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add/Edit Form -->
                <?php if (isset($_GET['action']) && $_GET['action'] === 'add' || $editLesson): ?>
                    <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color); margin-bottom: var(--spacing-xl);">
                        <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: var(--text-primary);">
                            <?php echo $editLesson ? 'Edit Lesson' : 'Create New Lesson'; ?>
                        </h2>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?php echo $editLesson ? 'edit' : 'add'; ?>">
                            <?php if ($editLesson): ?>
                                <input type="hidden" name="id" value="<?php echo $editLesson['id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; gap: var(--spacing-md);">
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Lesson Title *
                                    </label>
                                    <input type="text" name="title" required 
                                           value="<?php echo htmlspecialchars($editLesson['title'] ?? ''); ?>"
                                           style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary);"
                                           placeholder="e.g., HTML Basics">
                                </div>
                                
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Description
                                    </label>
                                    <textarea name="description" rows="3"
                                              style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; resize: vertical; background: var(--bg-primary); color: var(--text-primary);"
                                              placeholder="Brief description of what students will learn"><?php echo htmlspecialchars($editLesson['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                                    <div>
                                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                            Difficulty Level
                                        </label>
                                        <select name="difficulty" required
                                                style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary);">
                                            <option value="beginner" <?php echo (!$editLesson || ($editLesson['difficulty'] ?? '') === 'beginner') ? 'selected' : ''; ?>>Beginner</option>
                                            <option value="intermediate" <?php echo ($editLesson && ($editLesson['difficulty'] ?? '') === 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                            <option value="advanced" <?php echo ($editLesson && ($editLesson['difficulty'] ?? '') === 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: var(--spacing-md); padding-top: var(--spacing-md);">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="material-icons"><?php echo $editLesson ? 'save' : 'add'; ?></span>
                                        <?php echo $editLesson ? 'Update Lesson' : 'Create Lesson'; ?>
                                    </button>
                                    <a href="manage_lessons.php" class="btn btn-secondary">
                                        <span class="material-icons">cancel</span>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Lessons List -->
                <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
                    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: var(--text-primary);">
                        All Lessons (<?php echo count($lessons); ?>)
                    </h2>
                    
                    <?php if (empty($lessons)): ?>
                        <p style="color: var(--text-secondary); text-align: center; padding: var(--spacing-xl);">
                            No lessons yet. Create your first lesson to get started!
                        </p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid var(--border-color);">
                                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-primary);">Title</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-primary);">Difficulty</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-primary);">Tasks</th>
                                        <th style="padding: 12px; text-align: left; font-weight: 600; color: var(--text-primary);">Created</th>
                                        <th style="padding: 12px; text-align: right; font-weight: 600; color: var(--text-primary);">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                            <td style="padding: 12px;">
                                                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">
                                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                                </div>
                                                <?php if ($lesson['description']): ?>
                                                    <div style="font-size: 13px; color: var(--text-secondary);">
                                                        <?php echo htmlspecialchars(substr($lesson['description'], 0, 60)) . (strlen($lesson['description']) > 60 ? '...' : ''); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 12px;">
                                                <span class="task-difficulty <?php echo $lesson['difficulty']; ?>">
                                                    <?php echo ucfirst($lesson['difficulty']); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 12px; color: var(--text-secondary);">
                                                <?php echo $lesson['task_count']; ?> task<?php echo $lesson['task_count'] != 1 ? 's' : ''; ?>
                                            </td>
                                            <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">
                                                <?php echo date('M j, Y', strtotime($lesson['created_at'])); ?>
                                            </td>
                                            <td style="padding: 12px; text-align: right;">
                                                <div style="display: inline-flex; gap: 8px;">
                                                    <a href="?edit=<?php echo $lesson['id']; ?>" class="btn btn-secondary btn-small">
                                                        <span class="material-icons">edit</span>
                                                    </a>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure? This will also delete all tasks in this lesson.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $lesson['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-small">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/theme.js"></script>
</body>
</html>
