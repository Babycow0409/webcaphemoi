<?php
// Đảm bảo đã tải config.php
require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Lấy thông tin người dùng
$user_sql = "SELECT fullname, phone FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_fullname = $user_data['fullname'] ?? '';
$user_phone = $user_data['phone'] ?? '';

// Kiểm tra bảng addresses đã tồn tại chưa
$table_exists = $conn->query("SHOW TABLES LIKE 'addresses'");
if ($table_exists->num_rows == 0) {
    // Tạo bảng addresses nếu chưa tồn tại
    $sql = "CREATE TABLE IF NOT EXISTS `addresses` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `recipient_name` varchar(100) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `address_detail` varchar(255) NOT NULL,
        `ward` varchar(100) NOT NULL,
        `district` varchar(100) NOT NULL,
        `province` varchar(100) NOT NULL,
        `is_default` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        $success_msg = "Đã khởi tạo sổ địa chỉ của bạn.";
    } else {
        $error_msg = "Không thể tạo bảng addresses: " . $conn->error;
    }
}

// Xử lý thêm địa chỉ mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $address_detail = trim($_POST['address_detail']);
    $ward = trim($_POST['ward']);
    $district = trim($_POST['district']);
    $province = trim($_POST['province']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    if (empty($address_detail) || empty($ward) || empty($district) || empty($province)) {
        $error_msg = 'Vui lòng điền đầy đủ thông tin địa chỉ!';
    } else {
        if ($is_default) {
            $update_sql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('i', $user_id);
            $update_stmt->execute();
        }
        $sql = "INSERT INTO addresses (user_id, recipient_name, phone, address_detail, ward, district, province, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('issssssi', $user_id, $user_fullname, $user_phone, $address_detail, $ward, $district, $province, $is_default);
        
        if ($stmt->execute()) {
            set_message('Thêm địa chỉ mới thành công!', 'success');
            redirect('address-book.php');
        } else {
            $error_msg = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}

// Xử lý xóa địa chỉ
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM addresses WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('ii', $delete_id, $user_id);
    
    if ($delete_stmt->execute()) {
        set_message('Xóa địa chỉ thành công!', 'success');
        redirect('address-book.php');
    } else {
        set_message('Có lỗi xảy ra khi xóa địa chỉ!', 'danger');
        redirect('address-book.php');
    }
}

// Xử lý đặt địa chỉ mặc định
if (isset($_GET['default_id'])) {
    $default_id = $_GET['default_id'];
    
    // Đặt tất cả địa chỉ thành không mặc định
    $reset_sql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
    $reset_stmt = $conn->prepare($reset_sql);
    $reset_stmt->bind_param('i', $user_id);
    $reset_stmt->execute();
    
    // Đặt địa chỉ được chọn thành mặc định
    $default_sql = "UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?";
    $default_stmt = $conn->prepare($default_sql);
    $default_stmt->bind_param('ii', $default_id, $user_id);
    
    if ($default_stmt->execute()) {
        set_message('Địa chỉ mặc định đã được cập nhật!', 'success');
        redirect('address-book.php');
    } else {
        set_message('Có lỗi xảy ra khi cập nhật địa chỉ mặc định!', 'danger');
        redirect('address-book.php');
    }
}

// Xử lý chỉnh sửa địa chỉ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_address_submit'])) {
    $address_id = $_POST['edit_id'];
    $address_detail = trim($_POST['edit_address']);
    $ward = trim($_POST['edit_ward']);
    $district = trim($_POST['edit_district']);
    $province = trim($_POST['edit_city']);
    $is_default = isset($_POST['edit_is_default']) ? 1 : 0;
    
    // Validate dữ liệu đầu vào
    if (empty($address_detail) || empty($province)) {
        $error_msg = 'Vui lòng điền đầy đủ thông tin địa chỉ!';
    } else {
        // Nếu đây là địa chỉ mặc định, cập nhật tất cả các địa chỉ khác thành không mặc định
        if ($is_default) {
            $update_sql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('i', $user_id);
            $update_stmt->execute();
        }
        
        // Cập nhật địa chỉ
        $sql = "UPDATE addresses SET address_detail = ?, province = ?, district = ?, ward = ?, recipient_name = ?, phone = ?, is_default = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssiiii', $address_detail, $province, $district, $ward, $user_fullname, $user_phone, $is_default, $address_id, $user_id);
        
        if ($stmt->execute()) {
            set_message('Cập nhật địa chỉ thành công!', 'success');
            redirect('address-book.php');
        } else {
            $error_msg = 'Có lỗi xảy ra, vui lòng thử lại!';
        }
    }
}

