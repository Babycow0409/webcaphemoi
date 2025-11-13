<?php
session_start();
require_once 'includes/db_connect.php';

// Đảm bảo Content-Type header được thiết lập đúng
header('Content-Type: text/html; charset=utf-8');

// Lấy danh mục sản phẩm từ database
try {
    $sql_categories = "SELECT * FROM categories ORDER BY id ASC";
    $result_categories = $conn->query($sql_categories);
    $categories = [];
    
    if ($result_categories && $result_categories->num_rows > 0) {
        while ($row = $result_categories->fetch_assoc()) {
            $categories[] = $row;
        }
    }
} catch (Exception $e) {
    // Nếu có lỗi khi truy vấn bảng categories, dùng danh mục mặc định
    $categories = [
        ['id' => 1, 'name' => 'Cà phê'],
        ['id' => 2, 'name' => 'Trà'],
        ['id' => 3, 'name' => 'Bánh ngọt'],
        ['id' => 4, 'name' => 'Đồ uống khác']
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Mục Sản Phẩm - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/font-fix.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { background-color: #f8f9fa; line-height: 1.6; }
        
        .categories-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .category-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .category-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .category-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .category-card:hover .category-image img {
            transform: scale(1.1);
        }
        
        .category-title {
            padding: 20px;
            background-color: #fff;
            color: #3c2f2f;
            font-family: 'Playfair Display', serif;
            font-size: 1.5em;
        }
        
        h1 {
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            margin-bottom: 30px;
        }
        
        .btn-back {
            display: inline-block;
            background-color: #d4a373;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        
        .btn-back:hover {
            background-color: #8b4513;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="categories-container">
        <h1>Danh Mục Sản Phẩm</h1>
        
        <div class="categories-grid">
            <?php 
            // Hình ảnh mặc định cho các danh mục
            $default_images = [
                1 => 'https://images.unsplash.com/photo-1459755486867-b55449bb39ff?auto=format&fit=crop&w=500&q=80',
                2 => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=500&q=80',
                3 => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=500&q=80',
                4 => 'https://images.unsplash.com/photo-1563227812-0fbe82e4e005?auto=format&fit=crop&w=500&q=80'
            ];
            
            foreach($categories as $index => $category): 
                $cat_id = $category['id'];
                $image = isset($category['image']) && !empty($category['image']) ? $category['image'] : (isset($default_images[$cat_id]) ? $default_images[$cat_id] : 'images/category-default.jpg');
            ?>
            <div class="category-card">
                <a href="products.php?category=<?php echo $cat_id; ?>">
                    <div class="category-image">
                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" onerror="this.src='images/category-default.jpg'">
                    </div>
                    <div class="category-title">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn-back">Quay lại trang chủ</a>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 