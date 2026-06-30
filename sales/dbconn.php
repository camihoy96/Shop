<?php
$servername = "localhost";
$username = "root";      // default in XAMPP
$password = "";          // default is empty
$dbname = "shop";
$dbport = 3307;     // your database name

$conn = new mysqli($servername, $username, $password, $dbname, $dbport);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
