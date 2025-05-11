<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra ID người dùng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_GET['id']);

// Lấy thông tin người dùng
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$user = $result->fetch_assoc();

// Lấy thông tin đơn hàng của người dùng
$sql_orders = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = [];

if ($result_orders->num_rows > 0) {
    while ($row = $result_orders->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Lấy địa chỉ của người dùng
$sql_addresses = "SELECT * FROM addresses WHERE user_id = ?";
$stmt_addresses = $conn->prepare($sql_addresses);
$stmt_addresses->bind_param("i", $user_id);
$stmt_addresses->execute();
$result_addresses = $stmt_addresses->get_result();
$addresses = [];

if ($result_addresses->num_rows > 0) {
    while ($row = $result_addresses->fetch_assoc()) {
        $addresses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết người dùng - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container-fluid {
            padding: 0;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            min-height: 100vh;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.2);
        }
        .content {
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .user-info {
            margin-bottom: 30px;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-processing {
            background-color: #17a2b8;
        }
        .badge-shipped {
            background-color: #007bff;
        }
        .badge-delivered {
            background-color: #28a745;
        }
        .badge-cancelled {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../products/index.php">
                            <i class="fas fa-coffee mr-2"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../orders/index.php">
                            <i class="fas fa-shopping-cart mr-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../statistics/top-customers.php">
                            <i class="fas fa-chart-bar mr-2"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Chi tiết người dùng</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <div class="content">
                    <div class="user-info card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Thông tin cá nhân</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <table class="table table-striped">
                                        <tr>
                                            <th width="150">ID:</th>
                                            <td><?php echo $user['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Họ tên:</th>
                                            <td><?php echo $user['name'] ?? 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo $user['email']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Số điện thoại:</th>
                                            <td><?php echo $user['phone'] ?? 'N/A'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ngày đăng ký:</th>
                                            <td><?php echo isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="nav nav-tabs mt-4" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="orders-tab" data-toggle="tab" href="#orders" role="tab">
                                <i class="fas fa-shopping-cart mr-2"></i>Đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab">
                                <i class="fas fa-map-marker-alt mr-2"></i>Địa chỉ
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="userTabContent">
                        <!-- Tab đơn hàng -->
                        <div class="tab-pane fade show active" id="orders" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <?php if (empty($orders)): ?>
                                        <p class="text-muted">Người dùng chưa có đơn hàng nào.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Mã đơn hàng</th>
                                                        <th>Ngày đặt</th>
                                                        <th>Tổng tiền</th>
                                                        <th>Trạng thái</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($orders as $order): ?>
                                                        <tr>
                                                            <td><?php echo $order['order_number'] ?? $order['id']; ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                            <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                                                            <td>
                                                                <?php
                                                                $status_class = '';
                                                                $status_text = '';
                                                                
                                                                switch ($order['status']) {
                                                                    case 'pending':
                                                                        $status_class = 'badge-pending';
                                                                        $status_text = 'Chờ xử lý';
                                                                        break;
                                                                    case 'processing':
                                                                        $status_class = 'badge-processing';
                                                                        $status_text = 'Đang xử lý';
                                                                        break;
                                                                    case 'shipped':
                                                                        $status_class = 'badge-shipped';
                                                                        $status_text = 'Đang giao hàng';
                                                                        break;
                                                                    case 'delivered':
                                                                    case 'completed':
                                                                        $status_class = 'badge-success';
                                                                        $status_text = 'Đã giao hàng';
                                                                        break;
                                                                    case 'cancelled':
                                                                        $status_class = 'badge-danger';
                                                                        $status_text = 'Đã hủy';
                                                                        break;
                                                                    default:
                                                                        $status_class = 'badge-secondary';
                                                                        $status_text = 'Không xác định';
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                            </td>
                                                            <td>
                                                                <a href="../orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">
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
                            </div>
                        </div>
                        
                        <!-- Tab địa chỉ -->
                        <div class="tab-pane fade" id="address" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <?php if (empty($addresses)): ?>
                                        <p class="text-muted">Người dùng chưa có địa chỉ nào.</p>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php foreach ($addresses as $address): ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title">
                                                                <?php echo $address['name']; ?>
                                                                <?php if (isset($address['is_default']) && $address['is_default']): ?>
                                                                    <span class="badge badge-primary">Mặc định</span>
                                                                <?php endif; ?>
                                                            </h5>
                                                            <p class="card-text">
                                                                <strong>Địa chỉ:</strong> <?php echo $address['address_line1']; ?>
                                                                <?php if (!empty($address['address_line2'])): ?>
                                                                    , <?php echo $address['address_line2']; ?>
                                                                <?php endif; ?>
                                                            </p>
                                                            <p class="card-text">
                                                                <strong>Quận/Huyện:</strong> <?php echo $address['district']; ?><br>
                                                                <strong>Tỉnh/Thành phố:</strong> <?php echo $address['city']; ?><br>
                                                                <strong>Số điện thoại:</strong> <?php echo $address['phone']; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 