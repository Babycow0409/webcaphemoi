<?php
// Test script để kiểm tra cập nhật profile
session_start();
include 'includes/db_connect.php';

// Giả lập user đăng nhập
$_SESSION['user_id'] = 2;
$user_id = 2;

// Test dữ liệu
$fullname = "Test Fullname";
$email = "test@example.com";
$phone = "0123456789";

echo "Testing profile update...\n";
echo "User ID: " . $user_id . "\n";
echo "Fullname: " . $fullname . "\n";
echo "Email: " . $email . "\n";
echo "Phone: " . $phone . "\n\n";

// Test UPDATE
$stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
if (!$stmt) {
    echo "Prepare error: " . $conn->error . "\n";
    exit;
}

$stmt->bind_param("sssi", $fullname, $email, $phone, $user_id);

if ($stmt->execute()) {
    echo "✓ Update successful!\n";
    echo "Rows affected: " . $stmt->affected_rows . "\n\n";
} else {
    echo "✗ Execute error: " . $conn->error . "\n";
    exit;
}

// Test SELECT untuk verify
echo "Verifying data in database:\n";
$verify_stmt = $conn->prepare("SELECT id, fullname, email, phone FROM users WHERE id = ?");
$verify_stmt->bind_param("i", $user_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();
$row = $result->fetch_assoc();

echo "Current data:\n";
echo "- fullname: " . $row['fullname'] . "\n";
echo "- email: " . $row['email'] . "\n";
echo "- phone: " . $row['phone'] . "\n";

if ($row['fullname'] === $fullname && $row['email'] === $email && $row['phone'] === $phone) {
    echo "\n✓ All data matches! Update is working correctly.\n";
} else {
    echo "\n✗ Data does not match! Something is wrong.\n";
}

$stmt->close();
$verify_stmt->close();
$conn->close();
?>
