<?php
session_start();
require_once '../config/db_connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Secure database query using Prepared Statements (prevents SQL injection)
    $stmt = $conn->prepare("SELECT id, name, password, role_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (using plain text for now to keep it simple, though password_verify() is standard)
        if ($password === $user['password']) {
            // Success! Store user data in Session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role_id'] = $user['role_id'];
            
            header("Location: ../dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid password.";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No user found with that email.";
        header("Location: ../index.php");
        exit();
    }
}
?>