<?php
$page_title = "Chỉnh sửa phân ca";
$header_icon = "calendar-edit";
include '../../includes/admin-header.php';

$errors = [];

// Lấy ID phân ca từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID phân ca không hợp lệ";
    header("Location: index.php");
    exit();
}

// Lấy thông tin phân ca
$sql = "SELECT sa.*, e.fullname, e.employee_code, ws.shift_name 
        FROM shift_assignments sa
        JOIN employees e ON sa.employee_id = e.id
        JOIN work_shifts ws ON sa.shift_id = ws.id
        WHERE sa.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Không tìm thấy phân ca";
    header("Location: index.php");
    exit();
}

$assignment = $result->fetch_assoc();
$stmt->close();

// Lấy danh sách ca làm việc
$shifts_sql = "SELECT id, shift_name, start_time, end_time FROM work_shifts WHERE status = 'active' ORDER BY start_time";
$shifts_result = $conn->query($shifts_sql);

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shift_id = isset($_POST['shift_id']) ? (int)$_POST['shift_id'] : 0;
    $work_date = trim($_POST['work_date'] ?? '');
    $check_in_time = !empty($_POST['check_in_time']) ? trim($_POST['check_in_time']) : null;
    $check_out_time = !empty($_POST['check_out_time']) ? trim($_POST['check_out_time']) : null;
    $hours_worked = !empty($_POST['hours_worked']) ? (float)$_POST['hours_worked'] : 0;
    $status = $_POST['status'] ?? 'scheduled';
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    if ($shift_id <= 0) {
        $errors[] = "Vui lòng chọn ca làm việc";
    }
    
    if (empty($work_date)) {
        $errors[] = "Vui lòng chọn ngày làm việc";
    }
    
    // Tính số giờ làm việc nếu có check-in và check-out
    if (!empty($check_in_time) && !empty($check_out_time)) {
        $check_in = new DateTime($check_in_time);
        $check_out = new DateTime($check_out_time);
        if ($check_out > $check_in) {
            $diff = $check_in->diff($check_out);
            $hours_worked = $diff->h + ($diff->i / 60) + ($diff->s / 3600);
        }
    }
    
    // Nếu không có lỗi, cập nhật
    if (empty($errors)) {
        $sql = "UPDATE shift_assignments SET shift_id = ?, work_date = ?, check_in_time = ?, 
                check_out_time = ?, hours_worked = ?, status = ?, notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdssi", $shift_id, $work_date, $check_in_time, $check_out_time, 
                         $hours_worked, $status, $notes, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật phân ca thành công!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Lỗi khi cập nhật: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Cập nhật lại thông tin assignment để hiển thị
    if (!empty($errors)) {
        $assignment = array_merge($assignment, $_POST);
    }
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calendar-edit"></i> Chỉnh sửa phân ca
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

        <div class="alert alert-info mb-3">
            <strong>Nhân viên:</strong> <?php echo htmlspecialchars($assignment['fullname']); ?> 
            (<?php echo htmlspecialchars($assignment['employee_code']); ?>)
        </div>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shift_id">Ca làm việc <span class="text-danger">*</span></label>
                        <select class="form-control" id="shift_id" name="shift_id" required>
                            <option value="">-- Chọn ca làm việc --</option>
                            <?php 
                            $shifts_result->data_seek(0);
                            while($shift = $shifts_result->fetch_assoc()): ?>
                                <option value="<?php echo $shift['id']; ?>"
                                        <?php echo ($assignment['shift_id'] == $shift['id'] || (isset($_POST['shift_id']) && $_POST['shift_id'] == $shift['id'])) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($shift['shift_name'] . ' (' . date('H:i', strtotime($shift['start_time'])) . ' - ' . date('H:i', strtotime($shift['end_time'])) . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="work_date">Ngày làm việc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="work_date" name="work_date" 
                               value="<?php echo htmlspecialchars($assignment['work_date']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="check_in_time">Giờ check-in</label>
                        <input type="datetime-local" class="form-control" id="check_in_time" name="check_in_time" 
                               value="<?php echo $assignment['check_in_time'] ? date('Y-m-d\TH:i', strtotime($assignment['check_in_time'])) : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="check_out_time">Giờ check-out</label>
                        <input type="datetime-local" class="form-control" id="check_out_time" name="check_out_time" 
                               value="<?php echo $assignment['check_out_time'] ? date('Y-m-d\TH:i', strtotime($assignment['check_out_time'])) : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="hours_worked">Số giờ làm việc</label>
                        <input type="number" class="form-control" id="hours_worked" name="hours_worked" 
                               value="<?php echo htmlspecialchars($assignment['hours_worked']); ?>" 
                               step="0.1" min="0">
                        <small class="form-text text-muted">Sẽ tự động tính nếu có check-in và check-out</small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="scheduled" <?php echo $assignment['status'] == 'scheduled' ? 'selected' : ''; ?>>
                                Đã lên lịch
                            </option>
                            <option value="completed" <?php echo $assignment['status'] == 'completed' ? 'selected' : ''; ?>>
                                Đã hoàn thành
                            </option>
                            <option value="absent" <?php echo $assignment['status'] == 'absent' ? 'selected' : ''; ?>>
                                Vắng mặt
                            </option>
                            <option value="cancelled" <?php echo $assignment['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                Đã hủy
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($assignment['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật phân ca
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Tự động tính số giờ khi có check-in và check-out
document.getElementById('check_in_time').addEventListener('change', calculateHours);
document.getElementById('check_out_time').addEventListener('change', calculateHours);

function calculateHours() {
    const checkIn = document.getElementById('check_in_time').value;
    const checkOut = document.getElementById('check_out_time').value;
    
    if (checkIn && checkOut) {
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        
        if (end > start) {
            const diff = (end - start) / (1000 * 60 * 60); // Convert to hours
            document.getElementById('hours_worked').value = diff.toFixed(1);
        }
    }
}
</script>

<?php include '../../includes/admin-footer.php'; ?>




