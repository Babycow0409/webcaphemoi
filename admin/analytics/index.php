<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../includes/db_connect.php';

// Lấy thời gian lọc
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$dateFromQuery = $dateFrom . ' 00:00:00';
$dateToQuery = $dateTo . ' 23:59:59';

// Biến lưu dữ liệu
$topCustomers = [];
$summaryStats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'avg_order_value' => 0,
    'total_customers' => 0
];

// Lấy top 5 khách hàng có mức mua hàng cao nhất
$sql = "SELECT u.id, u.fullname, u.email, u.customer_level,
               COUNT(o.id) as order_count, 
               SUM(o.total_amount) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE o.order_date BETWEEN ? AND ?
        GROUP BY u.id
        ORDER BY total_spent DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dateFromQuery, $dateToQuery);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topCustomers[] = $row;
    }
}
$stmt->close();

// Lấy tổng thống kê
$sumSql = "SELECT COUNT(DISTINCT o.id) as total_orders,
                  SUM(o.total_amount) as total_revenue,
                  COUNT(DISTINCT o.user_id) as total_customers,
                  AVG(o.total_amount) as avg_order_value
           FROM orders o
           WHERE o.order_date BETWEEN ? AND ?";

$sumStmt = $conn->prepare($sumSql);
$sumStmt->bind_param("ss", $dateFromQuery, $dateToQuery);
$sumStmt->execute();
$sumResult = $sumStmt->get_result();

if ($sumResult && $sumResult->num_rows > 0) {
    $summaryStats = $sumResult->fetch_assoc();
}
$sumStmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo & Thống kê - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
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
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
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
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .stat-card {
            border-left: 4px solid;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card.primary { border-left-color: #007bff; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #17a2b8; }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .customer-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 10px;
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
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-chart-bar mr-2"></i> Báo cáo & Thống kê
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
                    <h2><i class="fas fa-chart-bar mr-2"></i>Báo cáo & Thống kê</h2>
                </div>

                <div class="content">
                    <!-- Filter -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Bộ lọc thời gian</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="form-inline">
                                <div class="form-group mr-3">
                                    <label class="mr-2">Từ ngày</label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                                </div>
                                <div class="form-group mr-3">
                                    <label class="mr-2">Đến ngày</label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>Xem báo cáo
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="stat-card primary">
                                <div class="stat-label">Tổng số đơn hàng</div>
                                <div class="stat-value"><?php echo $summaryStats['total_orders'] ?? 0; ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card success">
                                <div class="stat-label">Tổng doanh thu</div>
                                <div class="stat-value"><?php echo number_format($summaryStats['total_revenue'] ?? 0, 0, ',', '.'); ?> VNĐ</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card warning">
                                <div class="stat-label">Giá trị trung bình đơn hàng</div>
                                <div class="stat-value"><?php echo number_format($summaryStats['avg_order_value'] ?? 0, 0, ',', '.'); ?> VNĐ</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card info">
                                <div class="stat-label">Tổng số khách hàng</div>
                                <div class="stat-value"><?php echo $summaryStats['total_customers'] ?? 0; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Customers -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0"><i class="fas fa-star mr-2"></i>Top 5 Khách hàng có mức mua hàng cao nhất</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($topCustomers)): ?>
                                <p class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;">Xếp hạng</th>
                                                <th>Tên khách hàng</th>
                                                <th>Email</th>
                                                <th>Cấp độ</th>
                                                <th>Số đơn hàng</th>
                                                <th class="text-right">Tổng mua</th>
                                                <th style="width: 100px;">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $rank = 1;
                                            foreach ($topCustomers as $customer):
                                                // Xác định màu cấp độ
                                                $levelBadges = [
                                                    'bronze' => '<span class="customer-badge" style="background-color: #CD7F32; color: white;">Bronze</span>',
                                                    'silver' => '<span class="customer-badge" style="background-color: #C0C0C0; color: #333;">Silver</span>',
                                                    'gold' => '<span class="customer-badge" style="background-color: #FFD700; color: #333;">Gold</span>',
                                                    'platinum' => '<span class="customer-badge" style="background-color: #E5E4E2; color: #333;">Platinum</span>'
                                                ];
                                                $levelBadge = $levelBadges[$customer['customer_level']] ?? '<span class="customer-badge">Unknown</span>';
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="rank-badge"><?php echo $rank; ?></div>
                                                    </td>
                                                    <td><strong><?php echo htmlspecialchars($customer['fullname']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                    <td><?php echo $levelBadge; ?></td>
                                                    <td><?php echo $customer['order_count']; ?> đơn</td>
                                                    <td class="text-right"><strong><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?> VNĐ</strong></td>
                                                    <td>
                                                        <a href="../users/index.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết khách hàng">
                                                            <i class="fas fa-eye mr-1"></i>Xem KH
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php 
                                                // Lấy danh sách đơn hàng của khách hàng này
                                                $ordersSql = "SELECT id, total_amount, order_date, status FROM orders 
                                                             WHERE user_id = ? AND order_date BETWEEN ? AND ?
                                                             ORDER BY order_date DESC";
                                                $ordersStmt = $conn->prepare($ordersSql);
                                                $ordersStmt->bind_param("iss", $customer['id'], $dateFromQuery, $dateToQuery);
                                                $ordersStmt->execute();
                                                $ordersResult = $ordersStmt->get_result();
                                                $orders = [];
                                                while ($order = $ordersResult->fetch_assoc()) {
                                                    $orders[] = $order;
                                                }
                                                $ordersStmt->close();
                                                
                                                // Hiển thị danh sách đơn hàng
                                                if (!empty($orders)):
                                                ?>
                                                    <tr>
                                                        <td colspan="7">
                                                            <div style="padding-left: 50px; margin-top: 10px;">
                                                                <strong style="color: #666;">Danh sách đơn hàng:</strong>
                                                                <table class="table table-sm table-bordered mt-2" style="margin-left: 10px;">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Mã đơn hàng</th>
                                                                            <th>Ngày đặt</th>
                                                                            <th>Trạng thái</th>
                                                                            <th class="text-right">Số tiền</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($orders as $order): ?>
                                                                            <tr>
                                                                                <td>#<?php echo $order['id']; ?></td>
                                                                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                                                <td>
                                                                                    <span class="badge badge-secondary">
                                                                                        <?php 
                                                                                        $statusMap = [
                                                                                            'pending' => 'Chờ',
                                                                                            'confirmed' => 'Xác nhận',
                                                                                            'processing' => 'Xử lý',
                                                                                            'shipping' => 'Giao hàng',
                                                                                            'delivered' => 'Đã giao',
                                                                                            'cancelled' => 'Hủy'
                                                                                        ];
                                                                                        echo $statusMap[$order['status']] ?? $order['status'];
                                                                                        ?>
                                                                                    </span>
                                                                                </td>
                                                                                <td class="text-right">
                                                                                    <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</span>
                                                                                    <a href="../orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary ml-2" title="Xem chi tiết">
                                                                                        <i class="fas fa-arrow-right"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif;
                                                $rank++;
                                            endforeach;
                                            ?>
                                        </tbody>
                                    </table>
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
