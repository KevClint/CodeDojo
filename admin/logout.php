<?php
/**
 * Admin Logout
 * Clear session and redirect to unified login page
 */

session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

// Use unified logout function
logoutUser();

// Redirect to unified login page
header('Location: ../login.php');
exit;
?>
