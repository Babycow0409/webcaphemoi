<?php
/**
 * Script tự động tạo các bảng và cột cho MODULE 4
 * Truy cập: http://localhost/webcaphe/lab1/setup_module4.php
 */

require_once 'includes/db_connect.php';

// Đặt encoding
$conn->set_charset("utf8mb4");

echo "<!DOCTYPE html>";
echo "<html lang='vi'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Setup MODULE 4 - Database</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #333; border-bottom: 3px solid #d4a373; padding-bottom: 10px; }
    .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .step { margin: 15px 0; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff; }
    .step-title { font-weight: bold; color: #007bff; }
</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🚀 Setup MODULE 4 - Database</h1>";

$errors = [];
$success = [];

// Hàm kiểm tra cột có tồn tại không
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result->num_rows > 0;
}

// Hàm kiểm tra bảng có tồn tại không
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}

// Hàm kiểm tra index có tồn tại không
function indexExists($conn, $table, $index) {
    $result = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$index'");
    return $result->num_rows > 0;
}

// Hàm kiểm tra foreign key constraint có tồn tại không
function foreignKeyExists($conn, $table, $constraintName) {
    $dbName = $conn->query("SELECT DATABASE()")->fetch_row()[0];
    $result = $conn->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '$dbName' 
        AND TABLE_NAME = '$table' 
        AND CONSTRAINT_NAME = '$constraintName'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    return $result->num_rows > 0;
}

// Hàm xóa foreign key constraint
function dropForeignKey($conn, $table, $constraintName) {
    try {
        $sql = "ALTER TABLE `$table` DROP FOREIGN KEY `$constraintName`";
        return $conn->query($sql);
    } catch (Exception $e) {
        return false;
    }
}

// Hàm thực thi query và xử lý lỗi
function executeQuery($conn, $sql, $description) {
    global $errors, $success;
    try {
        if ($conn->query($sql)) {
            $success[] = "✅ $description";
            return true;
        } else {
            $errors[] = "❌ $description: " . $conn->error;
            return false;
        }
    } catch (Exception $e) {
        $errors[] = "❌ $description: " . $e->getMessage();
        return false;
    }
}

echo "<div class='info'>Đang kiểm tra và tạo các bảng, cột cho MODULE 4...</div>";

// 1. Tạo bảng loyalty_points
echo "<div class='step'>";
echo "<div class='step-title'>Bước 1: Tạo bảng loyalty_points</div>";

if (!tableExists($conn, 'loyalty_points')) {
    $sql = "CREATE TABLE `loyalty_points` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `points` int(11) NOT NULL DEFAULT 0 COMMENT 'Số điểm tích lũy',
      `points_used` int(11) NOT NULL DEFAULT 0 COMMENT 'Số điểm đã sử dụng',
      `points_available` int(11) NOT NULL DEFAULT 0 COMMENT 'Số điểm còn lại',
      `order_id` int(11) DEFAULT NULL COMMENT 'ID đơn hàng',
      `transaction_type` enum('earned','used','expired','bonus') NOT NULL DEFAULT 'earned' COMMENT 'Loại giao dịch',
      `description` text DEFAULT NULL COMMENT 'Mô tả giao dịch',
      `expiry_date` date DEFAULT NULL COMMENT 'Ngày hết hạn điểm',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    executeQuery($conn, $sql, "Tạo bảng loyalty_points");
    
    // Thêm foreign key sau khi tạo bảng
    if (!indexExists($conn, 'loyalty_points', 'loyalty_points_ibfk_1')) {
        $sql = "ALTER TABLE `loyalty_points` 
                ADD CONSTRAINT `loyalty_points_ibfk_1` 
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE";
        executeQuery($conn, $sql, "Thêm foreign key user_id cho loyalty_points");
    }
    
    if (!indexExists($conn, 'loyalty_points', 'loyalty_points_ibfk_2')) {
        $sql = "ALTER TABLE `loyalty_points` 
                ADD CONSTRAINT `loyalty_points_ibfk_2` 
                FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL";
        executeQuery($conn, $sql, "Thêm foreign key order_id cho loyalty_points");
    }
} else {
    $success[] = "✅ Bảng loyalty_points đã tồn tại";
}
echo "</div>";

// 2. Tạo bảng promotions
echo "<div class='step'>";
echo "<div class='step-title'>Bước 2: Tạo bảng promotions</div>";

