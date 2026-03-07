<?php
/**
 * Get Tasks API
 * Retrieves practice tasks by lesson
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    
    // Check if getting single task
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        $sql = "SELECT pt.*, l.title as lesson_title, l.difficulty 
                FROM practice_tasks pt 
                LEFT JOIN lessons l ON pt.lesson_id = l.id 
                WHERE pt.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $task = $stmt->fetch();
        
        if ($task) {
            echo json_encode([
                'success' => true,
                'task' => $task
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Task not found'
            ]);
        }
        
    } elseif (isset($_GET['lesson_id'])) {
        // Get tasks by lesson
        $lesson_id = intval($_GET['lesson_id']);
        
        $sql = "SELECT * FROM practice_tasks WHERE lesson_id = :lesson_id ORDER BY order_num ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':lesson_id' => $lesson_id]);
        $tasks = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'tasks' => $tasks
        ]);
        
    } else {
        // Get all tasks
        $sql = "SELECT pt.*, l.title as lesson_title, l.difficulty 
                FROM practice_tasks pt 
                LEFT JOIN lessons l ON pt.lesson_id = l.id 
                ORDER BY l.order_num ASC, pt.order_num ASC";
        $stmt = $pdo->query($sql);
        $tasks = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'tasks' => $tasks
        ]);
    }
    
} catch (Throwable $e) {
    error_log("Get Tasks Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
