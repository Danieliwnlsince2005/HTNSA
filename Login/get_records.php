<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get records for the current user
$sql = "SELECT * FROM search_records WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$records = array();
while ($row = $result->fetch_assoc()) {
    $records[] = array(
        'id' => $row['id'],
        'ingredients' => $row['ingredients'],
        'high_risk_count' => $row['high_risk_count'],
        'timestamp' => $row['timestamp']
    );
}

// Return records as JSON
header('Content-Type: application/json');
echo json_encode($records);
?> 