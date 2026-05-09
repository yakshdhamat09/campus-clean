<?php
session_start();
require_once '../config/db_connect.php';

// Only Staff can do this
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2) {
    $complaint_id = intval($_POST['complaint_id']);
    $staff_id = $_SESSION['user_id'];
    $remark = trim($_POST['remark']);
    $status_id = 5; // 5 = "Resolved" in status_master
    
    $upload_dir = '../assets/uploads/proofs/';
    $allowed_types = ['image/jpeg', 'image/png']; // Usually just want photos for resolution proof
    $file = $_FILES['action_proof'];
    $file_path = "";

    // 1. Handle the File Upload
    if ($file['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types) || $file['size'] > 2 * 1024 * 1024) {
            die("Error: Invalid file type or size exceeds 2MB.");
        }
        
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = "action_" . time() . "_" . uniqid() . "." . $file_ext;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $file_path = 'assets/uploads/proofs/' . $new_filename;
        } else {
            die("Error saving file.");
        }
    } else {
        die("Action proof image is required.");
    }

    // 2. Update the Complaint Status and set resolved_at time
    $stmt = $conn->prepare("UPDATE complaints SET status_id = ?, resolved_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("ii", $status_id, $complaint_id);
    
    if ($stmt->execute()) {
        // 3. Save the Action Proof image to attachments
        $stmt_att = $conn->prepare("INSERT INTO complaint_attachments (complaint_id, file_path, upload_type) VALUES (?, ?, 'Action_Proof')");
        $stmt_att->bind_param("is", $complaint_id, $file_path);
        $stmt_att->execute();
        
        // 4. Log the resolution in history
        $full_remark = "Resolved: " . $remark;
        $hist_stmt = $conn->prepare("INSERT INTO complaint_history (complaint_id, status_id, updated_by, remark) VALUES (?, ?, ?, ?)");
        $hist_stmt->bind_param("iiis", $complaint_id, $status_id, $staff_id, $full_remark);
        $hist_stmt->execute();
    }

    header("Location: ../view_complaint.php?id=" . $complaint_id);
    exit();
}
?>