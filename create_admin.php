<?php
require('dbconn.php'); // your DB connection

// Admin details
$name = "Administrator"; // change to your preferred name
$email = "admin@gmail.com"; // change to your preferred email
$password = "admin"; // change to your preferred password
$type = "admin";

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin into the users table
$sql = "INSERT INTO users (name, email, password, type) 
        VALUES ('$name', '$email', '$hashedPassword', '$type')";

if ($conn->query($sql) === TRUE) {
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>
