<?php
$page_title = "Phân ca làm việc";
$header_icon = "calendar-alt";
include '../../includes/admin-header.php';

// Kiểm tra bảng shift_assignments có tồn tại không
$table_exists = $conn->query("SHOW TABLES LIKE 'shift_assignments'")->num_rows > 0;
if (!$table_exists) {
    echo '<div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Bảng shift_assignments chưa được tạo. Vui lòng chạy <a href="../setup_tables.php">setup_tables.php</a> để tạo bảng.
    </div>';
    include '../../includes/admin-footer.php';
    exit();
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

// Xử lý phân trang
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(e.full_name LIKE ? OR e.employee_code LIKE ? OR ws.shift_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "sa.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($date_filter)) {
    $where_conditions[] = "sa.work_date = ?";
    $params[] = $date_filter;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total 
              FROM shift_assignments sa
              JOIN employees e ON sa.employee_id = e.id
              JOIN work_shifts ws ON sa.shift_id = ws.id
              $where_clause";
              
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

// Lấy danh sách phân ca
$sql = "SELECT sa.*, e.full_name, e.employee_code, ws.shift_name, ws.start_time, ws.end_time, ws.hourly_rate
        FROM shift_assignments sa
        JOIN employees e ON sa.employee_id = e.id
        JOIN work_shifts ws ON sa.shift_id = ws.id
        $where_clause
        ORDER BY sa.work_date DESC, sa.id DESC
        LIMIT ? OFFSET ?";
        
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt"></i> Danh sách phân ca làm việc
        </h5>
        <a href="assign.php" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Phân ca mới
        </a>
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
            <div class="col-md-12">
                <form action="" method="GET" class="form-inline">
                    <div class="input-group mr-2 mb-2">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm kiếm theo tên NV, mã NV, tên ca..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <input type="date" name="date" class="form-control mr-2 mb-2"
                        value="<?php echo htmlspecialchars($date_filter); ?>" placeholder="Lọc theo ngày">
                    <select name="status" class="form-control mr-2 mb-2" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="scheduled" <?php echo $status_filter == 'scheduled' ? 'selected' : ''; ?>>Đã lên
                            lịch</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Đã hoàn
                            thành</option>
                        <option value="absent" <?php echo $status_filter == 'absent' ? 'selected' : ''; ?>>Vắng mặt
                        </option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy
                        </option>
                    </select>
                    <?php if (!empty($search) || !empty($status_filter) || !empty($date_filter)): ?>
                    <a href="index.php" class="btn btn-secondary mb-2">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Assignments table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-employee">
                <thead>
                    <tr>
                        <th>Ngày làm việc</th>
                        <th>Nhân viên</th>
                        <th>Ca làm việc</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Số giờ</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['work_date'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['employee_code']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['shift_name']); ?><br>
                            <small class="text-muted shift-time">
                                <?php echo date('H:i', strtotime($row['start_time'])); ?> -
                                <?php echo date('H:i', strtotime($row['end_time'])); ?>
                            </small>
                        </td>
                        <td>
                            <?php if ($row['check_in_time']): ?>
                            <?php echo date('d/m/Y H:i', strtotime($row['check_in_time'])); ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['check_out_time']): ?>
                            <?php echo date('d/m/Y H:i', strtotime($row['check_out_time'])); ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['hours_worked'] > 0): ?>
                            <strong><?php echo number_format($row['hours_worked'], 1); ?>h</strong>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $status_class = 'badge-secondary';
                                $status_text = 'Đã lên lịch';
                                switch($row['status']) {
                                    case 'completed':
                                        $status_class = 'badge-success';
                                        $status_text = 'Đã hoàn thành';
                                        break;
                                    case 'absent':
                                        $status_class = 'badge-danger';
                                        $status_text = 'Vắng mặt';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'badge-warning';
                                        $status_text = 'Đã hủy';
                                        break;
                                }
                                ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary"
                                    title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa phân ca này?');" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-inbox"></i> Không tìm thấy phân ca nào
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
                    <a class="page-link"
                        href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo !empty($date_filter) ? '&date='.$date_filter : ''; ?>">
                        Trước
                    </a>
                </li>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo !empty($date_filter) ? '&date='.$date_filter : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo !empty($date_filter) ? '&date='.$date_filter : ''; ?>">
                        Sau
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <div class="text-muted">
            Hiển thị <?php echo $result->num_rows; ?> / <?php echo $total_records; ?> phân ca
        </div>
    </div>
</div>

<?php
$stmt->close();
include '../../includes/admin-footer.php';
?>