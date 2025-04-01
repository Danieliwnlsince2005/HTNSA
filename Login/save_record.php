<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if ingredients were provided
if (!isset($_POST['ingredients']) || empty($_POST['ingredients'])) {
    echo json_encode(['success' => false, 'message' => 'No ingredients provided']);
    exit;
}

require_once 'config.php';

try {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get ingredients and high_risk_count from POST data
    $ingredients = trim($_POST['ingredients']);
    $high_risk_count = isset($_POST['high_risk_count']) ? (int)$_POST['high_risk_count'] : 0;
    
    // Prepare SQL statement
    $sql = "INSERT INTO search_records (user_id, ingredients, high_risk_count, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param("isi", $user_id, $ingredients, $high_risk_count);
    
    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get the inserted record ID
    $record_id = $conn->insert_id;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Record saved successfully',
        'record_id' => $record_id
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error saving record: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error saving record: ' . $e->getMessage()
    ]);
}

// Close the connection
$conn->close();
?> 