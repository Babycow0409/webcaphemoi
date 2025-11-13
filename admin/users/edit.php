<?php
$page_title = "Chỉnh sửa người dùng";

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

// Khởi tạo biến
$id = $fullname = $email = $phone = $address = $city = $role = "";
$errors = [];
$success = false;

// Lấy thông tin người dùng cần chỉnh sửa
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    // Truy vấn thông tin người dùng
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $fullname = $user['fullname'];
        $email = $user['email'];
        $phone = $user['phone'];
        $address = $user['address'];
        $city = $user['city'];
        $role = $user['role'];
    } else {
        $errors[] = "Không tìm thấy người dùng với ID: $id";
    }
    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

// Xử lý form khi người dùng submit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    // Validate dữ liệu
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $role = $_POST['role'];
    
    // Kiểm tra các trường bắt buộc
    if (empty($fullname)) {
        $errors[] = "Họ tên không được để trống";
    }
    
    if (empty($email)) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    // Kiểm tra email đã tồn tại chưa (trừ email hiện tại của người dùng)
    $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email đã được sử dụng bởi người dùng khác";
    }
    $stmt->close();
    
    // Nếu không có lỗi, cập nhật thông tin người dùng
    if (empty($errors)) {
        $sql = "UPDATE users SET fullname = ?, email = ?, phone = ?, address = ?, city = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $fullname, $email, $phone, $address, $city, $role, $id);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Lỗi cập nhật: " . $conn->error;
        }
        $stmt->close();
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
                    <div class="card">
                        <div class="card-body">
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    Cập nhật thông tin người dùng thành công!
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="fullname">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Địa chỉ</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="city">Thành phố</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="role">Vai trò</label>
                                    <select class="form-control" id="role" name="role">
                                        <option value="customer" <?php echo ($role == 'customer') ? 'selected' : ''; ?>>Khách hàng</option>
                                        <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mt-4">
                                    <button type="submit" name="update_user" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Lưu thay đổi
                                    </button>
                                    <a href="index.php" class="btn btn-secondary ml-2">
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
// Include footer
require_once __DIR__ . '/../includes/admin-footer.php';
?> 