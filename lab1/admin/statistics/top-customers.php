<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

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

// Lấy thông tin admin hiện tại
$admin = $_SESSION["admin"];

// Lấy thời gian lọc từ form (nếu có)
$date_from = isset($_GET['date_from']) && !empty($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) && !empty($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Format lại thời gian cho query
$date_from_query = date('Y-m-d 00:00:00', strtotime($date_from));
$date_to_query = date('Y-m-d 23:59:59', strtotime($date_to));

// Kiểm tra bảng orders có tồn tại không
$orders_exist = $conn->query("SHOW TABLES LIKE 'orders'")->num_rows > 0;

$top_customers = [];

if ($orders_exist) {
    // Truy vấn top khách hàng theo doanh số
    $sql = "SELECT u.id, u.fullname, u.email, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent,
                   GROUP_CONCAT(CONCAT(o.id, ':', o.total_amount) ORDER BY o.order_date DESC SEPARATOR ',') as order_details
            FROM users u
            JOIN orders o ON u.id = o.user_id
            WHERE o.order_date BETWEEN ? AND ?
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $date_from_query, $date_to_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Xử lý chi tiết đơn hàng
            $orders = [];
            if (!empty($row['order_details'])) {
                $details = explode(',', $row['order_details']);
                foreach ($details as $detail) {
                    list($order_id, $amount) = explode(':', $detail);
                    $orders[] = [
                        'id' => $order_id,
                        'amount' => $amount
                    ];
                }
            }
            
            $row['orders'] = $orders;
            $top_customers[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Cà Phê Đậm Đà</title>
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
        color: rgba(255, 255, 255, .75);
        padding: 10px 20px;
    }

    .sidebar .nav-link:hover {
        color: white;
        background-color: rgba(255, 255, 255, .1);
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

    .card {
        margin-bottom: 20px;
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
                        <a class="nav-link" href="../employees/index.php">
                            <i class="fas fa-employees mr-2"></i> Nhân viên
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="../orders/index.php">
                            <i class="fas fa-shopping-cart mr-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="top-customers.php">
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
                    <h2>Thống kê</h2>
                    <div>
                        <i class="fas fa-user mr-1"></i>
                        <?php echo $admin["name"]; ?>
                    </div>
                </div>

                <div class="content">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0"><i class="fas fa-crown mr-2"></i>Top khách hàng theo doanh số</h5>
                        </div>
                        <div class="card-body">
                            <!-- Form lọc theo khoảng thời gian -->
                            <form method="GET" action="" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_from"><i class="far fa-calendar-alt mr-1"></i>Từ
                                                ngày</label>
                                            <input type="date" id="date_from" name="date_from"
                                                class="form-control form-control-sm" value="<?php echo $date_from; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_to"><i class="far fa-calendar-alt mr-1"></i>Đến
                                                ngày</label>
                                            <input type="date" id="date_to" name="date_to"
                                                class="form-control form-control-sm" value="<?php echo $date_to; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-sm d-block w-100">
                                                <i class="fas fa-chart-line mr-1"></i>Thống kê
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <?php if ($orders_exist): ?>
                            <?php if (empty($top_customers)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>Không có dữ liệu đơn hàng trong khoảng thời gian
                                đã chọn.
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Khách hàng</th>
                                            <th>Email</th>
                                            <th>Số đơn hàng</th>
                                            <th>Tổng chi tiêu</th>
                                            <th>Chi tiết</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_customers as $index => $customer): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($customer['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                            <td><?php echo $customer['order_count']; ?></td>
                                            <td><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="collapse"
                                                    data-target="#customer-<?php echo $customer['id']; ?>">
                                                    <i class="fas fa-list-ul mr-1"></i>Xem
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="p-0">
                                                <div class="collapse" id="customer-<?php echo $customer['id']; ?>">
                                                    <div class="card card-body m-2">
                                                        <h6>Đơn hàng gần đây:</h6>
                                                        <ul class="list-group">
                                                            <?php foreach (array_slice($customer['orders'], 0, 5) as $order): ?>
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span>Đơn hàng #<?php echo $order['id']; ?></span>
                                                                <span
                                                                    class="badge badge-primary"><?php echo number_format($order['amount'], 0, ',', '.'); ?>đ</span>
                                                            </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Chưa có dữ liệu đơn hàng trong hệ thống.
                            </div>
                            <?php endif; ?>
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