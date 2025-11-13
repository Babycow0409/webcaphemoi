<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

// Thông báo
$message = '';
$error = '';

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem cột active đã tồn tại trong bảng users chưa
$column_exists = false;
$check_column = "SHOW COLUMNS FROM users LIKE 'active'";
$column_result = $conn->query($check_column);

if ($column_result->num_rows > 0) {
    $column_exists = true;
    $message = "Cột 'active' đã tồn tại trong bảng users.";
} else {
    // Thêm cột active
    $add_column = "ALTER TABLE users ADD active TINYINT(1) NOT NULL DEFAULT 1";
    
    if ($conn->query($add_column) === TRUE) {
        $message = "Đã thêm cột 'active' vào bảng users thành công.";
        
        // Cập nhật giá trị mặc định cho tất cả người dùng
        $update_values = "UPDATE users SET active = 1";
        if ($conn->query($update_values) === TRUE) {
            $message .= " Đã thiết lập giá trị mặc định cho tất cả người dùng.";
        } else {
            $error = "Lỗi khi cập nhật giá trị mặc định: " . $conn->error;
        }
    } else {
        $error = "Lỗi khi thêm cột 'active': " . $conn->error;
    }
}

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm cột Active - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
        padding: 50px;
        max-width: 800px;
        margin: 0 auto;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Thêm cột Active vào bảng Users</h2>

        <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <p>Cột <code>active</code> dùng để xác định trạng thái hoạt động của tài khoản người dùng.</p>
        <p>Giá trị:</p>
        <ul>
            <li><strong>1</strong>: Tài khoản đang hoạt động (mặc định)</li>
            <li><strong>0</strong>: Tài khoản bị khóa</li>
        </ul>

        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Quay lại danh sách người dùng</a>
        </div>
    </div>
</body>

</html>