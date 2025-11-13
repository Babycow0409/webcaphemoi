<?php
// Simple script to make sure custom_order_id column exists
$conn = new mysqli('localhost', 'root', '', 'lab1');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if column exists
$sql = "SHOW COLUMNS FROM orders LIKE 'custom_order_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo 'Column custom_order_id exists in orders table.';
} else {
    // Add the column if it doesn't exist
    $sql = "ALTER TABLE orders ADD COLUMN custom_order_id VARCHAR(50) NULL UNIQUE AFTER id";
    if ($conn->query($sql) === TRUE) {
        echo 'Column custom_order_id added to orders table.';
    } else {
        echo 'Error adding column: ' . $conn->error;
    }
}

$conn->close();
?> 