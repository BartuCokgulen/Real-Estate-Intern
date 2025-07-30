<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'realestate_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'Real Estate');
define('SITE_URL', 'http://localhost/realestate');
define('UPLOAD_PATH', __DIR__ . '/../uploads');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

$properties_upload_path = UPLOAD_PATH . '/properties';
if (!file_exists($properties_upload_path)) {
    mkdir($properties_upload_path, 0777, true);
} 