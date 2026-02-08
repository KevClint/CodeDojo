<?php
/**
 * CodeDojo - User Logout
 * Destroys session and redirects to login
 */

session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

// Logout the user
logoutUser();

// Redirect to login page
header('Location: ../login.php');
exit;
?>
