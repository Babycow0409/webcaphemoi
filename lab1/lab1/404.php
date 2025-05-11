<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không tìm thấy trang - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { padding-top: 100px; line-height: 1.6; background-color: #f8f9fa; }
        header { background-color: #3c2f2f; color: white; padding: 1rem; position: fixed; width: 100%; top: 0; z-index: 1000; }
        nav { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.8em; padding: 10px; }
        .nav-links { display: flex; flex-wrap: wrap; align-items: center; padding: 10px; }
        nav a { color: white; text-decoration: none; margin: 10px 15px; font-weight: bold; }
        nav a:hover { color: #d4a373; }
        
        .error-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px 20px;
            text-align: center;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .error-container h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #d9534f;
            margin-bottom: 20px;
        }
        
        .error-container p {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-container .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 0 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .error-container .btn-primary {
            background-color: #5a3921;
            color: white;
        }
        
        .error-container .btn-primary:hover {
            background-color: #3d2715;
        }
        
        .error-container .btn-secondary {
            background-color: #e9ecef;
            color: #333;
            border: 1px solid #ced4da;
        }
        
        .error-container .btn-secondary:hover {
            background-color: #ced4da;
        }
        
        .coffee-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cà Phê Đậm Đà</div>
            <div class="nav-links">
                <a href="/php1su/lab1/index.php">Trang chủ</a>
                <a href="/php1su/lab1/products.php">Sản phẩm</a>
                <a href="/php1su/lab1/cart.php">Giỏ hàng</a>
            </div>
        </nav>
    </header>

    <div class="error-container">
        <div class="coffee-icon">☕</div>
        <h1>Trang không tồn tại</h1>
        <p>Rất tiếc, trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.<br>
           Có thể sản phẩm này đã được cập nhật hoặc không còn bán nữa.</p>
        <div class="actions">
            <a href="/php1su/lab1/products.php" class="btn btn-primary">Xem sản phẩm khác</a>
            <a href="/php1su/lab1/index.php" class="btn btn-secondary">Về trang chủ</a>
        </div>
    </div>
</body>
</html> 