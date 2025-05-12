<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION["admin"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Kiểm tra ID người dùng
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$user_id = intval($_POST['id']);

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Kiểm tra xem người dùng có tồn tại không
    $check_sql = "SELECT id FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }

    // Bắt đầu transaction
    $conn->begin_transaction();

    try {
        // Xóa các bản ghi trong bảng order_items liên quan đến orders của user
        $delete_order_items = "DELETE oi FROM order_items oi 
                             INNER JOIN orders o ON oi.order_id = o.id 
                             WHERE o.user_id = ?";
        $stmt_order_items = $conn->prepare($delete_order_items);
        $stmt_order_items->bind_param("i", $user_id);
        $stmt_order_items->execute();

        // Xóa các bản ghi trong bảng orders của user
        $delete_orders = "DELETE FROM orders WHERE user_id = ?";
        $stmt_orders = $conn->prepare($delete_orders);
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();

        // Xóa các bản ghi trong bảng addresses của user
        $delete_addresses = "DELETE FROM addresses WHERE user_id = ?";
        $stmt_addresses = $conn->prepare($delete_addresses);
        $stmt_addresses->bind_param("i", $user_id);
        $stmt_addresses->execute();

        // Xóa các bản ghi trong bảng cart_items của user
        $delete_cart_items = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt_cart_items = $conn->prepare($delete_cart_items);
        $stmt_cart_items->bind_param("i", $user_id);
        $stmt_cart_items->execute();

        // Cuối cùng mới xóa user
        $delete_user = "DELETE FROM users WHERE id = ?";
        $stmt_user = $conn->prepare($delete_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();

        // Commit transaction
        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>