<?php
session_start();
require_once 'db.php';

// Check session
if (isset($_SESSION['user_id'])) {
    // User is logged in
    return;
}

// Check remember me cookie
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE remember_token = ? AND token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            exit;
        }
    } catch (PDOException $e) {
        // Error checking token - just continue to redirect
    }
}

// Not logged in - redirect to login
header('Location: login.php');
exit;
?>