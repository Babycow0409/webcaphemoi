<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Kiểm tra các trường mật khẩu trước khi sử dụng
    $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // Cập nhật thông tin cơ bản
    if (!empty($fullname) && !empty($email) && !empty($phone)) {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullname, $email, $phone, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['fullname'] = $fullname;
            $success_msg = "Thông tin cá nhân đã được cập nhật";
            
            // Cập nhật lại thông tin người dùng
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error_msg = "Cập nhật thông tin thất bại";
        }
    }
    
    // Đổi mật khẩu chỉ khi có đủ 3 trường
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $error_msg = "Mật khẩu mới và xác nhận mật khẩu không khớp";
        } else if (!password_verify($current_password, $user['password'])) {
            $error_msg = "Mật khẩu hiện tại không đúng";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success_msg = "Mật khẩu đã được cập nhật";
            } else {
                $error_msg = "Cập nhật mật khẩu thất bại";
            }
        }
    }
}

// Lấy địa chỉ của người dùng
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản | Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 30px 0;
        }
        .profile-sidebar {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
        }
        .profile-content {
            flex: 3;
            min-width: 300px;
        }
        .profile-menu {
            background-color: #f5f5f5;
            border-radius: 10px;
            padding: 20px;
        }
        .profile-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .profile-menu li {
            margin-bottom: 10px;
        }
        .profile-menu a {
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        .profile-menu a:hover {
            background-color: #ddd;
        }
        .profile-menu a.active {
            background-color: #6f4e37;
            color: white;
        }
        .profile-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #6f4e37;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #5d4229;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .address-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .address-card .default-badge {
            position: absolute;
            top: -10px;
            right: 10px;
            background-color: #6f4e37;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .address-card .address-actions {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-menu">
                    <h3>Tài khoản của tôi</h3>
                    <ul>
                        <li><a href="profile.php" class="active">Thông tin cá nhân</a></li>
                        <li><a href="address-book.php">Sổ địa chỉ</a></li>
                        <li><a href="my-orders.php">Đơn hàng của tôi</a></li>
                        <li><a href="logout.php">Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="profile-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>
                
                <div class="profile-card">
                    <h2>Thông tin cá nhân</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Tên đăng nhập</label>
                            <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="fullname">Họ tên</label>
                            <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Số điện thoại</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Cập nhật thông tin</button>
                        </div>
                    </form>
                </div>
                
                <div class="profile-card">
                    <h2>Đổi mật khẩu</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password">Mật khẩu hiện tại</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Đổi mật khẩu</button>
                        </div>
                    </form>
                </div>
                
                <div class="profile-card">
                    <h2>Địa chỉ của tôi</h2>
                    
                    <?php if (empty($addresses)): ?>
                        <p>Bạn chưa có địa chỉ nào. <a href="address-book.php">Thêm địa chỉ mới</a></p>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card">
                                <?php if ($address['is_default']): ?>
                                    <span class="default-badge">Mặc định</span>
                                <?php endif; ?>
                                
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($address['address']); ?></p>
                                <p><strong>Thành phố:</strong> <?php echo htmlspecialchars($address['city']); ?></p>
                            </div>
                        <?php endforeach; ?>
                        
                        <a href="address-book.php" class="btn-primary">Quản lý địa chỉ</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 