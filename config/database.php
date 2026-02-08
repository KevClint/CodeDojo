<?php
/**
 * Database Configuration for CodeDojo
 * XAMPP Compatible Settings
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Default XAMPP username
define('DB_PASS', '');               // Default XAMPP password (empty)
define('DB_NAME', 'codedojo');
define('DB_CHARSET', 'utf8mb4');

// Create database connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error (in production, log to file instead)
        error_log("Database Connection Error: " . $e->getMessage());
        
        // User-friendly error message
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please check your configuration.'
        ]));
    }
}

// Test connection (optional - uncomment to debug)
// $conn = getDBConnection();
// if ($conn) {
//     echo "Database connected successfully!";
// }
?>
