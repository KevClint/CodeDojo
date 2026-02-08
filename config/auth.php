<?php
/**
 * CodeDojo - Authentication Functions
 * Handles user and admin login/logout, password hashing, and session management
 */

/**
 * Hash a password using bcrypt
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify a password against a hash
 * @param string $password Plain text password
 * @param string $hash Password hash to verify against
 * @return bool True if password matches hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Authenticate admin user
 * @param string $username Admin username
 * @param string $password Admin password
 * @return array|false User array if successful, false if failed
 */
function authenticateAdmin($username, $password) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, username, email, password FROM admins WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && verifyPassword($password, $admin['password'])) {
            return [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'role' => 'admin'
            ];
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Admin authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Authenticate regular user
 * @param string $username User username
 * @param string $password User password
 * @return array|false User array if successful, false if failed
 */
function authenticateUser($username, $password) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, password FROM users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => 'user'
            ];
        }
        
        return false;
    } catch (Exception $e) {
        error_log("User authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create session for authenticated user
 * @param array $userData User data from authentication
 * @return void
 */
function createUserSession($userData) {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['last_activity'] = time();
    
    // Additional user-specific data
    if ($userData['role'] === 'user') {
        $_SESSION['first_name'] = $userData['first_name'] ?? '';
        $_SESSION['last_name'] = $userData['last_name'] ?? '';
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if logged in user is admin
 * @return bool True if logged in user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if logged in user is regular user
 * @return bool True if logged in user is regular user
 */
function isUser() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Get current logged in user's role
 * @return string|null User's role ('admin' or 'user') or null if not logged in
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current logged in user's ID
 * @return int|null User's ID or null if not logged in
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged in user's username
 * @return string|null User's username or null if not logged in
 */
function getUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Logout current user
 * @return void
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Check session timeout (30 minutes of inactivity)
 * @return bool True if session is valid, false if timed out
 */
function checkSessionTimeout() {
    $timeout_duration = 1800; // 30 minutes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        logoutUser();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Redirect to login if not authenticated
 * @param string $requiredRole Optional role requirement ('admin', 'user', or null for any)
 * @param string $loginPage Path to login page
 * @return void
 */
function requireLogin($requiredRole = null, $loginPage = '../login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $loginPage);
        exit;
    }
    
    if ($requiredRole && getUserRole() !== $requiredRole) {
        header('Location: ' . $loginPage . '?error=unauthorized');
        exit;
    }
    
    if (!checkSessionTimeout()) {
        header('Location: ' . $loginPage . '?timeout=1');
        exit;
    }
}

/**
 * Register a new user
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Password
 * @param string $firstName First name
 * @param string $lastName Last name
 * @return array Success/failure with message
 */
function registerUser($username, $email, $password, $firstName = '', $lastName = '') {
    try {
        $db = getDBConnection();
        
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Username or email already exists'
            ];
        }
        
        // Validate password strength (minimum 6 characters)
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters'
            ];
        }
        
        // Hash password and insert user
        $hashedPassword = hashPassword($password);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName]);
        
        return [
            'success' => true,
            'message' => 'User registered successfully'
        ];
    } catch (Exception $e) {
        error_log("User registration error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ];
    }
}

/**
 * Update admin password (with current password verification)
 * @param int $adminId Admin ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return array Success/failure with message
 */
function updateAdminPassword($adminId, $currentPassword, $newPassword) {
    try {
        $db = getDBConnection();
        
        // Get current password hash
        $stmt = $db->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            return ['success' => false, 'message' => 'Admin not found'];
        }
        
        // Verify current password
        if (!verifyPassword($currentPassword, $admin['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Hash and update new password
        $hashedPassword = hashPassword($newPassword);
        $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $adminId]);
        
        return ['success' => true, 'message' => 'Password updated successfully'];
    } catch (Exception $e) {
        error_log("Password update error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update password'];
    }
}
?>
