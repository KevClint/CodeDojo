<?php
/**
 * Fix Authentication Hashes
 * Generates correct bcrypt hashes and updates the database
 */

require_once 'config/database.php';

echo "=== CodeDojo Hash Fix ===\n\n";

// Generate correct hashes
$adminPassword = 'codedojo123';
$userPassword = 'user123';

$adminHash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 10]);
$userHash = password_hash($userPassword, PASSWORD_BCRYPT, ['cost' => 10]);

echo "Correct Hashes Generated:\n";
echo "Admin Hash: $adminHash\n";
echo "User Hash: $userHash\n\n";

// Verify the hashes work
echo "Testing verification:\n";
if (password_verify($adminPassword, $adminHash)) {
    echo "✓ Admin hash verified\n";
} else {
    echo "✗ Admin hash failed\n";
}

if (password_verify($userPassword, $userHash)) {
    echo "✓ User hash verified\n";
} else {
    echo "✗ User hash failed\n";
}

echo "\n";

// Update database
try {
    $db = getDBConnection();
    
    // Update admin password
    $stmt = $db->prepare("UPDATE admins SET password = ? WHERE username = ?");
    $stmt->execute([$adminHash, 'admin']);
    echo "✓ Admin password updated in database\n";
    
    // Update user password
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$userHash, 'user']);
    echo "✓ User password updated in database\n";
    
    echo "\nNow try logging in with:\n";
    echo "Admin: admin / codedojo123\n";
    echo "User: user / user123\n";
    
} catch (Exception $e) {
    echo "✗ Error updating database: " . $e->getMessage() . "\n";
}

echo "\n=== Complete ===\n";
?>
