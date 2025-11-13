<?php
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

// Thông tin tài khoản admin
$admin_email = "admin@example.com";
$admin_password = "admin123";
$admin_fullname = "Administrator";
$admin_role = "admin";

// Hash mật khẩu
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Kiểm tra xem bảng users đã tồn tại chưa
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows == 0) {
    // Tạo bảng users nếu chưa tồn tại
    $sql_create_users = "CREATE TABLE users (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'customer',
        fullname VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql_create_users) === TRUE) {
        echo "Đã tạo bảng users<br>";
    } else {
        echo "Lỗi khi tạo bảng users: " . $conn->error . "<br>";
    }
}

// Kiểm tra xem tài khoản admin đã tồn tại chưa
$sql_check_admin = "SELECT * FROM users WHERE email = '$admin_email'";
$result = $conn->query($sql_check_admin);

if ($result->num_rows > 0) {
    // Cập nhật tài khoản admin hiện có
    $sql_update_admin = "UPDATE users SET password = '$hashed_password', fullname = '$admin_fullname', role = '$admin_role' WHERE email = '$admin_email'";
    
    if ($conn->query($sql_update_admin) === TRUE) {
        echo "Tài khoản admin đã được cập nhật:<br>";
        echo "Email: $admin_email<br>";
        echo "Mật khẩu: $admin_password<br>";
        echo "Mật khẩu đã hash: $hashed_password<br>";
    } else {
        echo "Lỗi khi cập nhật tài khoản admin: " . $conn->error;
    }
} else {
    // Tạo tài khoản admin mới
    $sql_insert_admin = "INSERT INTO users (email, password, role, fullname) VALUES ('$admin_email', '$hashed_password', '$admin_role', '$admin_fullname')";
    
    if ($conn->query($sql_insert_admin) === TRUE) {
        echo "Tài khoản admin đã được tạo thành công:<br>";
        echo "Email: $admin_email<br>";
        echo "Mật khẩu: $admin_password<br>";
        echo "Mật khẩu đã hash: $hashed_password<br>";
    } else {
        echo "Lỗi khi tạo tài khoản admin: " . $conn->error;
    }
}

echo "<br><a href='admin/login.php'>Đăng nhập với tài khoản admin</a>";

$conn->close();
?> 