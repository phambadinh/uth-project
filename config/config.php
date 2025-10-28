<?php
/**
 * Database Configuration & Session Management
 * UTH Learning System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials
$dbHost = 'localhost:3307';
$dbName = 'uth_learning';
$dbUser = 'root';
$dbPass = ''; // Để trống với XAMPP/WAMP mặc định

// DSN (Data Source Name)
$dsn = "mysql:host=$dbHost;port=3307;dbname=$dbName;charset=utf8mb4";

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Create PDO instance
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Log error (in production, don't display this to users)
    die('Database Connection Failed: ' . $e->getMessage());
}

// Define base URL
define('BASE_URL', 'http://localhost/uth-project');
define('ASSETS_URL', BASE_URL . '/assets');

// Error reporting (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
