<?php
/**
 * Script sửa lỗi foreign key constraint
 * Truy cập: http://localhost/webcaphe/lab1/fix_foreign_key.php
 */

require_once 'includes/db_connect.php';

$conn->set_charset("utf8mb4");

echo "<!DOCTYPE html>";
echo "<html lang='vi'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Fix Foreign Key - MODULE 4</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
    .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔧 Sửa lỗi Foreign Key Constraint</h1>";

// Kiểm tra các foreign key hiện có
echo "<h2>1. Kiểm tra foreign keys hiện có trong bảng orders</h2>";

$dbName = $conn->query("SELECT DATABASE()")->fetch_row()[0];
$result = $conn->query("
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = '$dbName' 
    AND TABLE_NAME = 'orders'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if ($result->num_rows > 0) {
    echo "<div class='info'>";
    echo "<strong>Các foreign key hiện có:</strong><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>{$row['CONSTRAINT_NAME']}</strong>: {$row['COLUMN_NAME']} → {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}</li>";
    }
    echo "</ul></div>";
} else {
    echo "<div class='info'>Không có foreign key nào trong bảng orders</div>";
}

// Kiểm tra foreign key cho promotion_id
echo "<h2>2. Kiểm tra foreign key cho promotion_id</h2>";

$checkPromoFK = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = '$dbName' 
    AND TABLE_NAME = 'orders' 
    AND COLUMN_NAME = 'promotion_id'
    AND REFERENCED_TABLE_NAME = 'promotions'
");

if ($checkPromoFK->num_rows > 0) {
    $fkInfo = $checkPromoFK->fetch_assoc();
    $fkName = $fkInfo['CONSTRAINT_NAME'];
    echo "<div class='success'>✅ Foreign key đã tồn tại: <strong>$fkName</strong></div>";
    
    if ($fkName != 'orders_ibfk_promotion') {
        echo "<div class='info'>⚠️ Tên constraint không đúng. Bạn có muốn xóa và tạo lại không?</div>";
        
        if (isset($_GET['action']) && $_GET['action'] == 'fix') {
            // Xóa constraint cũ
            $dropSql = "ALTER TABLE `orders` DROP FOREIGN KEY `$fkName`";
            if ($conn->query($dropSql)) {
                echo "<div class='success'>✅ Đã xóa constraint cũ: $fkName</div>";
                
                // Tạo lại constraint mới
                $createSql = "ALTER TABLE `orders` 
                             ADD CONSTRAINT `orders_ibfk_promotion` 
                             FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL";
                if ($conn->query($createSql)) {
                    echo "<div class='success'>✅ Đã tạo lại constraint: orders_ibfk_promotion</div>";
                } else {
                    echo "<div class='error'>❌ Lỗi khi tạo constraint: " . $conn->error . "</div>";
                }
            } else {
                echo "<div class='error'>❌ Lỗi khi xóa constraint: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='info'><a href='?action=fix' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Sửa ngay</a></div>";
        }
    }
} else {
    echo "<div class='error'>❌ Chưa có foreign key cho promotion_id</div>";
    
    // Kiểm tra xem cột promotion_id có tồn tại không
    $checkColumn = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'promotion_id'");
    if ($checkColumn->num_rows > 0) {
        echo "<div class='info'>✅ Cột promotion_id đã tồn tại. Đang tạo foreign key...</div>";
        
        // Kiểm tra bảng promotions có tồn tại không
        $checkPromoTable = $conn->query("SHOW TABLES LIKE 'promotions'");
        if ($checkPromoTable->num_rows > 0) {
            $createSql = "ALTER TABLE `orders` 
                         ADD CONSTRAINT `orders_ibfk_promotion` 
                         FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL";
            
            if ($conn->query($createSql)) {
                echo "<div class='success'>✅ Đã tạo foreign key thành công!</div>";
            } else {
                echo "<div class='error'>❌ Lỗi: " . $conn->error . "</div>";
                
                // Thử xóa constraint cũ nếu có
                if (strpos($conn->error, 'Duplicate') !== false) {
                    echo "<div class='info'>⚠️ Có vẻ như constraint đã tồn tại với tên khác. Đang tìm và xóa...</div>";
                    
                    // Tìm tất cả constraint liên quan đến promotion_id
                    $allFKs = $conn->query("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = '$dbName' 
                        AND TABLE_NAME = 'orders' 
                        AND COLUMN_NAME = 'promotion_id'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    if ($allFKs->num_rows > 0) {
                        while ($fk = $allFKs->fetch_assoc()) {
                            $oldName = $fk['CONSTRAINT_NAME'];
                            echo "<div class='info'>Đang xóa constraint: $oldName</div>";
                            $dropSql = "ALTER TABLE `orders` DROP FOREIGN KEY `$oldName`";
                            if ($conn->query($dropSql)) {
                                echo "<div class='success'>✅ Đã xóa: $oldName</div>";
                                
                                // Tạo lại
                                if ($conn->query($createSql)) {
                                    echo "<div class='success'>✅ Đã tạo lại foreign key thành công!</div>";
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo "<div class='error'>❌ Bảng promotions chưa tồn tại. Hãy tạo bảng promotions trước!</div>";
        }
    } else {
        echo "<div class='error'>❌ Cột promotion_id chưa tồn tại. Hãy chạy setup_module4.php trước!</div>";
    }
}

echo "<hr>";
echo "<div class='info'>";
echo "<h3>📋 Các bước tiếp theo:</h3>";
echo "<ul>";
echo "<li><a href='setup_module4.php'>Chạy lại Setup MODULE 4</a></li>";
echo "<li><a href='admin/customers/index.php'>Quản lý khách hàng</a></li>";
echo "<li><a href='admin/reports/index.php'>Dashboard báo cáo</a></li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";

$conn->close();
?>

