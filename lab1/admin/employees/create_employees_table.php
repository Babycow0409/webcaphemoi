<?php
// Script tạo bảng employees trong database
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

// Tạo bảng employees
$sql = "CREATE TABLE IF NOT EXISTS employees (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    position VARCHAR(100),
    department VARCHAR(100),
    salary DECIMAL(10,2),
    hire_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng employees đã được tạo thành công!<br>";
    
    // Thêm một số dữ liệu mẫu
    $sample_data = "INSERT INTO employees (employee_code, fullname, email, phone, address, city, position, department, salary, hire_date, status) VALUES
    ('NV001', 'Nguyễn Văn A', 'nguyenvana@example.com', '0901234567', '123 Nguyễn Huệ', 'TP.HCM', 'Nhân viên pha chế', 'Sản xuất', 8000000, '2024-01-15', 'active'),
    ('NV002', 'Trần Thị B', 'tranthib@example.com', '0912345678', '456 Lê Lợi', 'TP.HCM', 'Thu ngân', 'Bán hàng', 7000000, '2024-02-20', 'active'),
    ('NV003', 'Lê Văn C', 'levanc@example.com', '0923456789', '789 Trần Hưng Đạo', 'Hà Nội', 'Quản lý ca', 'Quản lý', 12000000, '2023-12-01', 'active')";
    
    if ($conn->query($sample_data) === TRUE) {
        echo "Đã thêm dữ liệu mẫu thành công!<br>";
    } else {
        echo "Lỗi khi thêm dữ liệu mẫu: " . $conn->error . "<br>";
    }
} else {
    echo "Lỗi khi tạo bảng: " . $conn->error . "<br>";
}

$conn->close();

echo "<br><a href='index.php'>Quay lại danh sách nhân viên</a>";
?>

