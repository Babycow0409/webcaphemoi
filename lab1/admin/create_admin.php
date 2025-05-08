<?php
// Kết nối CSDL
$host = "localhost";
$username = "root"; 
$password = "";
$database = "coffee_shop";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Hiển thị cấu trúc bảng users
echo "<h3>Cấu trúc bảng users hiện tại:</h3>";
$sql_show = "DESCRIBE users";
$result_show = $conn->query($sql_show);
if ($result_show->num_rows > 0) {
    echo "<table border='1'><tr><th>Cột</th><th>Kiểu dữ liệu</th></tr>";
    while($row = $result_show->fetch_assoc()) {
        echo "<tr><td>".$row["Field"]."</td><td>".$row["Type"]."</td></tr>";
    }
    echo "</table><br><br>";
} else {
    echo "Không thể lấy thông tin cấu trúc bảng users<br><br>";
}

// Kiểm tra xem có cột role trong bảng users không
$sql_check_role = "SHOW COLUMNS FROM `users` LIKE 'role'";
$result_check_role = $conn->query($sql_check_role);

// Nếu chưa có cột role, thêm cột này vào bảng
if ($result_check_role->num_rows === 0) {
    $sql_add_role = "ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'customer'";
    if ($conn->query($sql_add_role) === TRUE) {
        echo "Đã thêm cột 'role' vào bảng users.<br>";
    } else {
        echo "Lỗi khi thêm cột 'role': " . $conn->error . "<br>";
    }
}

// Kiểm tra xem có cột username không
$sql_check_username = "SHOW COLUMNS FROM `users` LIKE 'username'";
$result_check_username = $conn->query($sql_check_username);

// Nếu chưa có cột username, thêm vào
if ($result_check_username->num_rows === 0) {
    $sql_add_username = "ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) UNIQUE";
    if ($conn->query($sql_add_username) === TRUE) {
        echo "Đã thêm cột 'username' vào bảng users.<br>";
        // Cập nhật username cho các người dùng hiện có
        $sql_update_username = "UPDATE users SET username = CONCAT('user', id) WHERE username IS NULL";
        if ($conn->query($sql_update_username) === TRUE) {
            echo "Đã cập nhật username cho người dùng hiện có.<br>";
        }
    } else {
        echo "Lỗi khi thêm cột 'username': " . $conn->error . "<br>";
    }
}

// Tạo tài khoản admin
$name = "Admin";
$username = "admin";
$email = "admin@caphedamda.com";
$plain_password = "admin123"; // Mật khẩu: admin123
$password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
$role = "admin";

// Kiểm tra xem username hoặc email đã tồn tại chưa
$sql_check = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ss", $username, $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Username hoặc email đã tồn tại, cập nhật thành admin
    $user = $result_check->fetch_assoc();
    $sql_update = "UPDATE users SET role = 'admin', name = ?, password = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $name, $password_hash, $user['id']);
    
    if ($stmt_update->execute()) {
        echo "Đã cập nhật người dùng '{$user['email']}' thành quyền admin.<br>";
    } else {
        echo "Lỗi khi cập nhật quyền admin: " . $stmt_update->error . "<br>";
    }
} else {
    // Chưa có tài khoản, tạo mới
    // Kiểm tra xem bảng users có cột username không
    $has_username = ($result_check_username->num_rows > 0);
    
    if ($has_username) {
        $sql_insert = "INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssss", $name, $username, $email, $password_hash, $role);
    } else {
        $sql_insert = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssss", $name, $email, $password_hash, $role);
    }
    
    if ($stmt_insert->execute()) {
        echo "Đã tạo tài khoản admin thành công.<br>";
        echo "Username: {$username}<br>";
        echo "Email: {$email}<br>";
        echo "Mật khẩu: {$plain_password}<br>";
    } else {
        echo "Lỗi khi tạo tài khoản admin: " . $stmt_insert->error . "<br>";
    }
}

echo "<br>Bạn có thể <a href='login.php'>đăng nhập</a> với tài khoản admin vừa tạo.";

$conn->close();
?> 