<?php
$page_title = "Phân ca làm việc";
$header_icon = "calendar-plus";
include '../../includes/admin-header.php';

$errors = [];

// Lấy danh sách nhân viên và ca làm việc
$employees_sql = "SELECT id, employee_code, fullname FROM employees WHERE status = 'active' ORDER BY fullname";
$employees_result = $conn->query($employees_sql);

$shifts_sql = "SELECT id, shift_name, start_time, end_time FROM work_shifts WHERE status = 'active' ORDER BY start_time";
$shifts_result = $conn->query($shifts_sql);

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
    $shift_id = isset($_POST['shift_id']) ? (int)$_POST['shift_id'] : 0;
    $work_date = trim($_POST['work_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    if ($employee_id <= 0) {
        $errors[] = "Vui lòng chọn nhân viên";
    }
    
    if ($shift_id <= 0) {
        $errors[] = "Vui lòng chọn ca làm việc";
    }
    
    if (empty($work_date)) {
        $errors[] = "Vui lòng chọn ngày làm việc";
    }
    
    // Kiểm tra nhân viên đã được phân ca trong ngày này chưa
    if (empty($errors)) {
        $check_sql = "SELECT id FROM shift_assignments WHERE employee_id = ? AND work_date = ? AND status != 'cancelled'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $employee_id, $work_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Nhân viên này đã được phân ca trong ngày " . date('d/m/Y', strtotime($work_date));
        }
        $check_stmt->close();
    }
    
    // Nếu không có lỗi, thêm phân ca
    if (empty($errors)) {
        $sql = "INSERT INTO shift_assignments (employee_id, shift_id, work_date, notes, status) 
                VALUES (?, ?, ?, ?, 'scheduled')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $employee_id, $shift_id, $work_date, $notes);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Phân ca thành công!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Lỗi khi phân ca: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calendar-plus"></i> Phân ca làm việc mới
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul class="mb-0">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="employee_id">Nhân viên <span class="text-danger">*</span></label>
                        <select class="form-control" id="employee_id" name="employee_id" required>
                            <option value="">-- Chọn nhân viên --</option>
                            <?php while($emp = $employees_result->fetch_assoc()): ?>
                                <option value="<?php echo $emp['id']; ?>" 
                                        <?php echo (isset($_POST['employee_id']) && $_POST['employee_id'] == $emp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['employee_code'] . ' - ' . $emp['fullname']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shift_id">Ca làm việc <span class="text-danger">*</span></label>
                        <select class="form-control" id="shift_id" name="shift_id" required>
                            <option value="">-- Chọn ca làm việc --</option>
                            <?php 
                            $shifts_result->data_seek(0); // Reset pointer
                            while($shift = $shifts_result->fetch_assoc()): ?>
                                <option value="<?php echo $shift['id']; ?>"
                                        <?php echo (isset($_POST['shift_id']) && $_POST['shift_id'] == $shift['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($shift['shift_name'] . ' (' . date('H:i', strtotime($shift['start_time'])) . ' - ' . date('H:i', strtotime($shift['end_time'])) . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="work_date">Ngày làm việc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="work_date" name="work_date" 
                               value="<?php echo htmlspecialchars($_POST['work_date'] ?? date('Y-m-d')); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"
                          placeholder="Ghi chú về phân ca này..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu phân ca
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>




