<?php
<<<<<<< HEAD
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
            color: rgba(255,255,255,.75);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,.1);
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
                                            <label for="date_from"><i class="far fa-calendar-alt mr-1"></i>Từ ngày</label>
                                            <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="<?php echo $date_from; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_to"><i class="far fa-calendar-alt mr-1"></i>Đến ngày</label>
                                            <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="<?php echo $date_to; ?>">
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
                            
                            <?php if (!$orders_exist): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>Bảng orders chưa tồn tại. Chưa có thông tin đơn hàng nào để thống kê.
                                </div>
                            <?php elseif (empty($top_customers)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>Không có đơn hàng nào trong khoảng thời gian từ <?php echo date('d/m/Y', strtotime($date_from)); ?> đến <?php echo date('d/m/Y', strtotime($date_to)); ?>.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center" width="5%">STT</th>
                                                <th width="25%">Họ tên</th>
                                                <th width="25%">Email</th>
                                                <th class="text-center" width="15%">Số đơn hàng</th>
                                                <th class="text-right" width="15%">Tổng chi tiêu</th>
                                                <th class="text-center" width="15%">Chi tiết</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_customers as $index => $customer): ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <span class="font-weight-bold"><?php echo htmlspecialchars($customer['fullname']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $customer['order_count']; ?></span>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success">
                                                        <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?> VNĐ
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-info" data-toggle="collapse" data-target="#orders-<?php echo $customer['id']; ?>">
                                                            <i class="fas fa-eye mr-1"></i>Xem
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="p-0">
                                                        <div id="orders-<?php echo $customer['id']; ?>" class="collapse">
                                                            <table class="table mb-0 table-sm table-bordered">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="text-center" width="30%">Mã đơn hàng</th>
                                                                        <th class="text-right" width="40%">Giá trị</th>
                                                                        <th class="text-center" width="30%">Thao tác</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($customer['orders'] as $order): ?>
                                                                        <tr>
                                                                            <td class="text-center">#<?php echo $order['id']; ?></td>
                                                                            <td class="text-right"><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</td>
                                                                            <td class="text-center">
                                                                                <a href="../orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="fas fa-file-invoice mr-1"></i>Chi tiết
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
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
=======
$page_title = "Thống kê top khách hàng";
include '../includes/header.php';

// Xử lý form thống kê
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$top_customers = [];

if (!empty($start_date) || !empty($end_date)) {
    // Xây dựng câu truy vấn thống kê top 5 khách hàng có tổng mua cao nhất
    $sql = "SELECT u.id, u.fullname, u.email, u.phone, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
           FROM users u 
           JOIN orders o ON u.id = o.user_id 
           WHERE o.status = 'completed'";
    
    $params = [];
    $types = "";
    
    // Thêm điều kiện khoảng thời gian
    if (!empty($start_date)) {
        $sql .= " AND DATE(o.created_at) >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    
    if (!empty($end_date)) {
        $sql .= " AND DATE(o.created_at) <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    
    $sql .= " GROUP BY u.id 
             ORDER BY total_spent DESC 
             LIMIT 5";
    
    // Thực thi truy vấn
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $top_customers = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="content-header">
    <h1>Top khách hàng mua nhiều nhất</h1>
</div>

<!-- Form chọn khoảng thời gian -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="start_date">Từ ngày</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="end_date">Đến ngày</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Thống kê
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Kết quả thống kê -->
<?php if (count($top_customers) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Top 5 khách hàng có mức mua hàng cao nhất
                <?php if (!empty($start_date) && !empty($end_date)): ?>
                    từ <?php echo date('d/m/Y', strtotime($start_date)); ?> đến <?php echo date('d/m/Y', strtotime($end_date)); ?>
                <?php elseif (!empty($start_date)): ?>
                    từ <?php echo date('d/m/Y', strtotime($start_date)); ?>
                <?php elseif (!empty($end_date)): ?>
                    đến <?php echo date('d/m/Y', strtotime($end_date)); ?>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Thứ hạng</th>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Số đơn hàng</th>
                            <th>Tổng tiền mua</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_customers as $index => $customer): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $customer['fullname']; ?></td>
                                <td><?php echo $customer['email']; ?></td>
                                <td><?php echo $customer['phone']; ?></td>
                                <td><?php echo $customer['order_count']; ?> đơn</td>
                                <td><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" onclick="showOrderDetails(<?php echo $customer['id']; ?>)">
                                        <i class="fas fa-list"></i> Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
>>>>>>> 36298127f1deb9f590e3222166af251932b728fd
            </div>
        </div>
    </div>
    
<<<<<<< HEAD
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
=======
    <!-- Chi tiết đơn hàng của khách hàng (Modal) -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Chi tiết đơn hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Đang tải dữ liệu...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($start_date) || !empty($end_date))): ?>
    <div class="alert alert-info">
        Không tìm thấy thông tin khách hàng nào trong khoảng thời gian đã chọn.
    </div>
<?php endif; ?>

<script>
function showOrderDetails(userId) {
    // Hiển thị modal
    $('#orderDetailsModal').modal('show');
    
    // Lấy dữ liệu về các đơn hàng của khách hàng
    $.ajax({
        url: 'get_customer_orders.php',
        type: 'GET',
        data: {
            user_id: userId,
            start_date: '<?php echo $start_date; ?>',
            end_date: '<?php echo $end_date; ?>'
        },
        success: function(response) {
            $('#orderDetailsContent').html(response);
        },
        error: function() {
            $('#orderDetailsContent').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu.</div>');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?> 
>>>>>>> 36298127f1deb9f590e3222166af251932b728fd
