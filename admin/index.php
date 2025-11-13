<?php
$page_title = "Dashboard";

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thống kê tổng quan
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;

// Tính doanh thu từ các đơn hàng đã thanh toán hoặc đã giao hàng
// Kiểm tra xem có cột payment_status không
$check_payment_status = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'");
$has_payment_status = $check_payment_status && $check_payment_status->num_rows > 0;

if ($has_payment_status) {
    // Nếu có payment_status, tính từ các đơn hàng đã thanh toán
    $totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid' AND status != 'cancelled'")->fetch_assoc()['total'] ?? 0;
} else {
    // Nếu không có payment_status, tính từ các đơn hàng đã giao hàng
    $totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'")->fetch_assoc()['total'] ?? 0;
}

// Đơn hàng gần đây
$recentOrders = [];
$ordersQuery = $conn->query("SHOW TABLES LIKE 'orders'");
if ($ordersQuery->num_rows > 0) {
    // Kiểm tra cấu trúc bảng orders để biết các cột có sẵn
    $orders_columns = $conn->query("SHOW COLUMNS FROM orders");
    $has_created_at = false;
    $order_column = "id"; // Mặc định sắp xếp theo id
    
    if ($orders_columns) {
        while ($col = $orders_columns->fetch_assoc()) {
            if ($col['Field'] == 'created_at') {
                $has_created_at = true;
                break;
            }
            if ($col['Field'] == 'order_date') {
                $order_column = "order_date";
            }
        }
    }
    
    // Sử dụng created_at nếu có, nếu không dùng order_date hoặc id
    $order_by = $has_created_at ? "o.created_at" : "o.$order_column";
    
    $recentOrdersQuery = $conn->query("SELECT o.*, u.fullname FROM orders o 
                                      LEFT JOIN users u ON o.user_id = u.id 
                                      ORDER BY $order_by DESC LIMIT 5");
    if ($recentOrdersQuery) {
        $recentOrders = $recentOrdersQuery;
    }
}

// Sản phẩm mới nhất
$topProducts = $conn->query("SELECT id, name, price, 0 as order_count 
                            FROM products 
                            ORDER BY id DESC LIMIT 5");

// Include header
require_once __DIR__ . '/includes/admin-header.php';
?>

<style>
    .stats-card {
        color: white;
        padding: 20px;
        border-radius: 5px;
        text-align: center;
    }
    .stats-card h3 {
        font-size: 18px;
        margin-bottom: 10px;
    }
    .stats-card p {
        font-size: 24px;
        margin: 0;
    }
</style>
                    <h3>Chào mừng đến với trang quản trị!</h3>
                    <p>Đây là trang quản trị của website Cà Phê Đậm Đà.</p>
                    
                    <!-- Thống kê -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #3498db;">
                                <h3>Sản phẩm</h3>
                                <p><?php echo $totalProducts; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #2ecc71;">
                                <h3>Người dùng</h3>
                                <p><?php echo $totalUsers; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #e74c3c;">
                                <h3>Đơn hàng</h3>
                                <p><?php echo $totalOrders; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #f39c12;">
                                <h3>Doanh thu</h3>
                                <p><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="m-0">Chức năng có sẵn</h5>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li>Quản lý sản phẩm: Thêm, sửa, xóa sản phẩm</li>
                                <li>Quản lý đơn hàng: Xem và cập nhật trạng thái đơn hàng</li>
                                <li>Quản lý người dùng: Xem danh sách người dùng đã đăng ký</li>
                                <li>Thống kê: Xem thống kê top khách hàng theo doanh số</li>
                            </ul>
                            
                            <p>Hãy sử dụng menu bên trái để truy cập các chức năng quản trị.</p>
                        </div>
                    </div>
<?php
// Include footer
require_once __DIR__ . '/includes/admin-footer.php';
?>