// Lấy danh sách địa chỉ
$sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}

// Include header.php trước khi xuất bất kỳ HTML nào
include 'includes/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 40px auto;">
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
            <?php 
            // Hiển thị thông báo
            display_message();
            
            // Hiển thị thông báo lỗi nếu có
            if (!empty($error_msg)): 
            ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <div class="profile-card payment-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Sổ địa chỉ</h2>
                    <button class="payment-btn" onclick="showAddAddressModal()" style="width: auto; padding: 15px 30px; margin: 0;">
                        <i class="fas fa-plus"></i> Thêm địa chỉ mới
                    </button>
                </div>
                
                <?php if (empty($addresses)): ?>
                    <div class="empty-addresses">
                        <i class="fas fa-map-marker-alt" style="font-size: 80px; color: #d4a373; margin-bottom: 30px;"></i>
                        <h3 style="font-size: 1.8rem; color: #3c2f2f; margin-bottom: 15px;">Chưa có địa chỉ nào</h3>
                        <p style="font-size: 1.2rem; color: #666; margin-bottom: 30px;">Thêm địa chỉ để dễ dàng thanh toán trong các đơn hàng tiếp theo</p>
                        <button onclick="showAddAddressModal()" class="payment-btn" style="max-width: 300px; margin: 0 auto;">
                            <i class="fas fa-plus"></i> Thêm địa chỉ mới
                        </button>
                    </div>
                <?php else: ?>
                    <div class="address-list">
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
                                
                                <div class="address-actions">
                                    <button class="action-btn edit-btn" onclick="editAddress(<?php echo $address['id']; ?>, '<?php echo addslashes($address['address_detail']); ?>', '<?php echo addslashes($address['province']); ?>', <?php echo $address['is_default']; ?>)">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    
                                    <?php if (!$address['is_default']): ?>
                                        <a href="address-book.php?delete_id=<?php echo $address['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!$address['is_default']): ?>
                                        <a href="address-book.php?default_id=<?php echo $address['id']; ?>" class="action-btn default-btn">
                                            <i class="fas fa-check"></i> Đặt mặc định
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
        <div class="recipient-info-note" style="background-color: #f0f8ff; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #5d4037;">
            <p style="margin: 0; color: #333;"><i class="fas fa-info-circle" style="color: #5d4037; margin-right: 8px;"></i> Địa chỉ sẽ sử dụng thông tin cá nhân của bạn</p>
        </div>
        <form method="POST" action="" class="payment-form">
            <div class="form-group">
                <label for="address_detail" class="required">Địa chỉ chi tiết</label>
                <input type="text" id="address_detail" name="address_detail" required placeholder="Số nhà, tên đường...">
            </div>
            <div class="form-group">
                <label for="ward" class="required">Phường/Xã</label>
                <input type="text" id="ward" name="ward" required>
            </div>
            <div class="form-group">
                <label for="district" class="required">Quận/Huyện</label>
                <input type="text" id="district" name="district" required>
            </div>
            <div class="form-group">
                <label for="province" class="required">Tỉnh/Thành phố</label>
                <input type="text" id="province" name="province" required>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="is_default" name="is_default" value="1">
                    <span>Đặt làm địa chỉ mặc định</span>
                </label>
            </div>
            <div class="form-group">
                <button type="submit" name="add_address" class="payment-btn">Thêm địa chỉ</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal chỉnh sửa địa chỉ -->
