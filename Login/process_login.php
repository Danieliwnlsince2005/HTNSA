<?php
session_start();
require_once 'db.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

try {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($username)) {
        header('Location: login.php?error=username_required');
        exit;
    }

    if (empty($password)) {
        header('Location: login.php?error=password_required');
        exit;
    }

    // Find user by username or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        // Log successful login
        error_log("User logged in successfully: " . $user['username']);

        // Redirect to dashboard with cache control
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Location: dashboard.php');
        exit;
    } else {
        // Invalid credentials
        error_log("Failed login attempt for username: " . $username);
        header('Location: login.php?error=invalid_credentials');
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error during login: " . $e->getMessage());
    header('Location: login.php?error=db_error');
    exit;
} catch (Exception $e) {
    error_log("Server error during login: " . $e->getMessage());
    header('Location: login.php?error=server_error');
    exit;
}
?> 