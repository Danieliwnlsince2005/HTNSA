<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set custom error log path
ini_set('error_log', __DIR__ . '/logs/error.log');

$host = 'localhost';
$dbname = 'food_safety_checker';
$username = 'root';
$password = '';

try {
    // Test MySQL connection first
    $testConn = new mysqli($host, $username, $password);
    if ($testConn->connect_error) {
        throw new Exception("MySQL connection failed: " . $testConn->connect_error);
    }
    
    // Check if database exists
    $result = $testConn->query("SHOW DATABASES LIKE '$dbname'");
    if ($result->num_rows == 0) {
        // Create database if it doesn't exist
        $testConn->query("CREATE DATABASE IF NOT EXISTS $dbname");
        error_log("Database created: $dbname");
    }
    
    // Close test connection
    $testConn->close();
    
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (empty($tables)) {
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL
        )");
        error_log("Users table created");
    }
    
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}
?>