<?php
/**
 * Helper functions for the Food Safety Checker application
 */

/**
 * Log user activity
 */
function logActivity($action, $description) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (PDOException $e) {
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    header("Location: $url");
    exit;
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['message'];
        $type = $_SESSION['flash_message']['type'];
        unset($_SESSION['flash_message']);
        
        return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                    {$message}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return '';
}

/**
 * Get user data
 */
function getUserData($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if ingredient is safe
 */
function checkIngredientSafety($ingredient) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT risk_level, health_impact, common_products 
                              FROM ingredients 
                              WHERE name LIKE ?");
        $stmt->execute(["%$ingredient%"]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error checking ingredient safety: " . $e->getMessage());
        return false;
    }
}

/**
 * Save user's favorite ingredients
 */
function saveFavoriteIngredient($userId, $ingredient) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO favorite_ingredients (user_id, ingredient, created_at) 
                              VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $ingredient]);
    } catch (PDOException $e) {
        error_log("Error saving favorite ingredient: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's favorite ingredients
 */
function getFavoriteIngredients($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT ingredient, created_at 
                              FROM favorite_ingredients 
                              WHERE user_id = ? 
                              ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching favorite ingredients: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
} 