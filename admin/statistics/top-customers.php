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

// Truy vấn để kiểm tra cấu trúc bảng users
$check_users_table = "SHOW COLUMNS FROM users";
$result_check = $conn->query($check_users_table);
$columns = [];
if ($result_check) {
    while ($row = $result_check->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

// Xác định tên cột chứa thông tin tên người dùng
$name_column = in_array('full_name', $columns) ? 'full_name' : (in_array('name', $columns) ? 'name' : 'email');

// Lấy top 10 khách hàng có tổng giá trị đơn hàng cao nhất
$sql = "SELECT u.id, u.$name_column as customer_name, u.email, COUNT(o.id) as total_orders, 
        SUM(o.total_amount) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE o.status = 'completed' OR o.status = 'delivered'
        GROUP BY u.id
        ORDER BY total_spent DESC
        LIMIT 10";

$result = $conn->query($sql);
$top_customers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $top_customers[] = $row;
    }
}

// Truy vấn cấu trúc bảng orders để tìm cột ngày đặt hàng
$check_orders_table = "SHOW COLUMNS FROM orders";
$result_check_orders = $conn->query($check_orders_table);
$order_columns = [];
if ($result_check_orders) {
    while ($row = $result_check_orders->fetch_assoc()) {
        $order_columns[] = $row['Field'];
    }
}

// Xác định cột chứa ngày đặt hàng
$date_column = in_array('order_date', $order_columns) ? 'order_date' : 
               (in_array('created_at', $order_columns) ? 'created_at' : 
               (in_array('date', $order_columns) ? 'date' : NULL));

// Nếu không tìm thấy cột ngày, chỉ hiển thị tổng doanh thu không theo tháng
if ($date_column) {
    // Lấy thống kê doanh thu theo tháng
    $sql_revenue = "SELECT 
                    YEAR($date_column) as year,
                    MONTH($date_column) as month,
                    SUM(total_amount) as monthly_revenue
                    FROM orders
                    WHERE status = 'completed' OR status = 'delivered'
                    GROUP BY YEAR($date_column), MONTH($date_column)
                    ORDER BY year DESC, month DESC
                    LIMIT 12";

    $result_revenue = $conn->query($sql_revenue);
    $monthly_revenue = [];

    if ($result_revenue && $result_revenue->num_rows > 0) {
        while ($row = $result_revenue->fetch_assoc()) {
            $monthly_revenue[] = $row;
        }
    }
} else {
    // Không tìm thấy cột ngày, để mảng trống
    $monthly_revenue = [];
}

// Phần sản phẩm bán chạy
// Kiểm tra cấu trúc bảng order_items
$check_order_items = "SHOW COLUMNS FROM order_items";
$result_check_items = $conn->query($check_order_items);
$item_columns = [];
if ($result_check_items) {
    while ($row = $result_check_items->fetch_assoc()) {
        $item_columns[] = $row['Field'];
    }
}

// Xác định cột liên kết đến sản phẩm
$product_id_column = in_array('product_id', $item_columns) ? 'product_id' : 
                    (in_array('item_id', $item_columns) ? 'item_id' : NULL);

if ($product_id_column) {
    // Lấy top 5 sản phẩm bán chạy nhất
    $sql_products = "SELECT p.id, p.name, p.category, p.price, COUNT(oi.id) as total_sold
                    FROM products p
                    JOIN order_items oi ON p.id = oi.$product_id_column
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.status = 'completed' OR o.status = 'delivered'
                    GROUP BY p.id
                    ORDER BY total_sold DESC
                    LIMIT 5";
    $result_products = $conn->query($sql_products);
    $top_products = [];
    
    if ($result_products && $result_products->num_rows > 0) {
        while ($row = $result_products->fetch_assoc()) {
            $top_products[] = $row;
        }
    }
} else {
    // Không tìm thấy cột liên kết sản phẩm
    $top_products = [];
    
    // Hiển thị các sản phẩm mới nhất thay thế
    $sql_products = "SELECT id, name, category, price FROM products ORDER BY id DESC LIMIT 5";
    $result_products = $conn->query($sql_products);
    
    if ($result_products && $result_products->num_rows > 0) {
        while ($row = $result_products->fetch_assoc()) {
            $row['total_sold'] = 'N/A';
            $top_products[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            margin-bottom: 20px;
        }
        .stat-card .card-header {
            font-weight: bold;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-danger {
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
                        <a class="nav-link" href="../users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../statistics/top-customers.php">
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
                <div class="header">
                    <h2>Thống kê</h2>
                </div>
                
                <div class="content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="stat-card card">
                                <div class="card-header">
                                    <i class="fas fa-users mr-2"></i>Top 10 khách hàng
                                </div>
                                <div class="card-body">
                                    <?php if (empty($top_customers)): ?>
                                        <p class="text-muted">Chưa có dữ liệu về khách hàng</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Tên</th>
                                                        <th>Email</th>
                                                        <th>Số đơn hàng</th>
                                                        <th>Tổng chi tiêu</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($top_customers as $customer): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="../users/view.php?id=<?php echo $customer['id']; ?>">
                                                                    <?php echo $customer['customer_name']; ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo $customer['email']; ?></td>
                                                            <td><?php echo $customer['total_orders']; ?></td>
                                                            <td><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?> VNĐ</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="stat-card card">
                                <div class="card-header">
                                    <i class="fas fa-chart-line mr-2"></i>Doanh thu theo tháng
                                </div>
                                <div class="card-body">
                                    <?php if (empty($monthly_revenue)): ?>
                                        <p class="text-muted">Chưa có dữ liệu về doanh thu</p>
                                    <?php else: ?>
                                        <canvas id="revenueChart" height="300"></canvas>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="stat-card card">
                                <div class="card-header">
                                    <i class="fas fa-coffee mr-2"></i>
                                    <?php echo $product_id_column ? 'Sản phẩm bán chạy nhất' : 'Sản phẩm mới nhất'; ?>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($top_products)): ?>
                                        <p class="text-muted">Chưa có dữ liệu về sản phẩm bán chạy</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Tên sản phẩm</th>
                                                        <th>Danh mục</th>
                                                        <th>Giá</th>
                                                        <th>Số lượng đã bán</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($top_products as $product): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="../products/edit.php?id=<?php echo $product['id']; ?>">
                                                                    <?php echo $product['name']; ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo ucfirst($product['category']); ?></td>
                                                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</td>
                                                            <td><?php echo $product['total_sold']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
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
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <?php if (!empty($monthly_revenue)): ?>
    <script>
        // Dữ liệu doanh thu theo tháng
        var months = [];
        var revenues = [];
        
        <?php foreach ($monthly_revenue as $revenue): ?>
            months.push('<?php echo $revenue['month'] . '/' . $revenue['year']; ?>');
            revenues.push(<?php echo $revenue['monthly_revenue']; ?>);
        <?php endforeach; ?>
        
        // Tạo biểu đồ doanh thu
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months.reverse(),
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenues.reverse(),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html> 