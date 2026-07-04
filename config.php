<?php

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ojo-security-system";

// Create MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and display a clear error message if connection fails
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>