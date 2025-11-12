<?php
$page_title = "Thêm nhân viên mới";
$header_icon = "user-plus";
include '../includes/admin-header.php';

// Kiểm tra bảng employees có tồn tại không
$table_exists = $conn->query("SHOW TABLES LIKE 'employees'")->num_rows > 0;
if (!$table_exists) {
    echo '<div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> 
        Bảng employees chưa được tạo. Vui lòng chạy <a href="setup_tables.php">setup_tables.php</a> để tạo bảng.
    </div>';
    include '../includes/admin-footer.php';
    exit();
}

// Kiểm tra cột employee_code có tồn tại không
$column_exists = $conn->query("SHOW COLUMNS FROM employees LIKE 'employee_code'")->num_rows > 0;
if (!$column_exists) {
    // Thử thêm cột
    $conn->query("ALTER TABLE employees ADD COLUMN employee_code VARCHAR(20) UNIQUE NOT NULL AFTER id");
}

$errors = [];
$success = false;

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $employee_code = trim($_POST['employee_code'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
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
    
    if (empty($full_name)) {
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
    
    if (!empty($base_salary) && !is_numeric($base_salary)) {
        $errors[] = "Lương phải là số";
    }
    
    // Kiểm tra mã nhân viên đã tồn tại chưa
    if (empty($errors)) {
        $sql = "SELECT id FROM employees WHERE employee_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $employee_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Mã nhân viên đã tồn tại";
        }
        $stmt->close();
    }
    
    // Kiểm tra email đã tồn tại chưa
    if (empty($errors)) {
        $sql = "SELECT id FROM employees WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email đã được sử dụng";
        }
        $stmt->close();
    }
    
    $role = $_POST['role'] ?? 'employees';
    
    // Nếu không có lỗi, thêm nhân viên mới
    if (empty($errors)) {
        $sql = "INSERT INTO employees (employee_code, full_name, email, phone, address, city, position, department, base_salary, hire_date, status, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssdsss",$employee_code, $full_name, $email, $phone, $address, $city, $position, $department, $base_salary, $hire_date, $status, $role);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thêm nhân viên thành công!";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Lỗi khi thêm nhân viên: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!-- Main content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user-plus"></i> Thêm nhân viên mới
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
                            value="<?php echo htmlspecialchars($_POST['employee_code'] ?? ''); ?>" required>
                        <small class="form-text text-muted">Ví dụ: NV001, NV002...</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fullname">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname"
                            value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" pattern="[0-9]{10,11}"
                            placeholder="0901234567">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="city">Thành phố</label>
                        <input type="text" class="form-control" id="city" name="city"
                            value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="position">Chức vụ</label>
                        <input type="text" class="form-control" id="position" name="position"
                            value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>"
                            placeholder="Nhân viên pha chế, Thu ngân, Quản lý...">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="department">Phòng ban</label>
                        <input type="text" class="form-control" id="department" name="department"
                            value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>"
                            placeholder="Sản xuất, Bán hàng, Quản lý...">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="salary">Lương (VNĐ)</label>
                        <input type="number" class="form-control" id="salary" name="salary"
                            value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>" min="0" step="100000"
                            placeholder="8000000">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="hire_date">Ngày vào làm</label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date"
                            value="<?php echo htmlspecialchars($_POST['hire_date'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="role">Phân quyền</label>
                        <select class="form-control" id="role" name="role">
                            <option value="employee"
                                <?php echo (isset($_POST['role']) && $_POST['role'] == 'employee') ? 'selected' : 'selected'; ?>>
                                Nhân viên thường
                            </option>
                            <option value="manager"
                                <?php echo (isset($_POST['role']) && $_POST['role'] == 'manager') ? 'selected' : ''; ?>>
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
                            <option value="active"
                                <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>
                                Đang làm việc
                            </option>
                            <option value="inactive"
                                <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>
                                Đã nghỉ việc
                            </option>
                        </select>
                    </div>
                </div>
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

<?php include '../includes/admin-footer.php'; ?>