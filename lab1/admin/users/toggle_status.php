<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

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

$message = '';
$error = '';

// Kiểm tra xem có id và action được truyền vào không
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['action'])) {
    $user_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Kiểm tra người dùng có tồn tại 
    $sql = "SELECT id, username, role, active FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = "Không tìm thấy người dùng với ID: $user_id";
    } else {
        $user = $result->fetch_assoc();
        
        // Kiểm tra xem cột active đã tồn tại trong bảng users chưa
        $column_exists = false;
        $check_column = "SHOW COLUMNS FROM users LIKE 'active'";
        $column_result = $conn->query($check_column);
        if ($column_result->num_rows > 0) {
            $column_exists = true;
        }
        
        // Nếu cột không tồn tại, thêm cột active
        if (!$column_exists) {
            $add_column = "ALTER TABLE users ADD active TINYINT(1) NOT NULL DEFAULT 1";
            if (!$conn->query($add_column)) {
                $error = "Lỗi khi thêm cột active: " . $conn->error;
                $stmt->close();
                $conn->close();
                header("Location: index.php?error=" . urlencode($error));
                exit();
            }
        }
        
        // Không cho phép khóa tài khoản admin
        if ($user['role'] == 'admin' && $action == 'deactivate') {
            $error = "Không thể khóa tài khoản quản trị viên.";
        } else {
            // Cập nhật trạng thái
            $active_value = ($action == 'activate') ? 1 : 0;
            $action_name = ($action == 'activate') ? 'mở khóa' : 'khóa';
            
            $sql_update = "UPDATE users SET active = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $active_value, $user_id);
            
            if ($stmt_update->execute()) {
                $message = "Đã $action_name tài khoản người dùng thành công.";
            } else {
                $error = "Lỗi khi $action_name tài khoản: " . $conn->error;
            }
            
            $stmt_update->close();
        }
    }
    
    $stmt->close();
} else {
    $error = "Thiếu thông tin cần thiết để thực hiện thao tác.";
}

// Đóng kết nối
$conn->close();

// Chuyển hướng về trang danh sách người dùng
if (!empty($message)) {
    header("Location: index.php?message=" . urlencode($message));
    exit();
} else if (!empty($error)) {
    header("Location: index.php?error=" . urlencode($error));
    exit();
} else {
    header("Location: index.php");
    exit();
}
?> 