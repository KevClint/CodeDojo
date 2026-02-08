<?php
/**
 * Admin Authentication Check
 * Include this at the top of all admin pages
 * Uses unified authentication system
 */

session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

// Support both old and new authentication methods for backward compatibility
// Check old method first (legacy support)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Migrate to new authentication system
    $_SESSION['logged_in'] = true;
    $_SESSION['role'] = 'admin';
    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = $_SESSION['admin_username'] ?? 'admin';
    }
} else {
    // Use new authentication system
    requireLogin('admin', '../login.php');
}
?>
