<?php
$page_title = "Chỉnh sửa nhân viên";
$header_icon = "user-edit";
include '../includes/admin-header.php';

$errors = [];
$success = false;

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

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $employee_code = trim($_POST['employee_code'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    $hire_date = trim($_POST['hire_date'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validate dữ liệu
    if (empty($employee_code)) {
        $errors[] = "Vui lòng nhập mã nhân viên";
    }
    
    if (empty($fullname)) {
        $errors[] = "Vui lòng nhập họ tên";
    }
    
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = "Số điện thoại không hợp lệ (10-11 số)";
    }
    
    if (!empty($salary) && !is_numeric($salary)) {
        $errors[] = "Lương phải là số";
    }
    
    // Kiểm tra mã nhân viên đã tồn tại chưa (trừ nhân viên hiện tại)
    if (empty($errors)) {
        $sql = "SELECT id FROM employees WHERE employee_code = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $employee_code, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Mã nhân viên đã tồn tại";
        }
        $stmt->close();
    }
    
    // Kiểm tra email đã tồn tại chưa (trừ nhân viên hiện tại)
    if (empty($errors)) {
        $sql = "SELECT id FROM employees WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email đã được sử dụng";
        }
        $stmt->close();
    }
    
    $role = $_POST['role'] ?? 'employee';
    
    // Nếu không có lỗi, cập nhật thông tin nhân viên
    if (empty($errors)) {
        $sql = "UPDATE employees SET employee_code = ?, fullname = ?, email = ?, phone = ?, address = ?, city = ?, 
                position = ?, department = ?, salary = ?, hire_date = ?, status = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssdsssi", $employee_code, $fullname, $email, $phone, $address, $city, 
                         $position, $department, $salary, $hire_date, $status, $role, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật thông tin nhân viên thành công!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Lỗi khi cập nhật: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Cập nhật lại thông tin employee để hiển thị
    if (!empty($errors)) {
        $employee = array_merge($employee, $_POST);
    }
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user-edit"></i> Chỉnh sửa nhân viên: <?php echo htmlspecialchars($employee['fullname']); ?>
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
                        <label for="employee_code">Mã nhân viên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="employee_code" name="employee_code" 
                               value="<?php echo htmlspecialchars($employee['employee_code']); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fullname">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname" 
                               value="<?php echo htmlspecialchars($employee['fullname']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>" 
                               pattern="[0-9]{10,11}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?php echo htmlspecialchars($employee['address'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="city">Thành phố</label>
                        <input type="text" class="form-control" id="city" name="city" 
                               value="<?php echo htmlspecialchars($employee['city'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="position">Chức vụ</label>
                        <input type="text" class="form-control" id="position" name="position" 
                               value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="department">Phòng ban</label>
                        <input type="text" class="form-control" id="department" name="department" 
                               value="<?php echo htmlspecialchars($employee['department'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="salary">Lương (VNĐ)</label>
                        <input type="number" class="form-control" id="salary" name="salary" 
                               value="<?php echo htmlspecialchars($employee['salary'] ?? ''); ?>" 
                               min="0" step="100000">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="hire_date">Ngày vào làm</label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date" 
                               value="<?php echo htmlspecialchars($employee['hire_date'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="role">Phân quyền</label>
                        <select class="form-control" id="role" name="role">
                            <option value="employee" <?php echo ($employee['role'] ?? 'employee') == 'employee' ? 'selected' : ''; ?>>
                                Nhân viên thường
                            </option>
                            <option value="manager" <?php echo ($employee['role'] ?? 'employee') == 'manager' ? 'selected' : ''; ?>>
                                Nhân viên quản lý
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>>
                                Đang làm việc
                            </option>
                            <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>>
                                Đã nghỉ việc
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật thông tin
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>

