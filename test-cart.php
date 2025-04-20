<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Giỏ hàng</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .product { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .button { display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .button:hover { background: #218838; }
        .debug { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 30px; }
        pre { background: #eee; padding: 10px; overflow: auto; }
        .product-image { width: 100px; height: 100px; object-fit: cover; display: block; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Kiểm tra Giỏ hàng</h1>
    
    <h2>Thêm sản phẩm mẫu</h2>
    <div class="product">
        <img src="images/arabica-caudat.jpg" alt="Arabica Cầu Đất" class="product-image" onerror="this.src='images/default-product.jpg'">
        <h3>Cà phê Arabica Cầu Đất</h3>
        <p>Giá: 150,000 VNĐ</p>
        <a href="add-to-cart.php?id=test-product-1&name=Cà phê Arabica Cầu Đất&price=150000&image=images/arabica-caudat.jpg&quantity=1" class="button">Thêm vào giỏ hàng (GET)</a>
    </div>
    
    <div class="product">
        <img src="images/robusta-daklak.jpg" alt="Robusta Đắk Lắk" class="product-image" onerror="this.src='images/default-product.jpg'">
        <h3>Cà phê Robusta Đắk Lắk</h3>
        <p>Giá: 120,000 VNĐ</p>
        <a href="add-to-cart.php?id=test-product-2&name=Cà phê Robusta Đắk Lắk&price=120000&image=images/robusta-daklak.jpg&quantity=1" class="button">Thêm vào giỏ hàng (GET)</a>
    </div>
    
    <div class="product">
        <img src="images/robusta-india.jpg" alt="Robusta Ấn Độ" class="product-image" onerror="this.src='images/default-product.jpg'">
        <h3>Cà phê Robusta Ấn Độ</h3>
        <p>Giá: 135,000 VNĐ</p>
        <a href="add-to-cart.php?id=test-product-3&name=Cà phê Robusta Ấn Độ&price=135000&image=images/robusta-india.jpg&quantity=1" class="button">Thêm vào giỏ hàng (GET)</a>
    </div>
    
    <p><a href="cart.php" class="button" style="background: #007bff;">Xem giỏ hàng</a></p>
    <p><a href="cart.php?reset=1" class="button" style="background: #dc3545;">Xóa toàn bộ giỏ hàng</a></p>
    
    <div class="debug">
        <h2>Thông tin debug</h2>
        <h3>Giỏ hàng hiện tại:</h3>
        <pre><?php print_r(isset($_SESSION['cart']) ? $_SESSION['cart'] : 'Không có giỏ hàng'); ?></pre>
    </div>
</body>
</html> 