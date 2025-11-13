<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../classes/Report.php';
require_once __DIR__ . '/../../classes/SalesAnalytics.php';

$report = new Report($conn);
$analytics = new SalesAnalytics($conn);

// Lấy thời gian lọc
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$dateFromQuery = $dateFrom . ' 00:00:00';
$dateToQuery = $dateTo . ' 23:59:59';

// Lấy thống kê
$summaryStats = $analytics->getSummaryStats($dateFromQuery, $dateToQuery);
$topProducts = $analytics->getTopProducts($dateFromQuery, $dateToQuery, 10);
$topCustomers = $analytics->getTopCustomers($dateFromQuery, $dateToQuery, 10);
$revenueByDate = $analytics->getRevenueByDateRange($dateFromQuery, $dateToQuery);
$revenueByCategory = $analytics->getRevenueByCategory($dateFromQuery, $dateToQuery);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Báo cáo - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <style>
        .stat-card {
            border-left: 4px solid;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card.primary { border-left-color: #007bff; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h4><i class="fas fa-chart-line mr-2"></i>Dashboard Báo cáo</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <form method="GET" action="" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Từ ngày</label>
                                        <input type="date" name="date_from" class="form-control" value="<?php echo $dateFrom; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Đến ngày</label>
                                        <input type="date" name="date_to" class="form-control" value="<?php echo $dateTo; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-search mr-1"></i>Xem báo cáo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Thống kê tổng quan -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stat-card primary bg-light">
                                    <h6 class="text-muted">Tổng đơn hàng</h6>
                                    <h3 class="text-primary"><?php echo number_format($summaryStats['total_orders'] ?? 0); ?></h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card success bg-light">
                                    <h6 class="text-muted">Doanh thu</h6>
                                    <h3 class="text-success"><?php echo number_format($summaryStats['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card warning bg-light">
                                    <h6 class="text-muted">Giảm giá</h6>
                                    <h3 class="text-warning"><?php echo number_format($summaryStats['total_discount'] ?? 0, 0, ',', '.'); ?>đ</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card info bg-light">
                                    <h6 class="text-muted">Doanh thu thuần</h6>
                                    <h3 class="text-info"><?php echo number_format($summaryStats['net_revenue'] ?? 0, 0, ',', '.'); ?>đ</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Biểu đồ doanh thu -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Doanh thu theo ngày</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="80"></canvas>
                            </div>
                        </div>

                        <!-- Top sản phẩm -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Top 10 sản phẩm bán chạy</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>STT</th>
                                                        <th>Tên sản phẩm</th>
                                                        <th>Số lượng</th>
                                                        <th>Doanh thu</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($topProducts as $index => $product): ?>
                                                        <tr>
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                            <td><?php echo $product['total_quantity']; ?></td>
                                                            <td><?php echo number_format($product['total_revenue'], 0, ',', '.'); ?>đ</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Top 10 khách hàng</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>STT</th>
                                                        <th>Khách hàng</th>
                                                        <th>Số đơn</th>
                                                        <th>Tổng chi tiêu</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($topCustomers as $index => $customer): ?>
                                                        <tr>
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><?php echo htmlspecialchars($customer['fullname']); ?></td>
                                                            <td><?php echo $customer['total_orders']; ?></td>
                                                            <td><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export -->
                        <div class="mt-4">
                            <a href="export.php?type=revenue&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel mr-1"></i>Xuất Excel - Doanh thu
                            </a>
                            <a href="export.php?type=products&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel mr-1"></i>Xuất Excel - Sản phẩm
                            </a>
                            <a href="export.php?type=customers&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel mr-1"></i>Xuất Excel - Khách hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Biểu đồ doanh thu
        const revenueData = <?php echo json_encode($revenueByDate); ?>;
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.date),
                datasets: [{
                    label: 'Doanh thu',
                    data: revenueData.map(d => d.total_revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

