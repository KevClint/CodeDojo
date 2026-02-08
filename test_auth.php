<?php
/**
 * Authentication Debug Script
 * Check database connection and admin account
 */

require_once 'config/database.php';
require_once 'config/auth.php';

echo "=== CodeDojo Authentication Debug ===\n\n";

// Test database connection
echo "1. Testing Database Connection...\n";
try {
    $db = getDBConnection();
    echo "✓ Database connected successfully\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Check if admins table exists
echo "2. Checking Admins Table...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM admins");
    $result = $stmt->fetch();
    echo "✓ Admins table exists with " . $result['count'] . " account(s)\n\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Make sure you've run the SQL file: database/new.sql\n";
    exit;
}

// Show all admin accounts
echo "3. Admin Accounts in Database:\n";
try {
    $stmt = $db->query("SELECT id, username, email, is_active, LENGTH(password) as hash_length FROM admins");
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "✗ No admin accounts found!\n";
        echo "Please run: mysql -u root < database/new.sql\n";
    } else {
        foreach ($admins as $admin) {
            echo "  - ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}, Active: " . ($admin['is_active'] ? 'Yes' : 'No') . ", Hash Length: {$admin['hash_length']}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test login with admin/codedojo123
echo "4. Testing Admin Login (admin/codedojo123):\n";
try {
    $result = authenticateAdmin('admin', 'codedojo123');
    if ($result) {
        echo "✓ Login successful!\n";
        echo "  User ID: {$result['id']}\n";
        echo "  Username: {$result['username']}\n";
        echo "  Email: {$result['email']}\n";
        echo "  Role: {$result['role']}\n";
    } else {
        echo "✗ Login failed - Invalid username or password\n";
        echo "  Checking database directly...\n";
        
        // Check if admin exists
        $stmt = $db->prepare("SELECT id, username, email, password, is_active FROM admins WHERE username = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            echo "  ✗ Admin user 'admin' not found in database\n";
            echo "  Run the SQL file: mysql -u root < database/new.sql\n";
        } else {
            echo "  ✓ Admin user 'admin' found in database\n";
            echo "  Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "\n";
            echo "  Password Hash: " . substr($admin['password'], 0, 30) . "... (length: " . strlen($admin['password']) . ")\n";
            
            // Test password verification
            if (password_verify('codedojo123', $admin['password'])) {
                echo "  ✓ Password verification succeeded\n";
            } else {
                echo "  ✗ Password verification failed\n";
                echo "  The stored hash doesn't match the password\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing User Login (user/user123):\n";
try {
    $result = authenticateUser('user', 'user123');
    if ($result) {
        echo "✓ Login successful!\n";
    } else {
        echo "✗ Login failed - Invalid username or password\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";
?>
