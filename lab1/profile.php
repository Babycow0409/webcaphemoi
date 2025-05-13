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
            
            // Cập nhật recipient_name và phone trong bảng addresses
            $update_addresses = $conn->prepare("UPDATE addresses SET recipient_name = ?, phone = ? WHERE user_id = ?");
            $update_addresses->bind_param("ssi", $fullname, $phone, $user_id);
            $update_addresses->execute();
            
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
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 2");
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
        .container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(60,47,47,0.08);
    padding: 32px 24px;
    margin-top: 40px;
}

.profile-container {
    display: flex;
    gap: 32px;
    flex-wrap: wrap;
}

.profile-sidebar {
    min-width: 220px;
    background: #f8f6f2;
    border-radius: 12px;
    padding: 24px 16px;
    box-shadow: 0 2px 8px rgba(60,47,47,0.04);
}

.profile-menu h3 {
    font-size: 1.2rem;
    color: #3c2f2f;
    margin-bottom: 16px;
}

.profile-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.profile-menu li {
    margin-bottom: 12px;
}

.profile-menu a {
    color: #3c2f2f;
    text-decoration: none;
    font-weight: 500;
    display: block;
    padding: 10px 15px;
    border-radius: 5px;
    transition: background 0.2s, color 0.2s;
}

.profile-menu a.active,
.profile-menu a:hover {
    background: #d4a373;
    color: #fff;
}

.profile-content {
    flex: 1;
    min-width: 300px;
}

.profile-card {
    background: #f8f6f2;
    border-radius: 12px;
    padding: 32px 24px;
    box-shadow: 0 2px 8px rgba(60,47,47,0.04);
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 18px;
    flex: 1;
}

.form-group label {
    font-weight: 500;
}

.form-control,
.payment-form input[type="text"],
.payment-form input[type="tel"],
.payment-form input[type="email"],
.payment-form input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d4a373;
    border-radius: 6px;
    font-size: 1rem;
    background: #f8f6f2;
    margin-top: 4px;
    transition: border 0.2s;
}

.form-control:focus,
.payment-form input:focus {
    border: 1.5px solid #b6894c;
    outline: none;
}

.payment-form .form-row {
    display: flex;
    gap: 16px;
}

.payment-form label.required:after {
    content: " *";
    color: #b23c3c;
}

.btn-primary,
.payment-btn {
    background: #d4a373;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 28px;
    font-size: 1.05rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover,
.payment-btn:hover {
    background: #b6894c;
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

/* Address Book */
.address-list {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
}

.address-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(60,47,47,0.08);
    padding: 24px 20px;
    min-width: 320px;
    max-width: 350px;
    flex: 1 1 320px;
    position: relative;
    margin-bottom: 12px;
    transition: box-shadow 0.2s;
}

.address-card:hover {
    box-shadow: 0 6px 24px rgba(60,47,47,0.12);
}

.default-badge {
    position: absolute;
    top: 18px;
    right: 18px;
    background: #d4a373;
    color: #fff;
    font-size: 0.9rem;
    padding: 4px 12px;
    border-radius: 8px;
    font-weight: 600;
}

.address-info h3 {
    margin: 0 0 8px 0;
    font-size: 1.1rem;
    color: #3c2f2f;
}

.address-info p {
    margin: 4px 0;
    color: #666;
    font-size: 1rem;
}

.address-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
}

.action-btn {
    border: none;
    background: #f8f6f2;
    color: #3c2f2f;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 0.98rem;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.action-btn:hover, .action-btn:focus {
    background: #d4a373;
    color: #fff;
}

.edit-btn { }
.delete-btn { color: #b23c3c; }
.delete-btn:hover { background: #b23c3c; color: #fff; }
.default-btn { color: #3c2f2f; }
.default-btn:hover { background: #3c2f2f; color: #fff; }

.empty-addresses {
    text-align: center;
    padding: 60px 0 40px 0;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0; width: 100vw; height: 100vh;
    background: rgba(60,47,47,0.18);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: #fff;
    border-radius: 14px;
    padding: 32px 28px;
    max-width: 420px;
    width: 100%;
    margin: 60px auto;
    position: relative;
    box-shadow: 0 8px 32px rgba(60,47,47,0.18);
}

.close {
    position: absolute;
    top: 18px;
    right: 22px;
    font-size: 1.6rem;
    color: #b23c3c;
    cursor: pointer;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
}

@media (max-width: 900px) {
    .profile-container { flex-direction: column; }
    .profile-sidebar { min-width: unset; margin-bottom: 24px; }
    .address-list { flex-direction: column; }
    .address-card { max-width: 100%; }
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
                
                <div class="profile-card payment-section">
                    <h2>Thông tin cá nhân</h2>
                    <form method="POST" action="" class="payment-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fullname" class="required">Họ tên</label>
                                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="required">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="required">Số điện thoại</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="payment-btn">Cập nhật thông tin</button>
                        </div>
                    </form>
                </div>
                
                <div class="profile-card payment-section">
                    <h2>Thay đổi mật khẩu</h2>
                    <form method="POST" action="" class="payment-form">
                        <input type="hidden" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        
                        <div class="form-group">
                            <label for="current_password" class="required">Mật khẩu hiện tại</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password" class="required">Mật khẩu mới</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="required">Xác nhận mật khẩu</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="payment-btn">Đổi mật khẩu</button>
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
                                
                                <div class="address-info">
                                    <h3><?php echo htmlspecialchars($address['recipient_name']); ?></h3>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($address['phone']); ?></p>
                                    <p class="address-line"><i class="fas fa-map-marker-alt"></i>
                                        <?php
                                            echo htmlspecialchars($address['address_detail']);
                                            if (!empty($address['ward'])) echo ', ' . htmlspecialchars($address['ward']);
                                            if (!empty($address['district'])) echo ', ' . htmlspecialchars($address['district']);
                                            if (!empty($address['province'])) echo ', ' . htmlspecialchars($address['province']);
                                        ?>
                                    </p>
                                </div>
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