<div id="editAddressModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideEditAddressModal()">&times;</span>
        <h2>Chỉnh sửa địa chỉ</h2>
        <div class="recipient-info-note" style="background-color: #f0f8ff; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #5d4037;">
            <p style="margin: 0; color: #333;"><i class="fas fa-info-circle" style="color: #5d4037; margin-right: 8px;"></i> Địa chỉ sẽ sử dụng thông tin cá nhân của bạn</p>
        </div>
        <form id="editAddressForm" method="post" action="" class="payment-form">
            <input type="hidden" id="edit_id" name="edit_id">
            
            <div class="form-group">
                <label for="edit_address" class="required">Địa chỉ chi tiết</label>
                <input type="text" id="edit_address" name="edit_address" required>
            </div>
            
            <div class="form-group">
                <label for="edit_ward" class="required">Phường/Xã</label>
                <input type="text" id="edit_ward" name="edit_ward" required>
            </div>
            
            <div class="form-group">
                <label for="edit_district" class="required">Quận/Huyện</label>
                <input type="text" id="edit_district" name="edit_district" required>
            </div>
            
            <div class="form-group">
                <label for="edit_city" class="required">Tỉnh/Thành phố</label>
                <input type="text" id="edit_city" name="edit_city" required>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="edit_is_default" name="edit_is_default" value="1">
                    <span>Đặt làm địa chỉ mặc định</span>
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" name="edit_address_submit" class="payment-btn">Lưu địa chỉ</button>
            </div>
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
        document.getElementById('edit_id').value = id;
        
        // Tìm địa chỉ chi tiết trong DB bằng Ajax
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_address.php?id=' + id, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var addressData = JSON.parse(xhr.responseText);
                    document.getElementById('edit_address').value = addressData.address_detail || '';
                    document.getElementById('edit_ward').value = addressData.ward || '';
                    document.getElementById('edit_district').value = addressData.district || '';
                    document.getElementById('edit_city').value = addressData.province || '';
                    document.getElementById('edit_is_default').checked = addressData.is_default == 1;
                } catch (e) {
                    console.error('Error parsing JSON response', e);
                    
                    // Fallback to simple form
                    document.getElementById('edit_address').value = address || '';
                    document.getElementById('edit_ward').value = city || '';
                    document.getElementById('edit_district').value = city || '';
                    document.getElementById('edit_city').value = city || '';
                    document.getElementById('edit_is_default').checked = isDefault === 1;
                }
            }
        };
        xhr.send();
        
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

<style>
    /* Đặt trong <style> hoặc file css riêng */
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
        transition: color 0.2s;
    }

    .profile-menu a.active,
    .profile-menu a:hover {
        color: #d4a373;
    }

    .profile-content {
        flex: 1;
    }

    .profile-card {
        background: #f8f6f2;
        border-radius: 12px;
        padding: 32px 24px;
        box-shadow: 0 2px 8px rgba(60,47,47,0.04);
    }

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

    .payment-btn:hover {
        background: #b6894c;
    }

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

    .payment-form .form-row {
        display: flex;
        gap: 16px;
    }

    .payment-form .form-group {
        margin-bottom: 18px;
        flex: 1;
    }

    .payment-form label.required:after {
        content: " *";
        color: #b23c3c;
    }

    .payment-form input[type="text"],
    .payment-form input[type="tel"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d4a373;
        border-radius: 6px;
        font-size: 1rem;
        background: #f8f6f2;
        margin-top: 4px;
        transition: border 0.2s;
    }

    .payment-form input[type="text"]:focus,
    .payment-form input[type="tel"]:focus {
        border: 1.5px solid #b6894c;
        outline: none;
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