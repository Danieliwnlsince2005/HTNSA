<?php
require_once 'db.php';

header('Content-Type: application/json');

// Get form data
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input
$errors = [];

if (empty($username)) {
    $errors['username'] = 'Username is required';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors['username'] = 'Username can only contain letters, numbers, and underscores';
} elseif (strlen($username) < 3 || strlen($username) > 50) {
    $errors['username'] = 'Username must be between 3 and 50 characters';
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || 
         !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*]/', $password)) {
    $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>