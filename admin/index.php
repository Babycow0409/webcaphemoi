<?php
$page_title = "Dashboard";
include 'includes/header.php';

// Thống kê tổng quan
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
?>

<h2>Chào mừng đến với trang quản trị!</h2>
<p>Đây là trang quản trị của website Cà Phê Đậm Đà.</p>

<div class="dashboard-stats">
    <div class="row" style="display: flex; margin: 0 -15px;">
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #3498db; color: white; padding: 20px; border-radius: 5px;">
                <h3>Sản phẩm</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo $totalProducts; ?></p>
            </div>
        </div>
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #2ecc71; color: white; padding: 20px; border-radius: 5px;">
                <h3>Người dùng</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo $totalUsers; ?></p>
            </div>
        </div>
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #e74c3c; color: white; padding: 20px; border-radius: 5px;">
                <h3>Đơn hàng</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo $totalOrders; ?></p>
            </div>
        </div>
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #f39c12; color: white; padding: 20px; border-radius: 5px;">
                <h3>Doanh thu</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</p>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 30px;">
    <h3>Chức năng có sẵn:</h3>
    <ul>
        <li>Quản lý sản phẩm: Thêm, sửa, xóa sản phẩm</li>
        <li>Quản lý đơn hàng: Xem và cập nhật trạng thái đơn hàng</li>
        <li>Quản lý người dùng: Xem danh sách người dùng đã đăng ký</li>
        <li>Thống kê: Xem thống kê top khách hàng theo doanh số</li>
    </ul>
    
    <p>Hãy sử dụng menu bên trái để truy cập các chức năng quản trị.</p>
</div>

<?php
include 'includes/footer.php';
?> 