<?php
$page_title = "Tính lương nhân viên";
$header_icon = "money-bill-wave";
include '../../includes/admin-header.php';

// Kiểm tra bảng salary_calculations có tồn tại không
$table_exists = $conn->query("SHOW TABLES LIKE 'salary_calculations'")->num_rows > 0;
if (!$table_exists) {
    echo '<div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Bảng salary_calculations chưa được tạo. Vui lòng chạy <a href="../setup_tables.php">setup_tables.php</a> để tạo bảng.
    </div>';
    include '../../includes/admin-footer.php';
    exit();
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$month_filter = isset($_GET['month']) ? trim($_GET['month']) : date('Y-m');
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Xử lý phân trang
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn
$where_conditions = ["sc.calculation_month = ?"];
$params = [$month_filter];
$types = 's';

if (!empty($search)) {
    $where_conditions[] = "(e.full_name LIKE ? OR e.employee_code LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "sc.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total 
              FROM salary_calculations sc
              JOIN employees e ON sc.employee_id = e.id
              $where_clause";
              
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_records / $limit);

// Lấy danh sách tính lương
$sql = "SELECT sc.*, e.full_name, e.employee_code, e.base_salary
        FROM salary_calculations sc
        JOIN employees e ON sc.employee_id = e.id
        $where_clause
        ORDER BY sc.calculation_month DESC, e.full_name ASC
        LIMIT ? OFFSET ?";
        
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Tính tổng lương trong tháng
$total_sql = "SELECT SUM(total_salary) as total FROM salary_calculations WHERE calculation_month = ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("s", $month_filter);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_salary = $total_result->fetch_assoc()['total'] ?? 0;
$total_stmt->close();
?>

<!-- Main content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-money-bill-wave"></i> Danh sách tính lương
        </h5>
        <a href="calculate.php" class="btn btn-success btn-sm">
            <i class="fas fa-calculator"></i> Tính lương mới
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

        <!-- Summary Card -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="stats-card-employee">
                    <h6>Tổng lương tháng <?php echo date('m/Y', strtotime($month_filter . '-01')); ?></h6>
                    <h3><?php echo number_format($total_salary, 0, ',', '.'); ?> VNĐ</h3>
                </div>
            </div>
        </div>

        <!-- Search and filter -->
        <div class="row mb-3">
            <div class="col-md-12">
                <form action="" method="GET" class="form-inline">
                    <div class="input-group mr-2 mb-2">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm kiếm theo tên NV, mã NV..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <input type="month" name="month" class="form-control mr-2 mb-2"
                        value="<?php echo htmlspecialchars($month_filter); ?>">
                    <select name="status" class="form-control mr-2 mb-2" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Nháp</option>
                        <option value="calculated" <?php echo $status_filter == 'calculated' ? 'selected' : ''; ?>>Đã
                            tính</option>
                        <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Đã thanh toán
                        </option>
                    </select>
                    <?php if (!empty($search) || !empty($status_filter) || $month_filter != date('Y-m')): ?>
                    <a href="index.php" class="btn btn-secondary mb-2">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Salary table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-employee">
                <thead>
                    <tr>
                        <th>Tháng</th>
                        <th>Nhân viên</th>
                        <th>Lương cơ bản</th>
                        <th>Số ca</th>
                        <th>Tổng giờ</th>
                        <th>Lương ca</th>
                        <th>Thưởng</th>
                        <th>Khấu trừ</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('m/Y', strtotime($row['calculation_month'] . '-01')); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['employee_code']); ?></small>
                        </td>
                        <td><?php echo number_format($row['base_salary'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo $row['total_shifts']; ?></td>
                        <td><?php echo number_format($row['total_hours'], 1); ?>h</td>
                        <td><?php echo number_format($row['shift_earnings'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo number_format($row['bonus'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo number_format($row['deductions'], 0, ',', '.'); ?>đ</td>
                        <td><strong
                                class="salary-amount"><?php echo number_format($row['total_salary'], 0, ',', '.'); ?>đ</strong>
                        </td>
                        <td>
                            <?php
                                $status_class = 'badge-secondary';
                                $status_text = 'Nháp';
                                switch($row['status']) {
                                    case 'calculated':
                                        $status_class = 'badge-info';
                                        $status_text = 'Đã tính';
                                        break;
                                    case 'paid':
                                        $status_class = 'badge-success';
                                        $status_text = 'Đã thanh toán';
                                        break;
                                }
                                ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info"
                                    title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($row['status'] != 'paid'): ?>
                                <a href="calculate.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary"
                                    title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">
                            <i class="fas fa-inbox"></i> Không tìm thấy bản tính lương nào
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
                        href="?page=<?php echo $page-1; ?>&month=<?php echo $month_filter; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                        Trước
                    </a>
                </li>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?php echo $i; ?>&month=<?php echo $month_filter; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>

                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="?page=<?php echo $page+1; ?>&month=<?php echo $month_filter; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?>">
                        Sau
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <div class="text-muted">
            Hiển thị <?php echo $result->num_rows; ?> / <?php echo $total_records; ?> bản tính lương
        </div>
    </div>
</div>

<?php
$stmt->close();
include '../../includes/admin-footer.php';
?>