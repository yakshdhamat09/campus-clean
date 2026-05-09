<?php
$host = "localhost";
$username = "root"; 
$password = "YOUR_DATABASE_PASSWORD_HERE"; // Do not put real password here!
$database = "campus_clean";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>