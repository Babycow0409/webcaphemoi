<?php
$page_title = "Quản lý người dùng";

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
    
    .search-box {
        margin-bottom: 20px;
        color: #000;
    }

    .table {
        background-color: white;
        color: #000;
    }
    
    .table th {
        background-color: #f8f9fa;
        color: #495057;
    }
    
    .table td {
        color: #000;
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
    
    .alert {
        color: #000;
    }
</style>

<div class="content-wrapper">
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <?php 
    echo $_SESSION['success_message'];
    unset($_SESSION['success_message']);
    ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <?php echo htmlspecialchars($_GET['error']); ?>
</div>
<?php endif; ?>

<!-- Search box -->
<div class="search-box d-flex justify-content-between align-items-center">
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
                        <a href="add_user.php" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Thêm người dùng
                        </a>
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

<?php
// Đóng kết nối
$conn->close();

// Include footer
require_once __DIR__ . '/../includes/admin-footer.php';
?>