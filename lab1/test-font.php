<?php
// Đảm bảo header charset được thiết lập đúng
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm Tra Font Tiếng Việt</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/font-fix.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Kiểm Tra Font Chữ Tiếng Việt</h1>
    
    <div class="test-section">
        <h2>Các danh mục sản phẩm</h2>
        <div class="card-grid">
            <div class="card">
                <h3>Cà Phê</h3>
                <p>Cà phê Việt Nam ngon nhất thế giới</p>
            </div>
            <div class="card">
                <h3>Trà</h3>
                <p>Trà thơm nhẹ nhàng</p>
            </div>
            <div class="card">
                <h3>Bánh ngọt</h3>
                <p>Bánh ngon mỗi ngày</p>
            </div>
            <div class="card">
                <h3>Đồ uống khác</h3>
                <p>Nhiều loại thức uống đa dạng</p>
            </div>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Thông tin thêm</h2>
        <p>Đây là trang kiểm tra việc hiển thị font chữ tiếng Việt. Nếu bạn thấy tất cả các ký tự đặc biệt như: ă, â, đ, ê, ô, ơ, ư hiển thị đúng, thì đã thiết lập thành công.</p>
    </div>
    
    <p><a href="index.php">Quay lại trang chủ</a></p>
</body>
</html> 