<?php
session_start();
require_once '../config/db_connect.php';

// Only Supervisors can do this
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3) {
    $complaint_id = intval($_POST['complaint_id']);
    $staff_id = intval($_POST['staff_id']);
    $supervisor_id = $_SESSION['user_id'];
    $status_id = 3; // 3 = "Assigned" in status_master table

    // 1. Fetch staff name so we can put it in the history timeline
    $staff_query = $conn->query("SELECT name FROM users WHERE id = $staff_id");
    if($staff_query->num_rows > 0) {
        $staff_name = $staff_query->fetch_assoc()['name'];

        // 2. Update the main complaints table
        $stmt = $conn->prepare("UPDATE complaints SET assigned_staff_id = ?, status_id = ? WHERE id = ?");
        $stmt->bind_param("iii", $staff_id, $status_id, $complaint_id);
        
        if ($stmt->execute()) {
            // 3. Log the action in the complaint history table
            $remark = "Ticket assigned to Staff: " . $staff_name;
            $hist_stmt = $conn->prepare("INSERT INTO complaint_history (complaint_id, status_id, updated_by, remark) VALUES (?, ?, ?, ?)");
            $hist_stmt->bind_param("iiis", $complaint_id, $status_id, $supervisor_id, $remark);
            $hist_stmt->execute();
        }
    }

    // Send the supervisor right back to the ticket page to see the update
    header("Location: ../view_complaint.php?id=" . $complaint_id);
    exit();
} else {
    // If not a supervisor or not a POST request, kick them out
    header("Location: ../dashboard.php");
    exit();
}
?>