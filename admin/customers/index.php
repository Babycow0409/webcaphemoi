<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../classes/Customer.php';
require_once __DIR__ . '/../../classes/LoyaltyPoint.php';

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Xây dựng query
$where = "WHERE role = 'customer'";
$params = [];
$types = "";

if (!empty($search)) {
    $where .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($level)) {
    $where .= " AND customer_level = ?";
    $params[] = $level;
    $types .= "s";
}

// Đếm tổng số khách hàng
$countSql = "SELECT COUNT(*) as total FROM users $where";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalCustomers = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalCustomers / $limit);

// Lấy danh sách khách hàng
$sql = "SELECT * FROM users $where ORDER BY total_spent DESC, created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$customers = [];
$loyaltyPoint = new LoyaltyPoint($conn);

while ($row = $result->fetch_assoc()) {
    $customer = new Customer($conn, $row['id']);
    $row['available_points'] = $loyaltyPoint->getAvailablePoints($row['id']);
    $row['total_orders'] = $customer->getTotalOrders();
    $customers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - Admin</title>
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
        .badge-level {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-bronze { background-color: #cd7f32; color: white; }
        .badge-silver { background-color: #c0c0c0; color: white; }
        .badge-gold { background-color: #ffd700; color: black; }
        .badge-platinum { background-color: #e5e4e2; color: black; }
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
                            <i class="fas fa-user-friends mr-2"></i> Khách hàng
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
                <div class="header">
                    <h2><i class="fas fa-user-friends mr-2"></i>Quản lý khách hàng</h2>
                </div>

                <div class="content">
                    <!-- Filter và Search -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tìm kiếm</label>
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="Tên, email, SĐT..." value="<?php echo htmlspecialchars($search); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cấp độ</label>
                                            <select name="level" class="form-control">
                                                <option value="">Tất cả</option>
                                                <option value="bronze" <?php echo $level == 'bronze' ? 'selected' : ''; ?>>Đồng</option>
                                                <option value="silver" <?php echo $level == 'silver' ? 'selected' : ''; ?>>Bạc</option>
                                                <option value="gold" <?php echo $level == 'gold' ? 'selected' : ''; ?>>Vàng</option>
                                                <option value="platinum" <?php echo $level == 'platinum' ? 'selected' : ''; ?>>Bạch Kim</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-1"></i>Tìm kiếm
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <a href="index.php" class="btn btn-secondary btn-block">
                                                <i class="fas fa-redo mr-1"></i>Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danh sách khách hàng -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Danh sách khách hàng (<?php echo $totalCustomers; ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Khách hàng</th>
                                            <th>Email</th>
                                            <th>SĐT</th>
                                            <th>Cấp độ</th>
                                            <th>Tổng chi tiêu</th>
                                            <th>Số đơn</th>
                                            <th>Điểm tích lũy</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($customers)): ?>
                                            <tr>
                                                <td colspan="10" class="text-center">Không tìm thấy khách hàng nào.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo $customer['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($customer['fullname']); ?></strong>
                                                        <?php if ($customer['active'] == 0): ?>
                                                            <span class="badge badge-secondary ml-2">Đã khóa</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></td>
                                                    <td>
                                                        <span class="badge-level badge-<?php echo $customer['customer_level']; ?>">
                                                            <?php
                                                            $levels = ['bronze' => 'Đồng', 'silver' => 'Bạc', 'gold' => 'Vàng', 'platinum' => 'Bạch Kim'];
                                                            echo $levels[$customer['customer_level']] ?? 'Đồng';
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $customer['total_orders']; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-warning"><?php echo number_format($customer['available_points'], 0, ',', '.'); ?></span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                                    <td>
                                                        <a href="view.php?id=<?php echo $customer['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="orders.php?id=<?php echo $customer['id']; ?>" 
                                                           class="btn btn-sm btn-primary" title="Lịch sử đơn hàng">
                                                            <i class="fas fa-shopping-bag"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav>
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&level=<?php echo $level; ?>">Trước</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&level=<?php echo $level; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&level=<?php echo $level; ?>">Sau</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
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

