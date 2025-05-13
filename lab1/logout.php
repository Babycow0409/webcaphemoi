<?php
session_start();

// Save cart data to clear it from localStorage
$clearCart = true;

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

if ($clearCart) {
    // Add JavaScript to clear cart from localStorage
    echo '<script>
        localStorage.removeItem("cart");
        window.location.href = "index.php";
    </script>';
} else {
    // Redirect to login page
    header("Location: index.php");
    exit;
}
?> 