<?php
$page_title = "Thêm ca làm việc mới";
$header_icon = "clock";
include '../../includes/admin-header.php';

$errors = [];

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shift_name = trim($_POST['shift_name'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $hourly_rate = trim($_POST['hourly_rate'] ?? '0');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validate
    if (empty($shift_name)) {
        $errors[] = "Vui lòng nhập tên ca làm việc";
    }
    
    if (empty($start_time)) {
        $errors[] = "Vui lòng chọn giờ bắt đầu";
    }
    
    if (empty($end_time)) {
        $errors[] = "Vui lòng chọn giờ kết thúc";
    }
    
    if (!empty($start_time) && !empty($end_time) && $start_time == $end_time) {
        $errors[] = "Giờ bắt đầu và giờ kết thúc không được giống nhau";
    }
    
    if (!is_numeric($hourly_rate) || $hourly_rate < 0) {
        $errors[] = "Lương/giờ phải là số dương";
    }
    
    // Kiểm tra tên ca đã tồn tại chưa
    if (empty($errors)) {
        $sql = "SELECT id FROM work_shifts WHERE shift_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $shift_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Tên ca làm việc đã tồn tại";
        }
        $stmt->close();
    }
    
    // Nếu không có lỗi, thêm ca mới
    if (empty($errors)) {
        $sql = "INSERT INTO work_shifts (shift_name, start_time, end_time, hourly_rate, description, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdss", $shift_name, $start_time, $end_time, $hourly_rate, $description, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm ca làm việc thành công!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Lỗi khi thêm ca làm việc: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-clock"></i> Thêm ca làm việc mới
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
                        <label for="shift_name">Tên ca làm việc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="shift_name" name="shift_name" 
                               value="<?php echo htmlspecialchars($_POST['shift_name'] ?? ''); ?>" required
                               placeholder="Ví dụ: Ca sáng, Ca chiều, Ca tối...">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hourly_rate">Lương/giờ (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" 
                               value="<?php echo htmlspecialchars($_POST['hourly_rate'] ?? '50000'); ?>" 
                               min="0" step="1000" required>
                        <small class="form-text text-muted">Lương tính theo giờ cho ca này</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_time">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time" 
                               value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_time">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="end_time" name="end_time" 
                               value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
                        <small class="form-text text-muted">Nếu ca làm việc qua ngày (ví dụ: 22:00 - 06:00), chọn giờ kết thúc là 06:00</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : 'selected'; ?>>
                                Đang hoạt động
                            </option>
                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>
                                Ngừng hoạt động
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                          placeholder="Mô tả về ca làm việc..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu thông tin
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>




