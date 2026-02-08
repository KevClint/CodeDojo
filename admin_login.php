<?php
/**
 * CodeDojo - Admin Login (Legacy)
 * Redirects to unified login page
 * This page is kept for backward compatibility
 */

session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

// If already logged in as admin, go to dashboard
if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

// If logged in as user, redirect to login with admin role selected
if (isLoggedIn()) {
    logoutUser();
}

// Redirect to unified login page with admin role selected
header('Location: login.php?role=admin');
exit;
?>

