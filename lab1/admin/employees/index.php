<?php
$page_title = "Quản lý nhân viên";
$header_icon = "user-tie";
include '../includes/admin-header.php';

// Kiểm tra bảng employees có tồn tại không
$table_exists = $conn->query("SHOW TABLES LIKE 'employees'")->num_rows > 0;
if (!$table_exists) {
    echo '<div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Bảng employees chưa được tạo. Vui lòng chạy <a href="setup_tables.php">setup_tables.php</a> để tạo bảng.
    </div>';
    include '../includes/admin-footer.php';
    exit();
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Xử lý phân trang
$limit = 10; // Số bản ghi trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(fullname LIKE ? OR email LIKE ? OR employee_code LIKE ? OR position LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM employees $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_sql);
    $total_records = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $limit);

// Lấy danh sách nhân viên
$sql = "SELECT * FROM employees $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user-tie"></i> Danh sách nhân viên
        </h5>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Search and filter -->
        <div class="row mb-3">
            <div class="col-md-8">
                <form action="" method="GET" class="form-inline">
                    <div class="input-group mr-2">
                        <input type="text" name="search" class="form-control"
                               placeholder="Tìm kiếm theo tên, email, mã NV, chức vụ..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                    <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Đang làm việc</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Đã nghỉ việc</option>
                    </select>
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <a href="add.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Thêm nhân viên
                </a>
            </div>
        </div>

        <!-- Employees table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-employee">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Chức vụ</th>
                        <th>Phòng ban</th>
                        <th>Phân quyền</th>
                        <th>Lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['employee_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['position'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['department'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge badge-employee <?php echo ($row['role'] ?? 'employee') == 'manager' ? 'badge-manager' : 'badge-employee-regular'; ?>">
                                    <?php echo ($row['role'] ?? 'employee') == 'manager' ? 'Quản lý' : 'Nhân viên'; ?>
                                </span>
                            </td>
                            <td><?php echo $row['salary'] ? number_format($row['salary'], 0, ',', '.') . 'đ' : 'N/A'; ?></td>
                            <td>
                                <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo $row['status'] == 'active' ? 'Đang làm việc' : 'Đã nghỉ việc'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-action-group">
                                    <a href="view.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên <?php echo htmlspecialchars($row['fullname']); ?>?');"
                                       title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <i class="fas fa-inbox"></i> Không tìm thấy nhân viên nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                        Trước
                    </a>
                </li>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                        Sau
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <div class="text-muted">
            Hiển thị <?php echo $result->num_rows; ?> / <?php echo $total_records; ?> nhân viên
        </div>
    </div>
</div>

<?php
$stmt->close();
include '../includes/admin-footer.php';
?>
