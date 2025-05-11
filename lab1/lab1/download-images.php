<?php
// Kết nối đến cơ sở dữ liệu
require_once 'connect.php';

// Truy vấn để lấy tất cả các đường dẫn hình ảnh từ bảng sản phẩm
$query = "SELECT image FROM products";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

// Tạo thư mục nếu chưa tồn tại
$image_dir = "images/products";
if (!file_exists($image_dir)) {
    mkdir($image_dir, 0777, true);
}

// Tải xuống các hình ảnh và nén
$zip = new ZipArchive();
$zipname = "product_images.zip";

if ($zip->open($zipname, ZipArchive::CREATE) !== TRUE) {
    die("Không thể tạo file zip");
}

$image_count = 0;
$missing_count = 0;

echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tải xuống hình ảnh sản phẩm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .loading {
            margin-top: 20px;
            display: none;
        }
        .spinner {
            animation: spin 2s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <h1>Tải xuống hình ảnh sản phẩm</h1>';

while ($row = mysqli_fetch_assoc($result)) {
    $image_path = $row['image'];
    
    // Kiểm tra xem hình ảnh có tồn tại không
    if (file_exists($image_path)) {
        $zip->addFile($image_path, basename($image_path));
        $image_count++;
    } else {
        $missing_count++;
    }
}

// Đóng file zip
$zip->close();

// Hiển thị thông tin
echo '<div class="alert alert-info">
    <p><strong>Tổng số hình ảnh:</strong> ' . ($image_count + $missing_count) . '</p>
    <p><strong>Số hình ảnh đã tìm thấy:</strong> ' . $image_count . '</p>
    <p><strong>Số hình ảnh bị thiếu:</strong> ' . $missing_count . '</p>
</div>';

if ($image_count > 0) {
    echo '<div class="alert alert-success">
        <p>Đã nén ' . $image_count . ' hình ảnh thành công. Bạn có thể tải xuống file zip bên dưới.</p>
    </div>
    
    <a href="' . $zipname . '" class="btn" id="download-btn" download>
        <i class="fas fa-download"></i> Tải xuống hình ảnh
    </a>
    
    <div class="loading" id="loading">
        <i class="fas fa-spinner spinner"></i> Đang chuẩn bị file...
    </div>
    
    <script>
        document.getElementById("download-btn").addEventListener("click", function() {
            document.getElementById("loading").style.display = "block";
            setTimeout(function() {
                document.getElementById("loading").style.display = "none";
            }, 3000);
        });
    </script>';
} else {
    echo '<div class="alert alert-warning">
        <p>Không tìm thấy hình ảnh nào để tải xuống.</p>
    </div>';
}

echo '<p><a href="index.php">Quay lại trang chủ</a></p>
</body>
</html>';

// Đóng kết nối
mysqli_close($conn);
?> 