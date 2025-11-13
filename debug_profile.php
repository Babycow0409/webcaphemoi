<?php
session_start();
include 'includes/db_connect.php';

// Debug info
echo "<h2>Debug Information</h2>";
echo "<p><strong>Session:</strong></p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'><strong>Not logged in!</strong></p>";
    echo "<p><a href='login.php'>Go to login</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "<p><strong>User ID:</strong> " . $user_id . "</p>";

// Lấy thông tin người dùng
$user_query = $conn->query("SELECT * FROM users WHERE id = " . intval($user_id));
if (!$user_query) {
    echo "<p style='color: red;'><strong>Query error:</strong> " . $conn->error . "</p>";
    exit;
}

$user = $user_query->fetch_assoc();
$user_query->free();

echo "<p><strong>User Data:</strong></p>";
echo "<pre>";
print_r($user);
echo "</pre>";

if ($user === null) {
    echo "<p style='color: red;'><strong>User is NULL!</strong></p>";
} elseif (!isset($user['fullname'])) {
    echo "<p style='color: red;'><strong>fullname field is missing!</strong></p>";
} else {
    echo "<p style='color: green;'><strong>✓ User data loaded successfully</strong></p>";
    echo "<ul>";
    echo "<li>fullname: " . htmlspecialchars($user['fullname']) . "</li>";
    echo "<li>email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>phone: " . htmlspecialchars($user['phone']) . "</li>";
    echo "</ul>";
}

$conn->close();
?>
