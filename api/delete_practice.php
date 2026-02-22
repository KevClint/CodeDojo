<?php
/**
 * Delete Practice API
 * Removes a practice from database
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
    // Get practice ID
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid practice ID'
        ]);
        exit;
    }
    
    // Connect to database
    $pdo = getDBConnection();
    
    // Check if practice exists
    $sql = "SELECT id FROM user_practice WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Practice not found'
        ]);
        exit;
    }
    
    // Delete practice
    $sql = "DELETE FROM user_practice WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Practice deleted successfully'
    ]);
    
} catch (Throwable $e) {
    error_log("Delete Practice Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