if (!tableExists($conn, 'promotions')) {
    // Tạo bảng promotions - đã sửa lỗi UNIQUE
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
    
    if (executeQuery($conn, $sql, "Tạo bảng promotions")) {
        $success[] = "✅ Bảng promotions đã được tạo thành công";
    }
} else {
    $success[] = "✅ Bảng promotions đã tồn tại";
    
    // Kiểm tra và sửa lỗi nếu bảng đã tồn tại nhưng thiếu UNIQUE constraint
    $checkUnique = $conn->query("SHOW INDEX FROM `promotions` WHERE Key_name = 'code' AND Non_unique = 0");
    if ($checkUnique->num_rows == 0) {
        // Nếu chưa có UNIQUE constraint, thêm vào
        $sql = "ALTER TABLE `promotions` ADD UNIQUE KEY `code` (`code`)";
        executeQuery($conn, $sql, "Thêm UNIQUE constraint cho cột code");
    }
}
echo "</div>";

// 3. Tạo bảng promotion_usage
echo "<div class='step'>";
echo "<div class='step-title'>Bước 3: Tạo bảng promotion_usage</div>";

if (!tableExists($conn, 'promotion_usage')) {
    $sql = "CREATE TABLE `promotion_usage` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `promotion_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `order_id` int(11) NOT NULL,
      `discount_amount` decimal(10,2) NOT NULL,
      `order_amount` decimal(10,2) NOT NULL,
      `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `promotion_id` (`promotion_id`),
      KEY `user_id` (`user_id`),
      KEY `order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    executeQuery($conn, $sql, "Tạo bảng promotion_usage");
    
    // Thêm foreign keys
    if (!indexExists($conn, 'promotion_usage', 'promotion_usage_ibfk_1')) {
        $sql = "ALTER TABLE `promotion_usage` 
                ADD CONSTRAINT `promotion_usage_ibfk_1` 
                FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE";
        executeQuery($conn, $sql, "Thêm foreign key promotion_id");
    }
    
    if (!indexExists($conn, 'promotion_usage', 'promotion_usage_ibfk_2')) {
        $sql = "ALTER TABLE `promotion_usage` 
                ADD CONSTRAINT `promotion_usage_ibfk_2` 
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE";
        executeQuery($conn, $sql, "Thêm foreign key user_id");
    }
    
    if (!indexExists($conn, 'promotion_usage', 'promotion_usage_ibfk_3')) {
        $sql = "ALTER TABLE `promotion_usage` 
                ADD CONSTRAINT `promotion_usage_ibfk_3` 
                FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE";
        executeQuery($conn, $sql, "Thêm foreign key order_id");
    }
} else {
    $success[] = "✅ Bảng promotion_usage đã tồn tại";
}
echo "</div>";

// 4. Tạo bảng sales_reports
echo "<div class='step'>";
echo "<div class='step-title'>Bước 4: Tạo bảng sales_reports</div>";

