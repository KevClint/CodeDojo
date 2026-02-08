<?php
/**
 * CodeDojo - Manage Tasks
 * Admin page to create, edit, and delete practice tasks
 */

require_once 'auth_check.php';
require_once '../config/database.php';

$pageTitle = 'Manage Tasks';
$success = '';
$error = '';

$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $lesson_id = intval($_POST['lesson_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $instruction = trim($_POST['instruction'] ?? '');
        $hint = trim($_POST['hint'] ?? '');
        $starter_code = $_POST['starter_code'] ?? '';
        $order_num = intval($_POST['order_num'] ?? 0);
        
        if ($lesson_id > 0 && !empty($title) && !empty($instruction)) {
            try {
                $sql = "INSERT INTO practice_tasks (lesson_id, title, instruction, hint, starter_code, order_num) 
                        VALUES (:lesson_id, :title, :instruction, :hint, :starter_code, :order_num)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':lesson_id' => $lesson_id,
                    ':title' => $title,
                    ':instruction' => $instruction,
                    ':hint' => $hint,
                    ':starter_code' => $starter_code,
                    ':order_num' => $order_num
                ]);
                $success = 'Task created successfully!';
            } catch (Exception $e) {
                $error = 'Error creating task: ' . $e->getMessage();
            }
        } else {
            $error = 'Lesson, title, and instruction are required';
        }
    }
    
    elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $lesson_id = intval($_POST['lesson_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $instruction = trim($_POST['instruction'] ?? '');
        $hint = trim($_POST['hint'] ?? '');
        $starter_code = $_POST['starter_code'] ?? '';
        $order_num = intval($_POST['order_num'] ?? 0);
        
        if ($id > 0 && $lesson_id > 0 && !empty($title) && !empty($instruction)) {
            try {
                $sql = "UPDATE practice_tasks SET lesson_id = :lesson_id, title = :title, instruction = :instruction, 
                        hint = :hint, starter_code = :starter_code, order_num = :order_num WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':lesson_id' => $lesson_id,
                    ':title' => $title,
                    ':instruction' => $instruction,
                    ':hint' => $hint,
                    ':starter_code' => $starter_code,
                    ':order_num' => $order_num
                ]);
                $success = 'Task updated successfully!';
            } catch (Exception $e) {
                $error = 'Error updating task: ' . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id > 0) {
            try {
                $sql = "DELETE FROM practice_tasks WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $id]);
                $success = 'Task deleted successfully!';
            } catch (Exception $e) {
                $error = 'Error deleting task: ' . $e->getMessage();
            }
        }
    }
}

// Get task to edit
$editTask = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $sql = "SELECT * FROM practice_tasks WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $editId]);
    $editTask = $stmt->fetch();
}

// Get all lessons for dropdown
$lessons = $pdo->query("SELECT * FROM lessons ORDER BY order_num ASC")->fetchAll();

// Get all tasks with lesson info
$sql = "SELECT pt.*, l.title as lesson_title, l.difficulty 
        FROM practice_tasks pt 
        LEFT JOIN lessons l ON pt.lesson_id = l.id 
        ORDER BY l.order_num ASC, pt.order_num ASC";
