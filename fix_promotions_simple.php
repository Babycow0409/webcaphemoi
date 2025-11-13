<?php
/**
 * Script đơn giản để sửa lỗi bảng promotions
 * Truy cập: http://localhost/webcaphe/lab1/fix_promotions_simple.php
 */

require_once 'includes/db_connect.php';

$conn->set_charset("utf8mb4");

echo "<h2>Sửa lỗi bảng promotions</h2>";

// Kiểm tra bảng có tồn tại không
$checkTable = $conn->query("SHOW TABLES LIKE 'promotions'");

if ($checkTable->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ Bảng promotions đã tồn tại. Đang kiểm tra và sửa lỗi...</p>";
    
    // Xóa bảng cũ (CẨN THẬN: sẽ mất dữ liệu)
    // Uncomment dòng dưới nếu muốn xóa và tạo lại
    // $conn->query("DROP TABLE IF EXISTS `promotion_usage`");
    // $conn->query("DROP TABLE IF EXISTS `promotions`");
    // echo "<p style='color: red;'>Đã xóa bảng cũ</p>";
    
    // Hoặc chỉ sửa constraint
    try {
        // Kiểm tra xem có UNIQUE constraint chưa
        $checkIndex = $conn->query("SHOW INDEX FROM `promotions` WHERE Key_name = 'code' AND Non_unique = 0");
        if ($checkIndex->num_rows == 0) {
            // Thêm UNIQUE constraint
            $sql = "ALTER TABLE `promotions` ADD UNIQUE KEY `code` (`code`)";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✅ Đã thêm UNIQUE constraint cho cột code</p>";
            } else {
                echo "<p style='color: red;'>❌ Lỗi: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ UNIQUE constraint đã tồn tại</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
        echo "<p style='color: orange;'>💡 Nếu lỗi do duplicate, bạn có thể cần xóa và tạo lại bảng</p>";
    }
} else {
    echo "<p style='color: blue;'>📝 Bảng chưa tồn tại. Đang tạo mới...</p>";
    
    // Tạo bảng với cú pháp đúng
    $sql = "CREATE TABLE `promotions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `code` varchar(50) NOT NULL COMMENT 'Mã khuyến mãi',
      `name` varchar(255) NOT NULL COMMENT 'Tên chương trình khuyến mãi',
      `description` text DEFAULT NULL COMMENT 'Mô tả',
      `discount_type` enum('percentage','fixed','free_shipping') NOT NULL DEFAULT 'percentage' COMMENT 'Loại giảm giá',
      `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá trị giảm giá',
      `min_order_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Đơn hàng tối thiểu',
      `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'Giảm giá tối đa (nếu là %)',
      `usage_limit` int(11) DEFAULT NULL COMMENT 'Giới hạn số lần sử dụng',
      `used_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lần đã sử dụng',
      `user_limit` int(11) DEFAULT 1 COMMENT 'Giới hạn số lần sử dụng cho mỗi user',
      `start_date` datetime NOT NULL COMMENT 'Ngày bắt đầu',
      `end_date` datetime NOT NULL COMMENT 'Ngày kết thúc',
      `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái',
      `applicable_products` text DEFAULT NULL COMMENT 'Danh sách product_id áp dụng (JSON)',
      `applicable_categories` text DEFAULT NULL COMMENT 'Danh sách category_id áp dụng (JSON)',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `code` (`code`),
      KEY `status` (`status`),
      KEY `start_date` (`start_date`),
      KEY `end_date` (`end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ Bảng promotions đã được tạo thành công!</p>";
    } else {
        echo "<p style='color: red;'>❌ Lỗi khi tạo bảng: " . $conn->error . "</p>";
        echo "<p style='color: orange;'>💡 Chi tiết lỗi SQL: <pre>" . htmlspecialchars($sql) . "</pre></p>";
    }
}

echo "<hr>";
echo "<p><a href='setup_module4.php'>← Quay lại Setup MODULE 4</a></p>";

$conn->close();
?>

