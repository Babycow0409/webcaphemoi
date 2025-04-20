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

// Kiểm tra cấu trúc bảng users
echo "<h2>Cấu trúc bảng users hiện tại:</h2>";
$sql_describe = "DESCRIBE users";
$result_describe = $conn->query($sql_describe);

if ($result_describe) {
    echo "<table border='1' cellpadding='5'><tr><th>Cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result_describe->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table><br><br>";
} else {
    echo "Không thể lấy cấu trúc bảng: " . $conn->error . "<br>";
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

// Kiểm tra xem trong bảng users có cột nào để lưu tên không
$has_fullname = false;
$has_name = false;
$name_column = "";

$sql_check_fullname = "SHOW COLUMNS FROM `users` LIKE 'fullname'";
$result_check_fullname = $conn->query($sql_check_fullname);
if ($result_check_fullname->num_rows > 0) {
    $has_fullname = true;
    $name_column = "fullname";
}

$sql_check_name = "SHOW COLUMNS FROM `users` LIKE 'name'";
$result_check_name = $conn->query($sql_check_name);
if ($result_check_name->num_rows > 0) {
    $has_name = true;
    $name_column = "name";
}

// Tạo tài khoản admin dựa trên cấu trúc bảng
if ($has_name || $has_fullname) {
    // Nếu có cột name hoặc fullname
    $query = "INSERT INTO users (email, password, role, $name_column) 
              VALUES ('admin@caphedamda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin')";
} else {
    // Nếu không có cột name
    $query = "INSERT INTO users (email, password, role) 
              VALUES ('admin@caphedamda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')";
}

if ($conn->query($query)) {
    echo "Đã tạo tài khoản admin thành công: <br>";
    echo "Email: admin@caphedamda.com<br>";
    echo "Mật khẩu: password<br>";
} else {
    // Nếu tạo không thành công, có thể là do email đã tồn tại
    echo "Không thể tạo tài khoản admin mới: " . $conn->error . "<br>";
    
    // Thử cập nhật tài khoản admin hiện có
    if ($has_name || $has_fullname) {
        $query = "UPDATE users SET role = 'admin', password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', $name_column = 'Admin' 
                  WHERE email = 'admin@caphedamda.com'";
    } else {
        $query = "UPDATE users SET role = 'admin', password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
                  WHERE email = 'admin@caphedamda.com'";
    }
    
    if ($conn->query($query)) {
        echo "Đã cập nhật tài khoản admin hiện có:<br>";
        echo "Email: admin@caphedamda.com<br>";
        echo "Mật khẩu: password<br>";
    } else {
        echo "Lỗi khi cập nhật tài khoản admin: " . $conn->error . "<br>";
        
        // Hiển thị danh sách các cột trong bảng users
        echo "<h3>Danh sách các cột trong bảng users:</h3>";
        $sql_columns = "SHOW COLUMNS FROM users";
        $result_columns = $conn->query($sql_columns);
        
        if ($result_columns && $result_columns->num_rows > 0) {
            echo "<ul>";
            while ($column = $result_columns->fetch_assoc()) {
                echo "<li>" . $column['Field'] . "</li>";
            }
            echo "</ul>";
        }
        
        // Tạo câu lệnh SQL trực tiếp cho admin để họ sửa
        echo "<h3>Hãy chạy câu lệnh SQL sau trong phpMyAdmin để tạo tài khoản admin:</h3>";
        echo "<pre>INSERT INTO users (email, password, role) VALUES ('admin@caphedamda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');</pre>";
    }
}

echo "<br><a href='login.php'>Đăng nhập ngay</a>";
?> 