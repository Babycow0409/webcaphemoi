<?php
// Hiển thị mã HTML của trang sản phẩm để kiểm tra
$page = file_get_contents('http://localhost/lab1/products.php');
file_put_contents('debug.txt', $page);
echo "Đã lưu mã HTML vào file debug.txt";
?> 