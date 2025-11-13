<?php
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

// Lấy thông tin chi tiết người dùng
$user = [];
$orders = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Lấy thông tin người dùng
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra cấu trúc bảng orders để biết cột nào dùng để sắp xếp
        $check_columns = "SHOW COLUMNS FROM orders";
        $columns_result = $conn->query($check_columns);
        $has_created_at = false;
        $has_order_date = false;
        
        if ($columns_result) {
            while ($column = $columns_result->fetch_assoc()) {
                if ($column['Field'] == 'created_at') {
                    $has_created_at = true;
                }
                if ($column['Field'] == 'order_date') {
                    $has_order_date = true;
                }
            }
        }
        
        // Lấy danh sách đơn hàng của người dùng (nếu có)
        // Xây dựng câu truy vấn dựa trên cột có sẵn
        if ($has_created_at) {
            $order_by_clause = "o.created_at DESC";
        } elseif ($has_order_date) {
            $order_by_clause = "o.order_date DESC";
        } else {
            $order_by_clause = "o.id DESC"; // Sắp xếp theo ID nếu không có cột ngày
        }
        
        $sql_orders = "SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items,
                       (SELECT SUM(price * quantity) FROM order_items WHERE order_id = o.id) as total_amount
                       FROM orders o 
                       WHERE o.user_id = ? 
                       ORDER BY $order_by_clause";
        
        $stmt_orders = $conn->prepare($sql_orders);
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        
        if ($result_orders->num_rows > 0) {
            while ($row = $result_orders->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        
        $stmt_orders->close();
    }
    
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

// Set page title
$page_title = "Chi tiết người dùng: " . (isset($user['fullname']) ? htmlspecialchars($user['fullname']) : 'N/A');

// Include header
require_once __DIR__ . '/../includes/admin-header.php';
?>

<style>
    .content {
        background-color: white;
        color: #000;
    }
    
    .content-wrapper {
        background-color: white;
        color: #000;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
    }
    
    .user-profile {
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
        color: #000;
    }
    .user-info {
        margin-bottom: 30px;
        color: #000;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
</style>

<div class="content-wrapper">
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>
                    <?php if (empty($user)): ?>
                        <div class="alert alert-warning">
                            Không tìm thấy thông tin người dùng.
                        </div>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
                        </a>
                    <?php else: ?>
                        <div class="user-profile">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3><?php echo htmlspecialchars($user['fullname'] ?? $user['name'] ?? 'N/A'); ?></h3>
                                        <div>
                                            <?php if (isset($user['role'])): ?>
                                                <span class="badge <?php echo $user['role'] == 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                                                    <?php echo $user['role'] == 'admin' ? 'Quản trị viên' : 'Khách hàng'; ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($user['active'])): ?>
                                                <span class="badge <?php echo $user['active'] == 1 ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $user['active'] == 1 ? 'Đang hoạt động' : 'Đã khóa'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row user-info">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Thông tin cá nhân</h5>
                                    <p><strong>ID:</strong> <?php echo $user['id']; ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></p>
                                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?></p>
                                    <p><strong>Thành phố:</strong> <?php echo htmlspecialchars($user['city'] ?? 'Chưa cập nhật'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Thông tin tài khoản</h5>
                                    <p><strong>Tên đăng nhập:</strong> <?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></p>
                                    <p><strong>Ngày đăng ký:</strong> <?php echo isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?></p>
                                </div>
                            </div>
                            
                            <div class="action-buttons mb-4">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit mr-1"></i> Chỉnh sửa
                                </a>
                                
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <?php if (isset($user['active'])): ?>
                                        <?php if ($user['active'] == 1): ?>
                                            <button type="button" class="btn btn-warning" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'deactivate')">
                                                <i class="fas fa-lock mr-1"></i> Khóa tài khoản
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-success" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'activate')">
                                                <i class="fas fa-unlock mr-1"></i> Mở khóa tài khoản
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                                </a>
                            </div>
                            
                            <!-- Đơn hàng của người dùng -->
                            <h4 class="mt-4 mb-3">Lịch sử đơn hàng</h4>
                            <?php if (empty($orders)): ?>
                                <div class="alert alert-info">
                                    Người dùng này chưa có đơn hàng nào.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Mã đơn hàng</th>
                                                <th>Ngày đặt</th>
                                                <th>Số sản phẩm</th>
                                                <th>Tổng tiền</th>
                                                <th>Trạng thái</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td>
                                                        <?php 
                                                        if (isset($order['created_at'])) {
                                                            echo date('d/m/Y H:i', strtotime($order['created_at']));
                                                        } elseif (isset($order['order_date'])) {
                                                            echo date('d/m/Y H:i', strtotime($order['order_date']));
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $order['total_items']; ?></td>
                                                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                                    <td>
                                                        <?php 
                                                        $status_class = '';
                                                        $status_text = '';
                                                        
                                                        if (isset($order['status'])) {
                                                            switch ($order['status']) {
                                                                case 'pending':
                                                                    $status_class = 'badge-warning';
                                                                    $status_text = 'Chờ xử lý';
                                                                    break;
                                                                case 'processing':
                                                                    $status_class = 'badge-info';
                                                                    $status_text = 'Đang xử lý';
                                                                    break;
                                                                case 'confirmed':
                                                                    $status_class = 'badge-primary';
                                                                    $status_text = 'Đã xác nhận';
                                                                    break;
                                                                case 'shipping':
                                                                    $status_class = 'badge-primary';
                                                                    $status_text = 'Đang giao hàng';
                                                                    break;
                                                                case 'delivered':
                                                                    $status_class = 'badge-success';
                                                                    $status_text = 'Đã giao hàng';
                                                                    break;
                                                                case 'cancelled':
                                                                    $status_class = 'badge-danger';
                                                                    $status_text = 'Đã hủy';
                                                                    break;
                                                                default:
                                                                  $status_class = 'badge-warning';
                                                                    $status_text = 'Chờ xử lý';
                                                            }
                                                        }
                                                        //  else {
                                                        //     $status_class = 'badge-secondary';
                                                        //     $status_text = 'Không xác định';
                                                        // }
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="../orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> Xem
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<script>
    function toggleUserStatus(userId, action) {
        const actionText = action === 'activate' ? 'mở khóa' : 'khóa';
        const confirmMessage = action === 'activate' 
            ? 'Bạn có chắc chắn muốn mở khóa tài khoản này? Người dùng sẽ có thể đăng nhập và sử dụng hệ thống.'
            : 'Bạn có chắc chắn muốn khóa tài khoản này? Người dùng sẽ không thể đăng nhập và sử dụng hệ thống.';
        
        if (confirm(confirmMessage)) {
            fetch(`toggle_status.php?id=${userId}&action=${action}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi thay đổi trạng thái tài khoản');
                });
        }
    }
    </script>
</div>

<?php
// Đóng kết nối
$conn->close();

// Include footer
require_once __DIR__ . '/../includes/admin-footer.php';
?> 