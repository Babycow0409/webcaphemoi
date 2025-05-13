<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$database = "lab1"; // Set the database name to match the SQL file

// Create connection to MySQL server (without selecting a database)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected to MySQL server successfully.<br>";

// Create the database if it doesn't exist
if (!$conn->query("CREATE DATABASE IF NOT EXISTS $database")) {
    die("Error creating database: " . $conn->error);
}
echo "Database '$database' created or already exists.<br>";

// Select the database
if (!$conn->select_db($database)) {
    die("Error selecting database: " . $conn->error);
}
echo "Using database: $database<br>";

// Read the SQL file
$sql_file = file_get_contents('lab1/lab1.sql');

if ($sql_file === false) {
    die("Error reading the SQL file.");
}

echo "SQL file read successfully.<br>";

// Execute the SQL commands
if ($conn->multi_query($sql_file)) {
    echo "SQL commands executed successfully.<br>";
    
    // Process all result sets
    do {
        // Store the result (if any)
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    if ($conn->error) {
        echo "Error executing SQL commands: " . $conn->error;
    }
} else {
    echo "Error executing SQL commands: " . $conn->error;
}

// Close connection
$conn->close();
echo "Connection closed.";
?> 