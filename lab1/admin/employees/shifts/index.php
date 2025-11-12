<?php
$page_title = "Quản lý ca làm việc";
$header_icon = "clock";
include '../../includes/admin-header.php';

// Kiểm tra bảng work_shifts có tồn tại không
$table_exists = $conn->query("SHOW TABLES LIKE 'work_shifts'")->num_rows > 0;
if (!$table_exists) {
    echo '<div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Bảng work_shifts chưa được tạo. Vui lòng chạy <a href="../setup_tables.php">setup_tables.php</a> để tạo bảng.
    </div>';
    include '../../includes/admin-footer.php';
    exit();
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Xử lý phân trang
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(shift_name LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM work_shifts $where_clause";
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

// Lấy danh sách ca làm việc
$sql = "SELECT * FROM work_shifts $where_clause ORDER BY start_time ASC LIMIT ? OFFSET ?";
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
            <i class="fas fa-clock"></i> Danh sách ca làm việc
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
                               placeholder="Tìm kiếm theo tên ca, mô tả..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                    <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Ngừng hoạt động</option>
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
                    <i class="fas fa-plus"></i> Thêm ca làm việc
                </a>
            </div>
        </div>

        <!-- Shifts table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-employee">
                <thead>
                    <tr>
                        <th>Tên ca</th>
                        <th>Giờ bắt đầu</th>
                        <th>Giờ kết thúc</th>
                        <th>Thời gian làm việc</th>
                        <th>Lương/giờ</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $start = new DateTime($row['start_time']);
                            $end = new DateTime($row['end_time']);
                            if ($end < $start) {
                                $end->modify('+1 day');
                            }
                            $duration = $start->diff($end);
                            $hours = $duration->h + ($duration->i / 60);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['shift_name']); ?></strong></td>
                            <td><span class="shift-time"><?php echo date('H:i', strtotime($row['start_time'])); ?></span></td>
                            <td><span class="shift-time"><?php echo date('H:i', strtotime($row['end_time'])); ?></span></td>
                            <td><strong><?php echo number_format($hours, 1); ?> giờ</strong></td>
                            <td><?php echo number_format($row['hourly_rate'], 0, ',', '.'); ?>đ/giờ</td>
                            <td>
                                <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo $row['status'] == 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-sm btn-primary" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa ca <?php echo htmlspecialchars($row['shift_name']); ?>?');"
                                       title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <i class="fas fa-inbox"></i> Không tìm thấy ca làm việc nào
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
            Hiển thị <?php echo $result->num_rows; ?> / <?php echo $total_records; ?> ca làm việc
        </div>
    </div>
</div>

<?php
$stmt->close();
include '../../includes/admin-footer.php';
?>

