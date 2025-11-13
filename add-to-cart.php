<?php
// Redirect all requests to process-cart.php
// Get all request parameters and pass them to process-cart.php
$query = $_SERVER['QUERY_STRING'];
if (!empty($query)) {
    // If we have query parameters, make sure to add the action=add if not already present
    if (strpos($query, 'action=') === false) {
        $query .= '&action=add';
    }
    header("Location: process-cart.php?" . $query);
} else {
    header("Location: process-cart.php?action=add");
}
exit;
?> 