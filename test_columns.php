<?php
// Do includes first
include 'includes/db_connect.php';
require_once 'includes/db_checks.php';

// Start the session after setting headers
session_start();

// Set content type to plain text for better readability in browser
header('Content-Type: text/plain');

echo "Database Column Check Results:\n";
echo "==============================\n\n";

// Function to check if column exists and output status
function checkColumnStatus($conn, $table, $column) {
    $query = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($query);
    $exists = ($result && $result->num_rows > 0) ? "EXISTS" : "MISSING";
    echo sprintf("%-20s %-30s %s\n", $table, $column, $exists);
    return $exists === "EXISTS";
}

// Check orders table columns
echo "Checking orders table columns:\n";
echo "------------------------------\n";
$ordersColumns = ['id', 'user_id', 'shipping_name', 'shipping_address', 'shipping_phone', 
                 'total_amount', 'payment_method', 'status', 'order_date', 'custom_order_id'];

foreach ($ordersColumns as $column) {
    checkColumnStatus($conn, 'orders', $column);
}

echo "\nChecking order_items table columns:\n";
echo "------------------------------\n";
$orderItemsColumns = ['id', 'order_id', 'product_id', 'product_name', 'quantity', 'price', 'image'];

foreach ($orderItemsColumns as $column) {
    checkColumnStatus($conn, 'order_items', $column);
}

echo "\n\nApplying fixes...\n";
echo "=====================\n";

// Run the database check function
checkOrderSystemDb($conn);

echo "\nVerifying again after fixes:\n";
echo "============================\n";

echo "\nOrders table columns after fix:\n";
echo "------------------------------\n";
foreach ($ordersColumns as $column) {
    checkColumnStatus($conn, 'orders', $column);
}

echo "\nOrder items table columns after fix:\n";
echo "------------------------------\n";
foreach ($orderItemsColumns as $column) {
    checkColumnStatus($conn, 'order_items', $column);
}

echo "\nTest completed. Please check checkout.php again.";
?> 