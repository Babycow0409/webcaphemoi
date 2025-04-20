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

// Xử lý thêm địa chỉ mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    if (empty($address)) {
        $error_msg = "Địa chỉ không được để trống";
    } else if (empty($city)) {
        $error_msg = "Thành phố không được để trống";
    } else {
        // Nếu đánh dấu là địa chỉ mặc định, cập nhật tất cả địa chỉ khác thành không mặc định
        if ($is_default) {
            $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        // Thêm địa chỉ mới
        $stmt = $conn->prepare("INSERT INTO addresses (user_id, address, city, is_default) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $address, $city, $is_default);
        
        if ($stmt->execute()) {
            $success_msg = "Đã thêm địa chỉ mới";
        } else {
            $error_msg = "Thêm địa chỉ thất bại";
        }
    }
}

// Xử lý cập nhật địa chỉ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $address_id = $_POST['address_id'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    if (empty($address)) {
        $error_msg = "Địa chỉ không được để trống";
    } else if (empty($city)) {
        $error_msg = "Thành phố không được để trống";
    } else {
        // Nếu đánh dấu là địa chỉ mặc định, cập nhật tất cả địa chỉ khác thành không mặc định
        if ($is_default) {
            $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        // Cập nhật địa chỉ
        $stmt = $conn->prepare("UPDATE addresses SET address = ?, city = ?, is_default = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssiii", $address, $city, $is_default, $address_id, $user_id);
        
        if ($stmt->execute()) {
            $success_msg = "Đã cập nhật địa chỉ";
        } else {
            $error_msg = "Cập nhật địa chỉ thất bại";
        }
    }
}

// Xử lý xóa địa chỉ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $address_id = $_POST['address_id'];
    
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    
    if ($stmt->execute()) {
        $success_msg = "Đã xóa địa chỉ";
    } else {
        $error_msg = "Xóa địa chỉ thất bại";
    }
}

// Lấy danh sách địa chỉ
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id ASC");
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
    <title>Sổ địa chỉ | Coffee Shop</title>
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
        .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-danger:hover {
            background-color: #c82333;
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
        }
        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            cursor: pointer;
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
                        <li><a href="profile.php">Thông tin cá nhân</a></li>
                        <li><a href="address-book.php" class="active">Sổ địa chỉ</a></li>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Sổ địa chỉ</h2>
                        <button class="btn-primary" onclick="showAddAddressModal()">
                            <i class="fas fa-plus"></i> Thêm địa chỉ mới
                        </button>
                    </div>
                    
                    <?php if (empty($addresses)): ?>
                        <p>Bạn chưa có địa chỉ nào.</p>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card">
                                <?php if ($address['is_default']): ?>
                                    <span class="default-badge">Mặc định</span>
                                <?php endif; ?>
                                
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($address['address']); ?></p>
                                <p><strong>Thành phố:</strong> <?php echo htmlspecialchars($address['city']); ?></p>
                                
                                <div class="address-actions">
                                    <button class="btn-secondary" onclick="editAddress(<?php echo $address['id']; ?>, '<?php echo addslashes($address['address']); ?>', '<?php echo addslashes($address['city']); ?>', <?php echo $address['is_default']; ?>)">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    
                                    <?php if (!$address['is_default']): ?>
                                        <form method="POST" action="" style="display: inline-block;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                            <button type="submit" class="btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal thêm địa chỉ -->
    <div id="addAddressModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideAddAddressModal()">&times;</span>
            <h2>Thêm địa chỉ mới</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="address">Địa chỉ *</label>
                    <input type="text" id="address" name="address" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="city">Thành phố *</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_default" value="1"> Đặt làm địa chỉ mặc định
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Thêm địa chỉ</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal chỉnh sửa địa chỉ -->
    <div id="editAddressModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideEditAddressModal()">&times;</span>
            <h2>Chỉnh sửa địa chỉ</h2>
            <form id="editAddressForm" method="post" action="">
                <input type="hidden" id="edit_id" name="edit_id">
                <div class="form-group">
                    <label for="edit_address">Địa chỉ:</label>
                    <input type="text" id="edit_address" name="edit_address" required>
                </div>
                <div class="form-group">
                    <label for="edit_city">Thành phố:</label>
                    <input type="text" id="edit_city" name="edit_city" required>
                </div>
                <div class="form-group checkbox">
                    <input type="checkbox" id="edit_is_default" name="edit_is_default">
                    <label for="edit_is_default">Đặt làm địa chỉ mặc định</label>
                </div>
                <button type="submit" name="edit_address_submit">Lưu địa chỉ</button>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Modal thêm địa chỉ
        function showAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'block';
        }
        
        function hideAddAddressModal() {
            document.getElementById('addAddressModal').style.display = 'none';
        }
        
        // Modal chỉnh sửa địa chỉ
        function editAddress(id, address, city, isDefault) {
            console.log("Editing address:", id, address, city, isDefault);
            document.getElementById('edit_id').value = id;
            
            // Kiểm tra xem các element có tồn tại không
            const addressElem = document.getElementById('edit_address');
            const cityElem = document.getElementById('edit_city');
            const defaultElem = document.getElementById('edit_is_default');
            
            console.log("Elements:", addressElem, cityElem, defaultElem);
            
            if (addressElem) addressElem.value = address || '';
            if (cityElem) cityElem.value = city || '';
            if (defaultElem) defaultElem.checked = isDefault === 1;
            
            document.getElementById('editAddressModal').style.display = 'block';
        }
        
        function hideEditAddressModal() {
            document.getElementById('editAddressModal').style.display = 'none';
        }
        
        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('addAddressModal')) {
                hideAddAddressModal();
            }
            if (event.target == document.getElementById('editAddressModal')) {
                hideEditAddressModal();
            }
        }
    </script>
</body>
</html> 