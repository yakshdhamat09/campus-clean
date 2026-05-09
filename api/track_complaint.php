<?php
header('Content-Type: application/json');
require_once '../config/db_connect.php';

if(isset($_GET['tracking_id']) && !empty($_GET['tracking_id'])) {
    $tracking_id = $conn->real_escape_string(trim($_GET['tracking_id']));
    
    // Fetch basic details and current status
    $sql = "SELECT c.tracking_id, c.title, c.priority, s.status_name, DATE_FORMAT(c.created_at, '%d-%b-%Y') as date_submitted
            FROM complaints c 
            JOIN status_master s ON c.status_id = s.id 
            WHERE c.tracking_id = '$tracking_id'";
            
    $result = $conn->query($sql);
    
    if($result && $result->num_rows > 0) {
        $complaint = $result->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $complaint]);
    } else {
        echo json_encode(["status" => "error", "message" => "No complaint found with that Tracking ID."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Tracking ID is required."]);
}
?>