if (!tableExists($conn, 'sales_reports')) {
    $sql = "CREATE TABLE `sales_reports` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `report_date` date NOT NULL,
      `report_type` enum('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'daily',
      `total_orders` int(11) NOT NULL DEFAULT 0,
      `total_revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
      `total_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
      `total_shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
      `net_revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
      `total_customers` int(11) NOT NULL DEFAULT 0,
      `new_customers` int(11) NOT NULL DEFAULT 0,
      `top_product_id` int(11) DEFAULT NULL,
      `top_product_name` varchar(255) DEFAULT NULL,
      `top_product_quantity` int(11) DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_report` (`report_date`,`report_type`),
      KEY `report_date` (`report_date`),
      KEY `report_type` (`report_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    executeQuery($conn, $sql, "Tạo bảng sales_reports");
} else {
    $success[] = "✅ Bảng sales_reports đã tồn tại";
}
echo "</div>";

// 5. Thêm cột vào bảng orders
echo "<div class='step'>";
echo "<div class='step-title'>Bước 5: Thêm cột vào bảng orders</div>";

if (!columnExists($conn, 'orders', 'promotion_id')) {
    $sql = "ALTER TABLE `orders` ADD COLUMN `promotion_id` int(11) DEFAULT NULL COMMENT 'ID khuyến mãi áp dụng'";
    executeQuery($conn, $sql, "Thêm cột promotion_id vào orders");
} else {
    $success[] = "✅ Cột promotion_id đã tồn tại";
}

if (!columnExists($conn, 'orders', 'discount_amount')) {
    $sql = "ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Số tiền giảm giá'";
    executeQuery($conn, $sql, "Thêm cột discount_amount vào orders");
} else {
    $success[] = "✅ Cột discount_amount đã tồn tại";
}

if (!columnExists($conn, 'orders', 'points_used')) {
    $sql = "ALTER TABLE `orders` ADD COLUMN `points_used` int(11) DEFAULT 0 COMMENT 'Số điểm đã sử dụng'";
    executeQuery($conn, $sql, "Thêm cột points_used vào orders");
} else {
    $success[] = "✅ Cột points_used đã tồn tại";
}

if (!columnExists($conn, 'orders', 'points_earned')) {
    $sql = "ALTER TABLE `orders` ADD COLUMN `points_earned` int(11) DEFAULT 0 COMMENT 'Số điểm tích lũy'";
    executeQuery($conn, $sql, "Thêm cột points_earned vào orders");
} else {
    $success[] = "✅ Cột points_earned đã tồn tại";
}

// Thêm index và foreign key cho promotion_id
if (columnExists($conn, 'orders', 'promotion_id')) {
    // Thêm index nếu chưa có
    if (!indexExists($conn, 'orders', 'promotion_id')) {
        $sql = "ALTER TABLE `orders` ADD KEY `promotion_id` (`promotion_id`)";
        executeQuery($conn, $sql, "Thêm index promotion_id");
    } else {
        $success[] = "✅ Index promotion_id đã tồn tại";
    }
    
    // Kiểm tra xem bảng promotions có tồn tại không
    if (tableExists($conn, 'promotions')) {
        // Kiểm tra foreign key constraint
        if (!foreignKeyExists($conn, 'orders', 'orders_ibfk_promotion')) {
            // Xóa constraint cũ nếu có (phòng trường hợp bị lỗi)
            $checkOld = $conn->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'orders' 
                AND COLUMN_NAME = 'promotion_id'
                AND REFERENCED_TABLE_NAME = 'promotions'
            ");
            
            if ($checkOld->num_rows > 0) {
                $oldConstraint = $checkOld->fetch_assoc();
                $oldName = $oldConstraint['CONSTRAINT_NAME'];
                if ($oldName != 'orders_ibfk_promotion') {
                    dropForeignKey($conn, 'orders', $oldName);
                    $success[] = "✅ Đã xóa constraint cũ: $oldName";
                }
            }
            
            // Thêm foreign key constraint mới
            $sql = "ALTER TABLE `orders` 
                    ADD CONSTRAINT `orders_ibfk_promotion` 
                    FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL";
            executeQuery($conn, $sql, "Thêm foreign key promotion_id");
        } else {
            $success[] = "✅ Foreign key orders_ibfk_promotion đã tồn tại";
        }
    } else {
        $errors[] = "⚠️ Bảng promotions chưa tồn tại, không thể thêm foreign key. Hãy tạo bảng promotions trước.";
    }
}
echo "</div>";

// 6. Thêm cột vào bảng users
echo "<div class='step'>";
echo "<div class='step-title'>Bước 6: Thêm cột vào bảng users</div>";

if (!columnExists($conn, 'users', 'total_points')) {
    $sql = "ALTER TABLE `users` ADD COLUMN `total_points` int(11) NOT NULL DEFAULT 0 COMMENT 'Tổng điểm tích lũy'";
    executeQuery($conn, $sql, "Thêm cột total_points vào users");
} else {
    $success[] = "✅ Cột total_points đã tồn tại";
}

if (!columnExists($conn, 'users', 'customer_level')) {
    $sql = "ALTER TABLE `users` ADD COLUMN `customer_level` enum('bronze','silver','gold','platinum') NOT NULL DEFAULT 'bronze' COMMENT 'Cấp độ khách hàng'";
    executeQuery($conn, $sql, "Thêm cột customer_level vào users");
} else {
    $success[] = "✅ Cột customer_level đã tồn tại";
}

if (!columnExists($conn, 'users', 'total_spent')) {
    $sql = "ALTER TABLE `users` ADD COLUMN `total_spent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền đã chi tiêu'";
    executeQuery($conn, $sql, "Thêm cột total_spent vào users");
} else {
    $success[] = "✅ Cột total_spent đã tồn tại";
}

if (!columnExists($conn, 'users', 'last_order_date')) {
    $sql = "ALTER TABLE `users` ADD COLUMN `last_order_date` datetime DEFAULT NULL COMMENT 'Ngày đơn hàng cuối cùng'";
    executeQuery($conn, $sql, "Thêm cột last_order_date vào users");
} else {
    $success[] = "✅ Cột last_order_date đã tồn tại";
}

// Thêm index
if (columnExists($conn, 'users', 'total_spent') && !indexExists($conn, 'users', 'idx_users_total_spent')) {
    $sql = "ALTER TABLE `users` ADD INDEX `idx_users_total_spent` (`total_spent`)";
    executeQuery($conn, $sql, "Thêm index idx_users_total_spent");
}
echo "</div>";

// 7. Thêm index cho các bảng
echo "<div class='step'>";
echo "<div class='step-title'>Bước 7: Thêm index cho hiệu năng</div>";

if (tableExists($conn, 'orders') && !indexExists($conn, 'orders', 'idx_orders_promotion')) {
    $sql = "ALTER TABLE `orders` ADD INDEX `idx_orders_promotion` (`promotion_id`)";
    executeQuery($conn, $sql, "Thêm index idx_orders_promotion");
}

if (tableExists($conn, 'loyalty_points') && !indexExists($conn, 'loyalty_points', 'idx_loyalty_points_user')) {
    $sql = "ALTER TABLE `loyalty_points` ADD INDEX `idx_loyalty_points_user` (`user_id`, `transaction_type`)";
    executeQuery($conn, $sql, "Thêm index idx_loyalty_points_user");
}

if (tableExists($conn, 'promotions') && !indexExists($conn, 'promotions', 'idx_promotions_active')) {
    $sql = "ALTER TABLE `promotions` ADD INDEX `idx_promotions_active` (`status`, `start_date`, `end_date`)";
    executeQuery($conn, $sql, "Thêm index idx_promotions_active");
}
echo "</div>";

// 8. Insert dữ liệu mẫu
echo "<div class='step'>";
echo "<div class='step-title'>Bước 8: Thêm dữ liệu mẫu (promotions)</div>";

$promotions = [
    ['WELCOME10', 'Chào mừng khách hàng mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10.00, 100000.00, 50000.00, 100],
    ['FREESHIP', 'Miễn phí vận chuyển', 'Miễn phí vận chuyển cho đơn hàng trên 300.000đ', 'free_shipping', 0.00, 300000.00, NULL, NULL],
    ['SALE20', 'Giảm giá 20%', 'Giảm 20% cho đơn hàng trên 500.000đ', 'percentage', 20.00, 500000.00, 200000.00, NULL]
];

foreach ($promotions as $promo) {
    $code = $promo[0];
    $check = $conn->query("SELECT id FROM promotions WHERE code = '$code'");
    
    if ($check->num_rows == 0) {
        $name = $conn->real_escape_string($promo[1]);
        $desc = $conn->real_escape_string($promo[2]);
        $type = $promo[3];
        $value = $promo[4];
        $min = $promo[5];
        $max = $promo[6] !== NULL ? $promo[6] : 'NULL';
        $limit = $promo[7] !== NULL ? $promo[7] : 'NULL';
        
        $sql = "INSERT INTO `promotions` 
                (`code`, `name`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `start_date`, `end_date`, `status`) 
                VALUES 
                ('$code', '$name', '$desc', '$type', $value, $min, $max, $limit, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'active')";
        
        executeQuery($conn, $sql, "Thêm mã khuyến mãi: $code");
    } else {
        $success[] = "✅ Mã khuyến mãi $code đã tồn tại";
    }
}
echo "</div>";

// Hiển thị kết quả
echo "<h2>Kết quả:</h2>";

if (!empty($success)) {
    foreach ($success as $msg) {
        echo "<div class='success'>$msg</div>";
    }
}

if (!empty($errors)) {
    echo "<h2>Lỗi:</h2>";
    foreach ($errors as $msg) {
        echo "<div class='error'>$msg</div>";
    }
} else {
    echo "<div class='success'><strong>🎉 Hoàn thành! Tất cả các bảng và cột đã được tạo thành công!</strong></div>";
}

echo "<div class='info'>";
echo "<h3>📋 Kiểm tra lại:</h3>";
echo "<ul>";
echo "<li><a href='admin/customers/index.php' target='_blank'>Quản lý khách hàng</a></li>";
echo "<li><a href='admin/reports/index.php' target='_blank'>Dashboard báo cáo</a></li>";
echo "<li><a href='test_xampp.php' target='_blank'>Kiểm tra cấu hình</a></li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?>

