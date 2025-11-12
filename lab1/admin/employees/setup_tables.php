<?php
// Script setup để tạo tất cả các bảng cần thiết cho quản lý nhân viên
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

$conn->set_charset("utf8mb4");

echo "<h2>Thiết lập bảng quản lý nhân viên</h2>";

// 1. Tạo/kiểm tra bảng employees
echo "<h3>1. Kiểm tra bảng employees...</h3>";
$employees_exists = $conn->query("SHOW TABLES LIKE 'employees'")->num_rows > 0;

if (!$employees_exists) {
    $sql = "CREATE TABLE employees (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        employee_code VARCHAR(20) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        address VARCHAR(255),
        city VARCHAR(100),
        position VARCHAR(100),
        department VARCHAR(100),
        salary DECIMAL(10,2),
        hire_date DATE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        role ENUM('employee', 'manager') DEFAULT 'employee',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Đã tạo bảng employees thành công!<br>";
    } else {
        echo "✗ Lỗi khi tạo bảng employees: " . $conn->error . "<br>";
    }
} else {
    // Kiểm tra và thêm cột employee_code nếu chưa có
    $columns = $conn->query("SHOW COLUMNS FROM employees LIKE 'employee_code'");
    if ($columns->num_rows == 0) {
        $conn->query("ALTER TABLE employees ADD COLUMN employee_code VARCHAR(20) UNIQUE NOT NULL AFTER id");
        echo "✓ Đã thêm cột employee_code vào bảng employees<br>";
    }
    
    // Kiểm tra và thêm cột role nếu chưa có
    $columns = $conn->query("SHOW COLUMNS FROM employees LIKE 'role'");
    if ($columns->num_rows == 0) {
        $conn->query("ALTER TABLE employees ADD COLUMN role ENUM('employee', 'manager') DEFAULT 'employee' AFTER status");
        echo "✓ Đã thêm cột role vào bảng employees<br>";
    }
    
    echo "✓ Bảng employees đã tồn tại<br>";
}

// 2. Tạo bảng work_shifts
echo "<h3>2. Kiểm tra bảng work_shifts...</h3>";
$shifts_exists = $conn->query("SHOW TABLES LIKE 'work_shifts'")->num_rows > 0;

if (!$shifts_exists) {
    $sql = "CREATE TABLE work_shifts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        shift_name VARCHAR(50) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        description TEXT DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Đã tạo bảng work_shifts thành công!<br>";
    } else {
        echo "✗ Lỗi khi tạo bảng work_shifts: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Bảng work_shifts đã tồn tại<br>";
}

// 3. Tạo bảng shift_assignments
echo "<h3>3. Kiểm tra bảng shift_assignments...</h3>";
$assignments_exists = $conn->query("SHOW TABLES LIKE 'shift_assignments'")->num_rows > 0;

if (!$assignments_exists) {
    $sql = "CREATE TABLE shift_assignments (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        employee_id INT(11) NOT NULL,
        shift_id INT(11) NOT NULL,
        work_date DATE NOT NULL,
        check_in_time DATETIME DEFAULT NULL,
        check_out_time DATETIME DEFAULT NULL,
        hours_worked DECIMAL(4,2) DEFAULT 0.00,
        status ENUM('scheduled', 'completed', 'absent', 'cancelled') DEFAULT 'scheduled',
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY employee_id (employee_id),
        KEY shift_id (shift_id),
        KEY work_date (work_date),
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        FOREIGN KEY (shift_id) REFERENCES work_shifts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Đã tạo bảng shift_assignments thành công!<br>";
    } else {
        echo "✗ Lỗi khi tạo bảng shift_assignments: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Bảng shift_assignments đã tồn tại<br>";
}

// 4. Tạo bảng salary_calculations
echo "<h3>4. Kiểm tra bảng salary_calculations...</h3>";
$salary_exists = $conn->query("SHOW TABLES LIKE 'salary_calculations'")->num_rows > 0;

if (!$salary_exists) {
    $sql = "CREATE TABLE salary_calculations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        employee_id INT(11) NOT NULL,
        calculation_month VARCHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
        base_salary DECIMAL(10,2) DEFAULT 0.00,
        total_shifts INT(11) DEFAULT 0,
        total_hours DECIMAL(6,2) DEFAULT 0.00,
        shift_earnings DECIMAL(10,2) DEFAULT 0.00,
        bonus DECIMAL(10,2) DEFAULT 0.00,
        deductions DECIMAL(10,2) DEFAULT 0.00,
        total_salary DECIMAL(10,2) DEFAULT 0.00,
        status ENUM('draft', 'calculated', 'paid') DEFAULT 'draft',
        notes TEXT DEFAULT NULL,
        calculated_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY employee_month (employee_id, calculation_month),
        KEY calculation_month (calculation_month),
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Đã tạo bảng salary_calculations thành công!<br>";
    } else {
        echo "✗ Lỗi khi tạo bảng salary_calculations: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Bảng salary_calculations đã tồn tại<br>";
}

echo "<br><h3 style='color: green;'>✓ Hoàn tất thiết lập!</h3>";
echo "<br><a href='index.php' class='btn btn-primary'>Quay lại danh sách nhân viên</a>";

$conn->close();
?>


