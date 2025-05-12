<?php
session_start();

// Kiểm tra đăng nhập admin
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

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $where_clause = "WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR fullname LIKE '%$search%'";
}

// Lấy danh sách người dùng
$sql = "SELECT * FROM users $where_clause ORDER BY id DESC";
$result = $conn->query($sql);

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin</title>
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

    .sidebar .nav-link.active {
        color: white;
        background-color: rgba(255, 255, 255, .2);
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

    .search-box {
        margin-bottom: 20px;
    }

    .table th {
        background-color: #f8f9fa;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .btn-group .btn {
        margin: 0 2px;
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
                <div class="header">
                    <h2>Quản lý người dùng</h2>
                </div>

                <div class="content">
                    <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Search box -->
                    <div class="search-box">
                        <form action="" method="GET" class="form-inline">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Tìm kiếm theo tên, email..."
                                    value="<?php echo htmlspecialchars($search); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Users table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Họ tên</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fullname'] ?? $row['name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo $row['role'] == 'admin' ? 'badge-danger' : 'badge-info'; ?>">
                                            <?php echo $row['role'] == 'admin' ? 'Quản trị viên' : 'Khách hàng'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (isset($row['active'])): ?>
                                        <span
                                            class="badge <?php echo $row['active'] == 1 ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $row['active'] == 1 ? 'Đang hoạt động' : 'Đã khóa'; ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo isset($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"
                                                title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($row['role'] !== 'admin'): ?>
                                            <?php if (isset($row['active'])): ?>
                                            <?php if ($row['active'] == 1): ?>
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick="toggleUserStatus(<?php echo $row['id']; ?>, 'deactivate')"
                                                title="Khóa tài khoản">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="toggleUserStatus(<?php echo $row['id']; ?>, 'activate')"
                                                title="Mở khóa tài khoản">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Không tìm thấy người dùng nào.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    function toggleUserStatus(userId, action) {
        const confirmMessage = action === 'activate' ?
            'Bạn có chắc chắn muốn mở khóa tài khoản này? Người dùng sẽ có thể đăng nhập và sử dụng hệ thống.' :
            'Bạn có chắc chắn muốn khóa tài khoản này? Người dùng sẽ không thể đăng nhập và sử dụng hệ thống.';

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
</body>

</html>