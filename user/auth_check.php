<?php
/**
 * User Authentication Check
 * Include this at the top of all user-accessible pages
 * Ensures user is logged in and redirects to login if not
 */

session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

// Require user login
requireLogin('user', '../login.php');
?>
