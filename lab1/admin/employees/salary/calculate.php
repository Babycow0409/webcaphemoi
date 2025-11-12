<?php
$page_title = "Tính lương nhân viên";
$header_icon = "calculator";
include '../../includes/admin-header.php';

$errors = [];
$is_edit = false;
$calculation_id = null;

// Kiểm tra nếu là chỉnh sửa
if (isset($_GET['id'])) {
    $calculation_id = (int)$_GET['id'];
    $is_edit = true;
    
    $sql = "SELECT sc.*, e.fullname, e.employee_code, e.salary as base_salary_emp
            FROM salary_calculations sc
            JOIN employees e ON sc.employee_id = e.id
            WHERE sc.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $calculation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $_SESSION['error_message'] = "Không tìm thấy bản tính lương";
        header("Location: index.php");
        exit();
    }
    
    $calculation = $result->fetch_assoc();
    $stmt->close();
}

// Lấy danh sách nhân viên
$employees_sql = "SELECT id, employee_code, fullname, salary FROM employees WHERE status = 'active' ORDER BY fullname";
$employees_result = $conn->query($employees_sql);

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
    $calculation_month = trim($_POST['calculation_month'] ?? '');
    $base_salary = isset($_POST['base_salary']) ? (float)$_POST['base_salary'] : 0;
    $bonus = isset($_POST['bonus']) ? (float)$_POST['bonus'] : 0;
    $deductions = isset($_POST['deductions']) ? (float)$_POST['deductions'] : 0;
    $notes = trim($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    // Validate
    if ($employee_id <= 0) {
        $errors[] = "Vui lòng chọn nhân viên";
    }
    
    if (empty($calculation_month) || !preg_match('/^\d{4}-\d{2}$/', $calculation_month)) {
        $errors[] = "Vui lòng chọn tháng tính lương (định dạng: YYYY-MM)";
    }
    
    // Tính lương từ ca làm việc
    $total_shifts = 0;
    $total_hours = 0;
    $shift_earnings = 0;
    
    if (empty($errors) && $employee_id > 0 && !empty($calculation_month)) {
        // Lấy tất cả ca đã hoàn thành trong tháng
        $shifts_sql = "SELECT sa.hours_worked, ws.hourly_rate
                       FROM shift_assignments sa
                       JOIN work_shifts ws ON sa.shift_id = ws.id
                       WHERE sa.employee_id = ? 
                       AND sa.status = 'completed'
                       AND DATE_FORMAT(sa.work_date, '%Y-%m') = ?";
        $shifts_stmt = $conn->prepare($shifts_sql);
        $shifts_stmt->bind_param("is", $employee_id, $calculation_month);
        $shifts_stmt->execute();
        $shifts_result = $shifts_stmt->get_result();
        
        while ($shift = $shifts_result->fetch_assoc()) {
            if ($shift['hours_worked'] > 0) {
                $total_shifts++;
                $total_hours += $shift['hours_worked'];
                $shift_earnings += $shift['hours_worked'] * $shift['hourly_rate'];
            }
        }
        $shifts_stmt->close();
    }
    
    // Tính tổng lương
    $total_salary = $base_salary + $shift_earnings + $bonus - $deductions;
    
    // Nếu không có lỗi, lưu
    if (empty($errors)) {
        if ($is_edit && $calculation_id) {
            // Cập nhật
            $sql = "UPDATE salary_calculations SET 
                    base_salary = ?, total_shifts = ?, total_hours = ?, shift_earnings = ?, 
                    bonus = ?, deductions = ?, total_salary = ?, status = ?, notes = ?,
                    calculated_at = NOW()
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddddddsssi", $base_salary, $total_shifts, $total_hours, $shift_earnings,
                             $bonus, $deductions, $total_salary, $status, $notes, $calculation_id);
        } else {
            // Kiểm tra xem đã tính lương cho nhân viên trong tháng này chưa
            $check_sql = "SELECT id FROM salary_calculations WHERE employee_id = ? AND calculation_month = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("is", $employee_id, $calculation_month);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "Đã tính lương cho nhân viên này trong tháng " . date('m/Y', strtotime($calculation_month . '-01'));
            }
            $check_stmt->close();
            
            if (empty($errors)) {
                // Thêm mới
                $sql = "INSERT INTO salary_calculations 
                        (employee_id, calculation_month, base_salary, total_shifts, total_hours, 
                         shift_earnings, bonus, deductions, total_salary, status, notes, calculated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isdddddddss", $employee_id, $calculation_month, $base_salary, 
                                 $total_shifts, $total_hours, $shift_earnings, $bonus, $deductions, 
                                 $total_salary, $status, $notes);
            }
        }
        
        if (empty($errors) && isset($stmt)) {
            if ($stmt->execute()) {
                $_SESSION['success_message'] = $is_edit ? "Cập nhật tính lương thành công!" : "Tính lương thành công!";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Lỗi khi lưu: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Nếu là edit, load dữ liệu vào form
if ($is_edit && isset($calculation)) {
    $_POST['employee_id'] = $calculation['employee_id'];
    $_POST['calculation_month'] = $calculation['calculation_month'];
    $_POST['base_salary'] = $calculation['base_salary'];
    $_POST['bonus'] = $calculation['bonus'];
    $_POST['deductions'] = $calculation['deductions'];
    $_POST['notes'] = $calculation['notes'];
    $_POST['status'] = $calculation['status'];
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calculator"></i> <?php echo $is_edit ? 'Chỉnh sửa tính lương' : 'Tính lương mới'; ?>
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

        <form method="POST" action="" id="salaryForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="employee_id">Nhân viên <span class="text-danger">*</span></label>
                        <select class="form-control" id="employee_id" name="employee_id" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                            <option value="">-- Chọn nhân viên --</option>
                            <?php 
                            $employees_result->data_seek(0);
                            while($emp = $employees_result->fetch_assoc()): ?>
                                <option value="<?php echo $emp['id']; ?>" 
                                        data-salary="<?php echo $emp['salary']; ?>"
                                        <?php echo (isset($_POST['employee_id']) && $_POST['employee_id'] == $emp['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['employee_code'] . ' - ' . $emp['fullname']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <?php if ($is_edit): ?>
                            <input type="hidden" name="employee_id" value="<?php echo $calculation['employee_id']; ?>">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="calculation_month">Tháng tính lương <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" id="calculation_month" name="calculation_month" 
                               value="<?php echo htmlspecialchars($_POST['calculation_month'] ?? date('Y-m')); ?>" required
                               <?php echo $is_edit ? 'readonly' : ''; ?>>
                    </div>
                </div>
            </div>

            <div class="alert alert-info" id="shiftInfo" style="display: none;">
                <i class="fas fa-info-circle"></i> 
                <span id="shiftInfoText"></span>
                <span id="shiftEarnings" style="display: none;">0</span>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="base_salary">Lương cơ bản (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="base_salary" name="base_salary" 
                               value="<?php echo htmlspecialchars($_POST['base_salary'] ?? '0'); ?>" 
                               min="0" step="1000" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="bonus">Thưởng (VNĐ)</label>
                        <input type="number" class="form-control" id="bonus" name="bonus" 
                               value="<?php echo htmlspecialchars($_POST['bonus'] ?? '0'); ?>" 
                               min="0" step="1000">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="deductions">Khấu trừ (VNĐ)</label>
                        <input type="number" class="form-control" id="deductions" name="deductions" 
                               value="<?php echo htmlspecialchars($_POST['deductions'] ?? '0'); ?>" 
                               min="0" step="1000">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>
                                Nháp
                            </option>
                            <option value="calculated" <?php echo (isset($_POST['status']) && $_POST['status'] == 'calculated') ? 'selected' : ''; ?>>
                                Đã tính
                            </option>
                            <option value="paid" <?php echo (isset($_POST['status']) && $_POST['status'] == 'paid') ? 'selected' : ''; ?>>
                                Đã thanh toán
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"
                          placeholder="Ghi chú về tính lương..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6>Tổng lương: <span id="totalSalary" class="text-success font-weight-bold">0</span> VNĐ</h6>
                    <small class="text-muted">Lương cơ bản + Lương ca + Thưởng - Khấu trừ</small>
                </div>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> <?php echo $is_edit ? 'Cập nhật' : 'Lưu tính lương'; ?>
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Tự động điền lương cơ bản khi chọn nhân viên
document.getElementById('employee_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const baseSalary = selectedOption.getAttribute('data-salary');
    if (baseSalary) {
        document.getElementById('base_salary').value = baseSalary;
        calculateTotal();
    }
    
    // Load thông tin ca làm việc
    loadShiftInfo();
});

// Tính tổng lương
function calculateTotal() {
    const baseSalary = parseFloat(document.getElementById('base_salary').value) || 0;
    const bonus = parseFloat(document.getElementById('bonus').value) || 0;
    const deductions = parseFloat(document.getElementById('deductions').value) || 0;
    const shiftEarningsText = document.getElementById('shiftEarnings').textContent;
    const shiftEarnings = parseFloat(shiftEarningsText) || 0;
    
    const total = baseSalary + shiftEarnings + bonus - deductions;
    document.getElementById('totalSalary').textContent = total.toLocaleString('vi-VN');
}

// Load thông tin ca làm việc
function loadShiftInfo() {
    const employeeId = document.getElementById('employee_id').value;
    const month = document.getElementById('calculation_month').value;
    
    if (!employeeId || !month) {
        document.getElementById('shiftInfo').style.display = 'none';
        return;
    }
    
    fetch(`get_shift_info.php?employee_id=${employeeId}&month=${month}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('shiftInfoText').innerHTML = 
                    `Đã hoàn thành <strong>${data.total_shifts}</strong> ca, 
                     tổng <strong>${data.total_hours}</strong> giờ, 
                     lương ca: <strong>${parseFloat(data.shift_earnings).toLocaleString('vi-VN')}</strong> VNĐ`;
                document.getElementById('shiftEarnings').textContent = data.shift_earnings;
                document.getElementById('shiftInfo').style.display = 'block';
                calculateTotal();
            } else {
                document.getElementById('shiftInfo').style.display = 'none';
                document.getElementById('shiftEarnings').textContent = '0';
                calculateTotal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('shiftInfo').style.display = 'none';
            document.getElementById('shiftEarnings').textContent = '0';
            calculateTotal();
        });
}

// Tính lại khi thay đổi các trường
['base_salary', 'bonus', 'deductions'].forEach(id => {
    document.getElementById(id).addEventListener('input', calculateTotal);
});

document.getElementById('calculation_month').addEventListener('change', loadShiftInfo);

// Tính tổng ban đầu
calculateTotal();
if (document.getElementById('employee_id').value) {
    loadShiftInfo();
}
</script>

<?php include '../../includes/admin-footer.php'; ?>

