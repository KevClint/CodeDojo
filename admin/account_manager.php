<?php
/**
 * CodeDojo - Account Management Helper
 * Script to create admin and user accounts programmatically
 * 
 * USAGE:
 * 1. Create a new PHP file with this code
 * 2. Modify the account details below
 * 3. Run the script once in your browser
 * 4. Delete the script for security
 * 
 * DO NOT keep this file in production!
 */

// Security: Only allow CLI or localhost access
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost']);
$isCLI = php_sapi_name() === 'cli';

if (!$isLocalhost && !$isCLI) {
    die('Access denied. This script can only be run locally.');
}

require_once 'config/database.php';
require_once 'config/auth.php';

// Example: Create a new admin account
function createNewAdmin($username, $email, $password) {
    try {
        $db = getDBConnection();
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Admin with this username or email already exists'];
        }
        
        // Create new admin
        $hashedPassword = hashPassword($password);
        $stmt = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);
        
        return ['success' => true, 'message' => 'Admin created successfully!'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Example: Create a new user account
function createNewUser($username, $email, $password, $firstName = '', $lastName = '') {
    try {
        $result = registerUser($username, $email, $password, $firstName, $lastName);
        return $result;
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Example: Reset admin password
function resetAdminPassword($username, $newPassword) {
    try {
        $db = getDBConnection();
        
        // Find admin
        $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            return ['success' => false, 'message' => 'Admin not found'];
        }
        
        // Update password
        $hashedPassword = hashPassword($newPassword);
        $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $admin['id']]);
        
        return ['success' => true, 'message' => 'Password reset successfully!'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Example: Reset user password
function resetUserPassword($username, $newPassword) {
    try {
        $db = getDBConnection();
        
        // Find user
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Update password
        $hashedPassword = hashPassword($newPassword);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        return ['success' => true, 'message' => 'Password reset successfully!'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// EXECUTE EXAMPLES HERE
// Uncomment and modify as needed, then run this file

/*
// Create a new admin
$result = createNewAdmin('newadmin', 'newadmin@codedojo.local', 'securepassword123');
echo $result['message'] . "\n";

// Create a new user
$result = createNewUser('newuser', 'newuser@example.com', 'password123', 'John', 'Doe');
echo $result['message'] . "\n";

// Reset admin password
$result = resetAdminPassword('admin', 'newpassword123');
echo $result['message'] . "\n";

// Reset user password
$result = resetUserPassword('user', 'newpassword123');
echo $result['message'] . "\n";
*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - CodeDojo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        h1 {
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .description {
            color: #64748b;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #0284c7;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 13px;
            color: #0c4a6e;
        }
        
        .code-block {
            background: #1e293b;
            color: #cbd5e1;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 20px 0;
            line-height: 1.6;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #1e293b;
            font-size: 18px;
            margin-bottom: 12px;
        }
        
        .function-name {
            color: #667eea;
            font-weight: 600;
            margin: 12px 0 8px 0;
            font-size: 14px;
        }
        
        .parameters {
            color: #64748b;
            font-size: 12px;
            background: #f1f5f9;
            padding: 12px;
            border-radius: 6px;
            margin: 8px 0 12px 0;
        }
        
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 13px;
        }
        
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 40px;
            color: #94a3b8;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Account Management Helper</h1>
        <p class="description">
            This script provides helper functions to create and manage admin and user accounts programmatically.
            <strong>This file should only be used for initial setup and then deleted.</strong>
        </p>
        
        <div class="warning">
            ⚠️ <strong>Security Warning:</strong> Delete this file from production after use. Never keep it accessible on a live server.
        </div>
        
        <div class="section">
            <h2>📋 Available Functions</h2>
            
            <div class="function-name">createNewAdmin($username, $email, $password)</div>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 10px;">
                Creates a new admin account in the database.
            </p>
            <div class="parameters">
                <strong>Parameters:</strong><br>
                • username: string (unique)<br>
                • email: string<br>
                • password: string (plain text, will be hashed)
            </div>
            <div class="code-block">
$result = createNewAdmin('john_admin', 'john@codedojo.local', 'securepass123');
echo $result['message'];
// Output: Admin created successfully!
            </div>
            
            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
            
            <div class="function-name">createNewUser($username, $email, $password, $firstName, $lastName)</div>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 10px;">
                Creates a new user account with optional first and last names.
            </p>
            <div class="parameters">
                <strong>Parameters:</strong><br>
                • username: string (unique)<br>
                • email: string (unique)<br>
                • password: string (min 6 characters)<br>
                • firstName: string (optional)<br>
                • lastName: string (optional)
            </div>
            <div class="code-block">
$result = createNewUser('jane_doe', 'jane@example.com', 'password123', 'Jane', 'Doe');
echo $result['message'];
// Output: User registered successfully
            </div>
            
            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
            
            <div class="function-name">resetAdminPassword($username, $newPassword)</div>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 10px;">
                Changes the password for an existing admin account.
            </p>
            <div class="parameters">
                <strong>Parameters:</strong><br>
                • username: string<br>
                • newPassword: string
            </div>
            <div class="code-block">
$result = resetAdminPassword('admin', 'newpassword456');
echo $result['message'];
// Output: Password reset successfully!
            </div>
            
            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
            
            <div class="function-name">resetUserPassword($username, $newPassword)</div>
            <p style="color: #64748b; font-size: 13px; margin-bottom: 10px;">
                Changes the password for an existing user account.
            </p>
            <div class="parameters">
                <strong>Parameters:</strong><br>
                • username: string<br>
                • newPassword: string
            </div>
            <div class="code-block">
$result = resetUserPassword('john_smith', 'newpassword789');
echo $result['message'];
// Output: Password reset successfully!
            </div>
        </div>
        
        <div class="section">
            <h2>🚀 How to Use</h2>
            
            <div class="info-box">
                <strong>Step 1:</strong> Open this file in a text editor<br>
                <strong>Step 2:</strong> Find the "EXECUTE EXAMPLES HERE" section at the bottom<br>
                <strong>Step 3:</strong> Uncomment the functions you need<br>
                <strong>Step 4:</strong> Modify parameters to your needs<br>
                <strong>Step 5:</strong> Save the file<br>
                <strong>Step 6:</strong> Access this file in your browser<br>
                <strong>Step 7:</strong> Check the output for success/error messages<br>
                <strong>Step 8:</strong> Delete this file immediately after use
            </div>
        </div>
        
        <div class="section">
            <h2>📝 Quick Reference</h2>
            
            <div class="code-block">
// Create admin
$result = createNewAdmin('admin2', 'admin2@local', 'pass123');

// Create user
$result = createNewUser('user1', 'user1@example.com', 'pass456', 'John', 'Doe');

// Reset admin password
$result = resetAdminPassword('admin', 'newpassword');

// Reset user password
$result = resetUserPassword('user1', 'newpassword');

// Check result
if ($result['success']) {
    echo "✓ " . $result['message'];
} else {
    echo "✗ " . $result['message'];
}
            </div>
        </div>
        
        <div class="warning">
            ⚠️ <strong>Important Security Notes:</strong><br>
            • All passwords are hashed with bcrypt before storage<br>
            • Never pass hashed passwords to these functions<br>
            • Usernames and emails must be unique<br>
            • Use strong passwords (8+ characters, mixed case, numbers)<br>
            • Delete this file immediately after creating accounts<br>
            • Never commit this file to version control
        </div>
        
        <div class="footer">
            <strong>Need more help?</strong><br>
            See AUTHENTICATION.md for complete documentation and advanced usage examples.
        </div>
    </div>
</body>
</html>
