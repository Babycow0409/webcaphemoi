<?php
$page_title = "Chi tiết tính lương";
$header_icon = "file-invoice-dollar";
include '../../includes/admin-header.php';

// Lấy ID tính lương từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID tính lương không hợp lệ";
    header("Location: index.php");
    exit();
}

// Lấy thông tin tính lương
$sql = "SELECT sc.*, e.fullname, e.employee_code, e.salary as base_salary_emp, e.position, e.department
        FROM salary_calculations sc
        JOIN employees e ON sc.employee_id = e.id
        WHERE sc.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Không tìm thấy bản tính lương";
    header("Location: index.php");
    exit();
}

$salary = $result->fetch_assoc();
$stmt->close();

// Lấy danh sách ca làm việc trong tháng
$shifts_sql = "SELECT sa.*, ws.shift_name, ws.start_time, ws.end_time, ws.hourly_rate
                FROM shift_assignments sa
                JOIN work_shifts ws ON sa.shift_id = ws.id
                WHERE sa.employee_id = ? 
                AND sa.status = 'completed'
                AND DATE_FORMAT(sa.work_date, '%Y-%m') = ?
                ORDER BY sa.work_date ASC";
$shifts_stmt = $conn->prepare($shifts_sql);
$shifts_stmt->bind_param("is", $salary['employee_id'], $salary['calculation_month']);
$shifts_stmt->execute();
$shifts_result = $shifts_stmt->get_result();
?>

<!-- Main content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-invoice-dollar"></i> Chi tiết tính lương
        </h5>
        <div>
            <a href="calculate.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <a href="index.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%" class="bg-light">Nhân viên</th>
                        <td><strong><?php echo htmlspecialchars($salary['fullname']); ?></strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Mã nhân viên</th>
                        <td><?php echo htmlspecialchars($salary['employee_code']); ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Chức vụ</th>
                        <td><?php echo htmlspecialchars($salary['position'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Phòng ban</th>
                        <td><?php echo htmlspecialchars($salary['department'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Tháng tính lương</th>
                        <td><strong><?php echo date('m/Y', strtotime($salary['calculation_month'] . '-01')); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%" class="bg-light">Lương cơ bản</th>
                        <td><?php echo number_format($salary['base_salary'], 0, ',', '.'); ?> VNĐ</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Số ca đã làm</th>
                        <td><?php echo $salary['total_shifts']; ?> ca</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Tổng giờ làm việc</th>
                        <td><?php echo number_format($salary['total_hours'], 1); ?> giờ</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Lương ca</th>
                        <td><?php echo number_format($salary['shift_earnings'], 0, ',', '.'); ?> VNĐ</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Thưởng</th>
                        <td><?php echo number_format($salary['bonus'], 0, ',', '.'); ?> VNĐ</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Khấu trừ</th>
                        <td class="text-danger">-<?php echo number_format($salary['deductions'], 0, ',', '.'); ?> VNĐ</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Tổng lương</th>
                        <td><strong class="text-success" style="font-size: 1.2em;"><?php echo number_format($salary['total_salary'], 0, ',', '.'); ?> VNĐ</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Trạng thái</th>
                        <td>
                            <?php
                            $status_class = 'badge-secondary';
                            $status_text = 'Nháp';
                            switch($salary['status']) {
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
                    </tr>
                </table>
            </div>
        </div>

        <?php if ($shifts_result->num_rows > 0): ?>
        <div class="mt-4">
            <h6 class="border-bottom pb-2">Chi tiết ca làm việc trong tháng</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Ngày</th>
                            <th>Ca làm việc</th>
                            <th>Giờ làm việc</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Số giờ</th>
                            <th>Lương ca</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($shift = $shifts_result->fetch_assoc()): 
                            $shift_earn = $shift['hours_worked'] * $shift['hourly_rate'];
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($shift['work_date'])); ?></td>
                            <td>
                                <?php echo htmlspecialchars($shift['shift_name']); ?><br>
                                <small class="text-muted">
                                    <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                                    <?php echo date('H:i', strtotime($shift['end_time'])); ?>
                                </small>
                            </td>
                            <td>
                                <?php echo date('H:i', strtotime($shift['start_time'])); ?> - 
                                <?php echo date('H:i', strtotime($shift['end_time'])); ?>
                            </td>
                            <td><?php echo $shift['check_in_time'] ? date('d/m/Y H:i', strtotime($shift['check_in_time'])) : '-'; ?></td>
                            <td><?php echo $shift['check_out_time'] ? date('d/m/Y H:i', strtotime($shift['check_out_time'])) : '-'; ?></td>
                            <td><?php echo number_format($shift['hours_worked'], 1); ?>h</td>
                            <td><?php echo number_format($shift_earn, 0, ',', '.'); ?>đ</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($salary['notes'])): ?>
        <div class="mt-4">
            <h6 class="border-bottom pb-2">Ghi chú</h6>
            <p><?php echo nl2br(htmlspecialchars($salary['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="mt-4">
            <p class="text-muted">
                <small>
                    <i class="fas fa-clock"></i> 
                    <?php if ($salary['calculated_at']): ?>
                        Tính lương lúc: <?php echo date('d/m/Y H:i', strtotime($salary['calculated_at'])); ?>
                    <?php else: ?>
                        Tạo lúc: <?php echo date('d/m/Y H:i', strtotime($salary['created_at'])); ?>
                    <?php endif; ?>
                </small>
            </p>
        </div>
    </div>
</div>

<?php
$shifts_stmt->close();
include '../../includes/admin-footer.php';
?>




