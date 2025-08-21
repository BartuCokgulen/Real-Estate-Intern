<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Destroy session
session_destroy();

// Clear all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
$_SESSION['success'] = "You have been successfully logged out.";
header('Location: login.php');
exit;
?> 