<?php
$page_title = "Thêm người dùng mới";

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$errors = [];
$success_message = '';

// Xử lý form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form và làm sạch
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $fullname = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $active = isset($_POST['active']) ? 1 : 0;

    // Kiểm tra dữ liệu
    if (empty($username)) {
        $errors[] = "Tên đăng nhập không được để trống";
    } elseif (strlen($username) < 3) {
        $errors[] = "Tên đăng nhập phải có ít nhất 3 ký tự";
    }

    if (empty($email)) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    if (empty($fullname)) {
        $errors[] = "Họ tên không được để trống";
    }

    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        $errors[] = "Mật khẩu phải chứa ít nhất một chữ hoa, một chữ thường và một số";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Xác nhận mật khẩu không khớp";
    }

    if (!in_array($role, ['admin', 'customer'])) {
        $errors[] = "Vai trò không hợp lệ";
    }

    // Kiểm tra username và email tồn tại chưa
    $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['username'] === $username) {
            $errors[] = "Tên đăng nhập đã tồn tại";
        }
        if ($row['email'] === $email) {
            $errors[] = "Email đã tồn tại";
        }
    }
    $stmt->close();

    // Nếu không có lỗi, thêm người dùng mới
    if (empty($errors)) {
        try {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Chuẩn bị SQL để thêm người dùng
            $sql = "INSERT INTO users (username, password, email, fullname, phone, address, city, role, active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $username, $hashed_password, $email, $fullname, $phone, $address, $city, $role, $active);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Thêm người dùng thành công!";
                header("Location: index.php");
                exit();
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $errors[] = "Lỗi khi thêm người dùng: " . $e->getMessage();
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}

// Include header
require_once __DIR__ . '/../includes/admin-header.php';
?>

<style>
    .content {
        background-color: white;
        color: #000;
    }
    
    .content-wrapper {
        background-color: white;
        color: #000;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
    }
    
    .card {
        margin-bottom: 20px;
        color: #000;
    }
</style>

<div class="content-wrapper">
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?php echo $success_message; ?>
                            </div>
                            <?php endif; ?>

                            <div class="form-container">
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <div class="form-group">
                                        <label for="username">Tên đăng nhập <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="fullname">Họ tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="fullname" name="fullname"
                                            value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Số điện thoại</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="address">Địa chỉ</label>
                                        <textarea class="form-control" id="address" name="address"
                                            rows="2"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="city">Thành phố</label>
                                        <input type="text" class="form-control" id="city" name="city"
                                            value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            required>
                                        <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Xác nhận mật khẩu <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password"
                                            name="confirm_password" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="role">Vai trò <span class="text-danger">*</span></label>
                                        <select class="form-control" id="role" name="role" required>
                                            <option value="customer"
                                                <?php echo (isset($role) && $role === 'customer') ? 'selected' : ''; ?>>
                                                Khách hàng</option>
                                            <option value="admin"
                                                <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>
                                                Quản trị viên</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="active"
                                                name="active"
                                                <?php echo (!isset($active) || $active === 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="active">Kích hoạt tài khoản</label>
                                        </div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-user-plus mr-1"></i> Thêm người dùng
                                        </button>
                                        <a href="index.php" class="btn btn-outline-secondary ml-2">
                                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<?php
// Đóng kết nối
$conn->close();

// Include footer
require_once __DIR__ . '/../includes/admin-footer.php';
?>