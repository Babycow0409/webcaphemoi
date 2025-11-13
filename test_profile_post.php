<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// In ra POST data nếu có
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    echo "POST Data:\n";
    print_r($_POST);
    echo "\n\nUser ID: " . $user_id;
    echo "\n</pre>";
    
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    echo "<p>Parsed Data:</p>";
    echo "<ul>";
    echo "<li>fullname: " . ($fullname ? "'$fullname'" : "(empty)") . "</li>";
    echo "<li>email: " . ($email ? "'$email'" : "(empty)") . "</li>";
    echo "<li>phone: " . ($phone ? "'$phone'" : "(empty)") . "</li>";
    echo "</ul>";
    
    // Test UPDATE
    if (!empty($fullname) && !empty($email) && !empty($phone)) {
        echo "<p style='color: green;'><strong>✓ All fields have data - attempting update...</strong></p>";
        
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        if (!$stmt) {
            echo "<p style='color: red;'><strong>✗ Prepare failed: " . $conn->error . "</strong></p>";
        } else {
            $stmt->bind_param("sssi", $fullname, $email, $phone, $user_id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'><strong>✓ Update successful! Affected rows: " . $stmt->affected_rows . "</strong></p>";
                echo "<p><a href='profile.php'>Back to profile</a></p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Execute failed: " . $conn->error . "</strong></p>";
            }
            $stmt->close();
        }
    } else {
        echo "<p style='color: red;'><strong>✗ Some fields are empty - update skipped</strong></p>";
    }
} else {
    echo "<p>This is a POST-only page. Send a POST request.</p>";
}

$conn->close();
?>
