<?php
// Set the response header to JSON so browsers and clients know how to read it
header('Content-Type: application/json');
require_once '../config/db_connect.php';

// Query to count complaints per category
$sql = "SELECT cat.category_name, COUNT(c.id) as total_complaints 
        FROM complaint_categories cat 
        LEFT JOIN complaints c ON cat.id = c.category_id 
        GROUP BY cat.id";
        
$result = $conn->query($sql);
$data = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    // Convert the PHP array into a valid JSON string
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>