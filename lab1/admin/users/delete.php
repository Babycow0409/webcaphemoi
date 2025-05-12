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

// Kiểm tra xem có id được truyền vào không
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Kiểm tra người dùng có tồn tại và không phải là admin
    $sql = "SELECT id, username, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = "Không tìm thấy người dùng với ID: $user_id";
    } else {
        $user = $result->fetch_assoc();
        
        // Không cho phép xóa tài khoản admin
        if ($user['role'] == 'admin') {
            $error = "Không thể xóa tài khoản quản trị viên.";
        } else {
            // Xóa thông tin liên quan đến người dùng trước
            
            // 1. Xóa địa chỉ của người dùng (nếu có bảng addresses)
            $sql_delete_addresses = "DELETE FROM addresses WHERE user_id = ?";
            $stmt_addresses = $conn->prepare($sql_delete_addresses);
            if ($stmt_addresses) {
                $stmt_addresses->bind_param("i", $user_id);
                $stmt_addresses->execute();
                $stmt_addresses->close();
            }
            
            // 2. Xóa chi tiết người dùng (nếu có bảng user_details)
            $sql_delete_details = "DELETE FROM user_details WHERE user_id = ?";
            $stmt_details = $conn->prepare($sql_delete_details);
            if ($stmt_details) {
                $stmt_details->bind_param("i", $user_id);
                $stmt_details->execute();
                $stmt_details->close();
            }
            
            // 3. Xóa người dùng
            $sql_delete_user = "DELETE FROM users WHERE id = ?";
            $stmt_user = $conn->prepare($sql_delete_user);
            $stmt_user->bind_param("i", $user_id);
            
            if ($stmt_user->execute()) {
                $message = "Đã xóa người dùng thành công.";
            } else {
                $error = "Lỗi khi xóa người dùng: " . $conn->error;
            }
            
            $stmt_user->close();
        }
    }
    
    $stmt->close();
} else {
    $error = "ID người dùng không hợp lệ.";
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