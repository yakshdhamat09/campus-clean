<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in and form was submitted via POST
if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../index.php");
    exit();
}

// 1. Gather text data from the form
$complainant_id = $_SESSION['user_id'];
$title = trim($_POST['title']);
$category_id = $_POST['category_id'];
$priority = $_POST['priority'];
$area_id = $_POST['area_id'];
$exact_location = trim($_POST['exact_location']);
$description = trim($_POST['description']);

// 2. File Upload Setup
$upload_dir = '../assets/uploads/proofs/';
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
$max_size = 2 * 1024 * 1024; // 2MB limit

$file = $_FILES['proof_file']; // Notice we use $_FILES for uploaded data, not $_POST
$file_path = "";

// 3. File Validation & Secure Upload
if ($file['error'] === UPLOAD_ERR_OK) {
    
    // Check file size (server-side validation)
    if ($file['size'] > $max_size) {
        die("Error: File size exceeds 2MB limit. Please go back and try a smaller file.");
    }
    
    // Check file type securely using finfo (prevents fake extensions)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        die("Error: Invalid file type. Only JPG, PNG, and PDF are allowed.");
    }
    
    // Securely rename file (e.g., proof_168000000_a1b2c3.jpg)
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = "proof_" . time() . "_" . uniqid() . "." . $file_ext;
    $destination = $upload_dir . $new_filename;
    
    // Move from temporary server storage to your assets folder
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Save relative path for the database so we can display it later
        $file_path = 'assets/uploads/proofs/' . $new_filename;
    } else {
        die("Error: Failed to save uploaded file to the server.");
    }
} else {
    die("Error: Please upload a valid evidence file.");
}

// 4. Generate Unique Tracking ID
$year = date("Y");
// Count how many complaints exist this year to determine the next number
$result = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE YEAR(created_at) = $year");
$row = $result->fetch_assoc();
$next_number = str_pad($row['total'] + 1, 3, "0", STR_PAD_LEFT); // Turns '1' into '001'
$tracking_id = "COMP-" . $year . "-" . $next_number;

// 5. Insert into main Complaints Table
$status_id = 1; // 1 = "Submitted" in our status_master table
$stmt = $conn->prepare("INSERT INTO complaints (tracking_id, complainant_id, category_id, area_id, exact_location, priority, title, description, status_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siiissssi", $tracking_id, $complainant_id, $category_id, $area_id, $exact_location, $priority, $title, $description, $status_id);

if ($stmt->execute()) {
    // Get the ID of the complaint we just inserted
    $complaint_id = $conn->insert_id;
    
    // 6. Insert the file path into Complaint Attachments Table
    $stmt_att = $conn->prepare("INSERT INTO complaint_attachments (complaint_id, file_path, upload_type) VALUES (?, ?, 'Proof')");
    $stmt_att->bind_param("is", $complaint_id, $file_path);
    $stmt_att->execute();
    
    // 7. Log the initial status in Complaint History
    $remark = "Complaint submitted by user.";
    $stmt_hist = $conn->prepare("INSERT INTO complaint_history (complaint_id, status_id, updated_by, remark) VALUES (?, ?, ?, ?)");
    $stmt_hist->bind_param("iiis", $complaint_id, $status_id, $complainant_id, $remark);
    $stmt_hist->execute();
    
    // Success! Send them back to the dashboard.
    header("Location: ../dashboard.php");
    exit();
} else {
    die("Database Error: " . $conn->error);
}
?>