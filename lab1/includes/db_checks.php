<?php
/**
 * Function to check if a column exists in a table and create it if missing
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @param string $definition Column definition (e.g. "VARCHAR(50) NULL")
 * @param string $after Column after which to add the new column (optional)
 * @return bool True if operation was successful
 */
function ensureColumnExists($conn, $table, $column, $definition, $after = null) {
    try {
        // Check if column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        
        if ($checkColumn->num_rows === 0) {
            // Column doesn't exist, create it
            $afterClause = $after ? " AFTER `$after`" : "";
            $alterQuery = "ALTER TABLE `$table` ADD COLUMN `$column` $definition$afterClause";
            
            if ($conn->query($alterQuery) === TRUE) {
                error_log("Column $column added to table $table successfully");
                return true;
            } else {
                error_log("Error adding column $column to table $table: " . $conn->error);
                return false;
            }
        } else {
            // Column already exists
            return true;
        }
    } catch (Exception $e) {
        error_log("Exception when ensuring column $column exists: " . $e->getMessage());
        return false;
    }
}

/**
 * Check all required database columns for the ordering system
 * @param mysqli $conn Database connection
 * @return array Array of messages indicating what was fixed
 */
function checkOrderSystemDb($conn) {
    $messages = [];
    
    // Check orders table columns
    $result1 = ensureColumnExists($conn, "orders", "custom_order_id", "VARCHAR(50) NULL UNIQUE", "id");
    if ($result1) $messages[] = "Checked orders.custom_order_id column";
    
    $result2 = ensureColumnExists($conn, "orders", "order_number", "VARCHAR(30) UNIQUE", "id");
    if ($result2) $messages[] = "Checked orders.order_number column";
    
    $result3 = ensureColumnExists($conn, "orders", "order_date", "DATETIME DEFAULT CURRENT_TIMESTAMP", "payment_method");
    if ($result3) $messages[] = "Checked orders.order_date column";
    
    // Check order_items table columns
    $result4 = ensureColumnExists($conn, "order_items", "product_id", "INT", "order_id");
    if ($result4) $messages[] = "Checked order_items.product_id column";
    
    $result5 = ensureColumnExists($conn, "order_items", "image", "VARCHAR(255) NULL", "price");
    if ($result5) $messages[] = "Checked order_items.image column";
    
    return $messages;
}
?> 