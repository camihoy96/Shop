<?php
require 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful!<br>";

// Test a simple query
$result = $conn->query("SELECT 1");
if ($result) {
    echo "Query test successful!";
} else {
    echo "Query test failed: " . $conn->error;
}

$conn->close();
?>