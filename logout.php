<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_destroy();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

$_SESSION['success'] = "You have been successfully logged out.";
header('Location: login.php');
exit;
?> 