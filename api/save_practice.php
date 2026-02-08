<?php
/**
 * Save Practice API
 * Handles saving new practice submissions
 */

header('Content-Type: application/json');
require_once '../config/database.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $html_code = $_POST['html_code'] ?? '';
    $task_id = !empty($_POST['task_id']) ? intval($_POST['task_id']) : null;
    
    // Validation
    if (empty($title)) {
        echo json_encode([
            'success' => false,
            'message' => 'Title is required'
        ]);
        exit;
    }
    
    if (empty($html_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'HTML code is required'
        ]);
        exit;
    }
    
    // Connect to database
    $pdo = getDBConnection();
    
    // Insert practice
    $sql = "INSERT INTO user_practice (title, html_code, task_id) VALUES (:title, :html_code, :task_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':html_code' => $html_code,
        ':task_id' => $task_id
    ]);
    
    // Get inserted ID
    $insertId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Practice saved successfully',
        'id' => $insertId
    ]);
    
} catch (PDOException $e) {
    error_log("Save Practice Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
