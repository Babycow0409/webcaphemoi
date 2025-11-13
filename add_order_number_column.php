<?php
include 'includes/db_connect.php';

// Thêm cột order_number vào bảng orders nếu chưa tồn tại
$sql = "SHOW COLUMNS FROM orders LIKE 'order_number'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Cột chưa tồn tại, thêm vào
    $sql = "ALTER TABLE orders ADD COLUMN order_number VARCHAR(30) UNIQUE AFTER id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Đã thêm cột order_number vào bảng orders thành công<br>";
        
        // Cập nhật giá trị order_number cho các đơn hàng hiện có
        $sql = "SELECT id, created_at FROM orders WHERE order_number IS NULL";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $order_id = $row['id'];
                $created_at = $row['created_at'];
                $new_order_number = 'ORDER' . date('YmdHis', strtotime($created_at)) . rand(100, 999);
                
                $stmt = $conn->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
                $stmt->bind_param("si", $new_order_number, $order_id);
                $stmt->execute();
                
                echo "Đã cập nhật order_number cho đơn hàng #$order_id<br>";
            }
        }
    } else {
        echo "Lỗi khi thêm cột: " . $conn->error;
    }
} else {
    echo "Cột order_number đã tồn tại trong bảng orders<br>";
}

// Sửa lại file my-orders.php
$file_path = 'my-orders.php';
$file_content = file_get_contents($file_path);

// Nếu đoạn code cập nhật order_number vẫn còn, đưa nó vào điều kiện kiểm tra tồn tại cột
$search_str = "// Kiểm tra và cập nhật order_number cho đơn hàng nếu chưa có
foreach (\$orders as &\$order) {
    if (empty(\$order['order_number'])) {
        \$order_id = \$order['id'];
        \$new_order_number = 'ORDER' . date('YmdHis', strtotime(\$order['created_at'])) . rand(100, 999);
        
        \$stmt = \$conn->prepare(\"UPDATE orders SET order_number = ? WHERE id = ?\");
        \$stmt->bind_param(\"si\", \$new_order_number, \$order_id);
        \$stmt->execute();
        
        \$order['order_number'] = \$new_order_number;
    }
}";

$replace_str = "// Kiểm tra và cập nhật order_number cho đơn hàng nếu chưa có
\$hasOrderNumberColumn = true;
\$checkColumnSql = \"SHOW COLUMNS FROM orders LIKE 'order_number'\";
\$columnResult = \$conn->query(\$checkColumnSql);
if (\$columnResult->num_rows > 0) {
    foreach (\$orders as &\$order) {
        if (empty(\$order['order_number'])) {
            \$order_id = \$order['id'];
            \$new_order_number = 'ORDER' . date('YmdHis', strtotime(\$order['created_at'])) . rand(100, 999);
            
            \$stmt = \$conn->prepare(\"UPDATE orders SET order_number = ? WHERE id = ?\");
            \$stmt->bind_param(\"si\", \$new_order_number, \$order_id);
            \$stmt->execute();
            
            \$order['order_number'] = \$new_order_number;
        }
    }
} else {
    // Không tìm thấy cột order_number
    \$hasOrderNumberColumn = false;
}";

$new_content = str_replace($search_str, $replace_str, $file_content);

// Cập nhật file
if ($new_content != $file_content) {
    file_put_contents($file_path, $new_content);
    echo "Đã cập nhật file $file_path thành công";
} else {
    echo "Không cần cập nhật file $file_path";
}

echo "<br><br><a href='my-orders.php'>Quay lại trang đơn hàng</a>";
?> 