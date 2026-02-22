<?php
/**
 * Load Practice API
 * Retrieves saved practices from database
 */

header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    
    // Check if loading single practice by ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        $sql = "SELECT * FROM user_practice WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $practice = $stmt->fetch();
        
        if ($practice) {
            echo json_encode([
                'success' => true,
                'practice' => $practice
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Practice not found'
            ]);
        }
        
    } else {
        // Load all practices
        $sql = "SELECT * FROM user_practice ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        $practices = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'practices' => $practices,
            'count' => count($practices)
        ]);
    }
    
} catch (Throwable $e) {
    error_log("Load Practice Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