$tasks = $pdo->query($sql)->fetchAll();
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
                    <a href="manage_tasks.php" class="nav-item active">
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
                    <h1>Manage Tasks</h1>
                </div>
                <div class="header-right">
                    <a href="?action=add" class="btn btn-primary">
                        <span class="material-icons">add</span>
                        New Task
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
                
                <?php if (empty($lessons)): ?>
                    <div style="background: #fef3c7; color: #92400e; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons">warning</span>
                        You need to create a lesson first before adding tasks. <a href="manage_lessons.php?action=add" style="color: #92400e; font-weight: 600; margin-left: 8px;">Create Lesson</a>
                    </div>
                <?php endif; ?>
                
                <!-- Add/Edit Form -->
                <?php if ((isset($_GET['action']) && $_GET['action'] === 'add' || $editTask) && !empty($lessons)): ?>
                    <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color); margin-bottom: var(--spacing-xl);">
                        <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: var(--text-primary);">
                            <?php echo $editTask ? 'Edit Task' : 'Create New Task'; ?>
                        </h2>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="<?php echo $editTask ? 'edit' : 'add'; ?>">
                            <?php if ($editTask): ?>
                                <input type="hidden" name="id" value="<?php echo $editTask['id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; gap: var(--spacing-md);">
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Lesson *
                                    </label>
                                    <select name="lesson_id" required
                                            style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary);">
                                        <option value="">Select a lesson...</option>
                                        <?php foreach ($lessons as $lesson): ?>
                                            <option value="<?php echo $lesson['id']; ?>" 
                                                    <?php echo ($editTask['lesson_id'] ?? '') == $lesson['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($lesson['title']); ?> (<?php echo ucfirst($lesson['difficulty']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Task Title *
                                    </label>
                                    <input type="text" name="title" required 
                                           value="<?php echo htmlspecialchars($editTask['title'] ?? ''); ?>"
                                           style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary);"
                                           placeholder="e.g., Create Your First Button">
                                </div>
                                
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Task Instruction * <small style="color: var(--text-muted); font-weight: 400;">(What should the student do?)</small>
                                    </label>
                                    <textarea name="instruction" rows="4" required
                                              style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; resize: vertical; background: var(--bg-primary); color: var(--text-primary);"
                                              placeholder="Clear instructions for the student (1-3 sentences)"><?php echo htmlspecialchars($editTask['instruction'] ?? ''); ?></textarea>
                                </div>
                                
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Hint <small style="color: var(--text-muted); font-weight: 400;">(Optional - guide without spoiling)</small>
                                    </label>
                                    <textarea name="hint" rows="3"
                                              style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; resize: vertical; background: var(--bg-primary); color: var(--text-primary);"
                                              placeholder="Helpful hint that points in the right direction"><?php echo htmlspecialchars($editTask['hint'] ?? ''); ?></textarea>
                                </div>
                                
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Starter Code <small style="color: var(--text-muted); font-weight: 400;">(Optional - helps students get started)</small>
                                    </label>
                                    <textarea name="starter_code" rows="8"
                                              style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; font-family: 'Courier New', monospace; resize: vertical; background: var(--bg-code); color: var(--text-code);"
                                              placeholder="<!-- HTML starter code or comments to guide students -->"><?php echo htmlspecialchars($editTask['starter_code'] ?? ''); ?></textarea>
                                </div>
                                
                                <div>
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-primary);">
                                        Order Number <small style="color: var(--text-muted); font-weight: 400;">(Lower numbers appear first)</small>
                                    </label>
                                    <input type="number" name="order_num" min="0"
                                           value="<?php echo htmlspecialchars($editTask['order_num'] ?? '0'); ?>"
                                           style="width: 200px; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 16px; font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary);"
                                           placeholder="0">
                                </div>
                                
                                <div style="display: flex; gap: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid var(--border-color);">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="material-icons"><?php echo $editTask ? 'save' : 'add'; ?></span>
                                        <?php echo $editTask ? 'Update Task' : 'Create Task'; ?>
                                    </button>
                                    <a href="manage_tasks.php" class="btn btn-secondary">
                                        <span class="material-icons">cancel</span>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Tasks List -->
                <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
                    <h2 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-md); color: var(--text-primary);">
                        All Tasks (<?php echo count($tasks); ?>)
                    </h2>
                    
                    <?php if (empty($tasks)): ?>
                        <p style="color: var(--text-secondary); text-align: center; padding: var(--spacing-xl);">
                            No tasks yet. Create your first task to get started!
                        </p>
                    <?php else: ?>
                        <div style="display: grid; gap: var(--spacing-md);">
                            <?php 
                            $currentLesson = null;
                            foreach ($tasks as $task): 
                                if ($currentLesson !== $task['lesson_title']):
                                    if ($currentLesson !== null) echo '</div>';
                                    $currentLesson = $task['lesson_title'];
                            ?>
                                <div style="margin-top: <?php echo $currentLesson === $task['lesson_title'] ? '0' : 'var(--spacing-lg)'; ?>;">
                                    <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-sm); display: flex; align-items: center; gap: var(--spacing-sm);">
                                        <span class="material-icons" style="font-size: 20px; color: var(--color-primary);">book</span>
                                        <?php echo htmlspecialchars($task['lesson_title']); ?>
                                        <span class="task-difficulty <?php echo $task['difficulty']; ?>" style="margin-left: 8px;">
                                            <?php echo ucfirst($task['difficulty']); ?>
                                        </span>
                                    </h3>
                                    <div style="display: grid; gap: var(--spacing-sm);">
                            <?php endif; ?>
                            
                                        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 8px; padding: var(--spacing-md);">
                                            <div style="display: flex; justify-content: space-between; align-items: start; gap: var(--spacing-md);">
                                                <div style="flex: 1;">
                                                    <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-xs);">
                                                        <span style="color: var(--text-muted); font-size: 13px;">Order: <?php echo $task['order_num']; ?></span>
                                                    </div>
                                                    <h4 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin-bottom: var(--spacing-xs);">
                                                        <?php echo htmlspecialchars($task['title']); ?>
                                                    </h4>
                                                    <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: var(--spacing-sm); line-height: 1.5;">
                                                        <?php echo htmlspecialchars($task['instruction']); ?>
                                                    </p>
                                                    <?php if ($task['hint']): ?>
                                                        <div style="background: var(--bg-primary); border-left: 3px solid var(--color-info); padding: 8px 12px; border-radius: 4px; font-size: 13px; color: var(--text-secondary);">
                                                            💡 <strong>Hint:</strong> <?php echo htmlspecialchars(substr($task['hint'], 0, 80)) . (strlen($task['hint']) > 80 ? '...' : ''); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="display: flex; gap: 8px;">
                                                    <a href="?edit=<?php echo $task['id']; ?>" class="btn btn-secondary btn-small">
                                                        <span class="material-icons">edit</span>
                                                    </a>
                                                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-small">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                            
                            <?php endforeach; ?>
                                    </div>
                                </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/theme.js"></script>
</body>
</html>
