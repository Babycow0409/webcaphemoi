<?php
/**
 * Script kiểm tra cấu hình XAMPP và kết nối database
 * Truy cập: http://localhost/webcaphe/lab1/test_xampp.php
 */

echo "<h1>Kiểm tra Cấu hình XAMPP</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; }
</style>";

// Kiểm tra 1: PHP Version
echo "<h2>1. Kiểm tra PHP Version</h2>";
$phpVersion = phpversion();
echo "<div class='info'>PHP Version: <strong>$phpVersion</strong></div>";

if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<div class='success'>✓ PHP version phù hợp</div>";
} else {
    echo "<div class='error'>✗ PHP version quá cũ, cần PHP 7.4 trở lên</div>";
}

// Kiểm tra 2: Kết nối MySQL
echo "<h2>2. Kiểm tra kết nối MySQL</h2>";
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    echo "<div class='success'>✓ Kết nối MySQL thành công</div>";
    echo "<div class='info'>Server: $servername<br>Username: $username</div>";
    $conn->close();
} catch (Exception $e) {
    echo "<div class='error'>✗ " . $e->getMessage() . "</div>";
    echo "<div class='info'>Hãy kiểm tra MySQL đã khởi động trong XAMPP Control Panel chưa</div>";
}

// Kiểm tra 3: Database lab1
echo "<h2>3. Kiểm tra Database 'lab1'</h2>";
try {
    $conn = new mysqli($servername, $username, $password, "lab1");
    if ($conn->connect_error) {
        throw new Exception("Database 'lab1' chưa tồn tại hoặc không thể kết nối");
    }
    echo "<div class='success'>✓ Database 'lab1' tồn tại và có thể kết nối</div>";
    
    // Kiểm tra các bảng
    $tables = $conn->query("SHOW TABLES");
    if ($tables && $tables->num_rows > 0) {
        echo "<div class='info'><strong>Các bảng trong database:</strong><ul>";
        while ($row = $tables->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul></div>";
    } else {
        echo "<div class='error'>✗ Database 'lab1' trống, chưa có bảng nào. Hãy import file lab1.sql</div>";
    }
    $conn->close();
} catch (Exception $e) {
    echo "<div class='error'>✗ " . $e->getMessage() . "</div>";
    echo "<div class='info'>Hãy import file lab1.sql vào database. Xem hướng dẫn trong file HUONG_DAN_CHAY_XAMPP.md</div>";
}

// Kiểm tra 4: File cấu hình
echo "<h2>4. Kiểm tra File Cấu hình</h2>";
$configFile = __DIR__ . '/includes/db_connect.php';
if (file_exists($configFile)) {
    echo "<div class='success'>✓ File db_connect.php tồn tại</div>";
    include $configFile;
    
    // Test kết nối với file cấu hình
    if (isset($conn) && !$conn->connect_error) {
        echo "<div class='success'>✓ Kết nối database thành công qua file cấu hình</div>";
    } else {
        echo "<div class='error'>✗ Không thể kết nối database qua file cấu hình</div>";
    }
} else {
    echo "<div class='error'>✗ Không tìm thấy file db_connect.php</div>";
}

// Kiểm tra 5: File chính
echo "<h2>5. Kiểm tra File Chính</h2>";
$indexFile = __DIR__ . '/index.php';
if (file_exists($indexFile)) {
    echo "<div class='success'>✓ File index.php tồn tại</div>";
} else {
    echo "<div class='error'>✗ Không tìm thấy file index.php</div>";
}

// Kiểm tra 6: Thư mục uploads
echo "<h2>6. Kiểm tra Thư mục Uploads</h2>";
$uploadsDir = __DIR__ . '/uploads';
if (is_dir($uploadsDir)) {
    echo "<div class='success'>✓ Thư mục uploads tồn tại</div>";
    if (is_writable($uploadsDir)) {
        echo "<div class='success'>✓ Thư mục uploads có quyền ghi</div>";
    } else {
        echo "<div class='error'>✗ Thư mục uploads không có quyền ghi</div>";
    }
} else {
    echo "<div class='error'>✗ Thư mục uploads không tồn tại</div>";
}

// Tổng kết
echo "<h2>Kết luận</h2>";
echo "<div class='info'>";
echo "<p><strong>Nếu tất cả đều có dấu ✓ (màu xanh):</strong></p>";
echo "<ul>";
echo "<li>Website đã sẵn sàng sử dụng</li>";
echo "<li>Truy cập: <a href='index.php' target='_blank'>http://localhost/webcaphe/lab1/index.php</a></li>";
echo "</ul>";
echo "<p><strong>Nếu có lỗi (màu đỏ):</strong></p>";
echo "<ul>";
echo "<li>Xem file HUONG_DAN_CHAY_XAMPP.md để biết cách khắc phục</li>";
echo "<li>Đảm bảo Apache và MySQL đã khởi động trong XAMPP Control Panel</li>";
echo "<li>Đảm bảo đã import file lab1.sql vào database</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='index.php'>← Quay về trang chủ</a> | <a href='test_xampp.php'>Làm mới trang kiểm tra</a></p>";
?>

