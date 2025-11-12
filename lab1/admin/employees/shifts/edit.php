<?php
$page_title = "Chỉnh sửa ca làm việc";
$header_icon = "clock";
include '../../includes/admin-header.php';

$errors = [];

// Lấy ID ca từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID ca làm việc không hợp lệ";
    header("Location: index.php");
    exit();
}

// Lấy thông tin ca
$sql = "SELECT * FROM work_shifts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Không tìm thấy ca làm việc";
    header("Location: index.php");
    exit();
}

$shift = $result->fetch_assoc();
$stmt->close();

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
    
    // Kiểm tra tên ca đã tồn tại chưa (trừ ca hiện tại)
    if (empty($errors)) {
        $sql = "SELECT id FROM work_shifts WHERE shift_name = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $shift_name, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Tên ca làm việc đã tồn tại";
        }
        $stmt->close();
    }
    
    // Nếu không có lỗi, cập nhật
    if (empty($errors)) {
        $sql = "UPDATE work_shifts SET shift_name = ?, start_time = ?, end_time = ?, hourly_rate = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssi", $shift_name, $start_time, $end_time, $hourly_rate, $description, $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật ca làm việc thành công!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Lỗi khi cập nhật: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Cập nhật lại thông tin shift để hiển thị
    if (!empty($errors)) {
        $shift = array_merge($shift, $_POST);
    }
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-clock"></i> Chỉnh sửa ca làm việc: <?php echo htmlspecialchars($shift['shift_name']); ?>
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
                               value="<?php echo htmlspecialchars($shift['shift_name']); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hourly_rate">Lương/giờ (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" 
                               value="<?php echo htmlspecialchars($shift['hourly_rate']); ?>" 
                               min="0" step="1000" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_time">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time" 
                               value="<?php echo date('H:i', strtotime($shift['start_time'])); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_time">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="end_time" name="end_time" 
                               value="<?php echo date('H:i', strtotime($shift['end_time'])); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?php echo $shift['status'] == 'active' ? 'selected' : ''; ?>>
                                Đang hoạt động
                            </option>
                            <option value="inactive" <?php echo $shift['status'] == 'inactive' ? 'selected' : ''; ?>>
                                Ngừng hoạt động
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($shift['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật thông tin
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>




