<?php
$page_title = "Chi tiết nhân viên";
$header_icon = "user";
include '../includes/admin-header.php';

// Lấy ID nhân viên từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID nhân viên không hợp lệ";
    header("Location: index.php");
    exit();
}

// Lấy thông tin nhân viên
$sql = "SELECT * FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Không tìm thấy nhân viên";
    header("Location: index.php");
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();
?>

<!-- Main content -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-user"></i> Chi tiết nhân viên
        </h5>
        <div>
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
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
                        <th width="40%" class="bg-light">Mã nhân viên</th>
                        <td><strong><?php echo htmlspecialchars($employee['employee_code']); ?></strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Họ và tên</th>
                        <td><?php echo htmlspecialchars($employee['fullname']); ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Email</th>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>">
                                <?php echo htmlspecialchars($employee['email']); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Số điện thoại</th>
                        <td>
                            <?php if (!empty($employee['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($employee['phone']); ?>">
                                    <?php echo htmlspecialchars($employee['phone']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Chưa cập nhật</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Địa chỉ</th>
                        <td><?php echo !empty($employee['address']) ? htmlspecialchars($employee['address']) : '<span class="text-muted">Chưa cập nhật</span>'; ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Thành phố</th>
                        <td><?php echo !empty($employee['city']) ? htmlspecialchars($employee['city']) : '<span class="text-muted">Chưa cập nhật</span>'; ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%" class="bg-light">Chức vụ</th>
                        <td><?php echo !empty($employee['position']) ? htmlspecialchars($employee['position']) : '<span class="text-muted">Chưa cập nhật</span>'; ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Phòng ban</th>
                        <td><?php echo !empty($employee['department']) ? htmlspecialchars($employee['department']) : '<span class="text-muted">Chưa cập nhật</span>'; ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Lương</th>
                        <td>
                            <?php if (!empty($employee['salary'])): ?>
                                <strong class="text-success"><?php echo number_format($employee['salary'], 0, ',', '.'); ?> VNĐ</strong>
                            <?php else: ?>
                                <span class="text-muted">Chưa cập nhật</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Ngày vào làm</th>
                        <td>
                            <?php if (!empty($employee['hire_date'])): ?>
                                <?php echo date('d/m/Y', strtotime($employee['hire_date'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Chưa cập nhật</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Trạng thái</th>
                        <td>
                            <span class="badge <?php echo $employee['status'] == 'active' ? 'badge-success' : 'badge-secondary'; ?>">
                                <?php echo $employee['status'] == 'active' ? 'Đang làm việc' : 'Đã nghỉ việc'; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Ngày tạo</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($employee['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <h6 class="border-bottom pb-2">Thông tin bổ sung</h6>
            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>Ngày cập nhật cuối:</strong> <?php echo date('d/m/Y H:i', strtotime($employee['updated_at'])); ?></p>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($employee['hire_date'])): ?>
                        <?php
                        $hire_date = new DateTime($employee['hire_date']);
                        $now = new DateTime();
                        $interval = $hire_date->diff($now);
                        $years = $interval->y;
                        $months = $interval->m;
                        ?>
                        <p><strong>Thời gian làm việc:</strong> 
                            <?php 
                            if ($years > 0) echo $years . ' năm ';
                            if ($months > 0) echo $months . ' tháng';
                            if ($years == 0 && $months == 0) echo 'Dưới 1 tháng';
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa thông tin
            </a>
            <a href="delete.php?id=<?php echo $id; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên <?php echo htmlspecialchars($employee['fullname']); ?>?');">
                <i class="fas fa-trash"></i> Xóa nhân viên
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Danh sách nhân viên
            </a>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>

