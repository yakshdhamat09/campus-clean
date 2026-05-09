<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3) {
    $category_name = trim($_POST['category_name']);
    
    if(!empty($category_name)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO complaint_categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
    }
    
    // Send the user right back to the categories page so they can see the updated list
    header("Location: ../manage_categories.php");
    exit();
}
?>