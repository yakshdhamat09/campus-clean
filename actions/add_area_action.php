<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3) {
    $department = trim($_POST['department_name']);
    $section = trim($_POST['section_name']);
    $facility = trim($_POST['facility_spot']);
    
    if(!empty($department) && !empty($section) && !empty($facility)) {
        // Prepare the SQL statement for all three columns
        $stmt = $conn->prepare("INSERT INTO area_master (department_name, section_name, facility_spot) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $department, $section, $facility);
        $stmt->execute();
    }
    
    // Send the user right back to the areas page
    header("Location: ../manage_areas.php");
    exit();
}
?>