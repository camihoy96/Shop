<?php
$servername = "localhost";
$username = "root";      // default in XAMPP
$password = "";          // default is empty
$dbname = "st4nger";     // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
