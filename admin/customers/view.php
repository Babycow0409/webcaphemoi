<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../classes/Customer.php';
require_once __DIR__ . '/../../classes/LoyaltyPoint.php';

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$successMessage = $_SESSION['customer_level_success'] ?? '';
$errorMessage = $_SESSION['customer_level_error'] ?? '';
$reload = false;
$levelOptions = [
    'bronze' => 'Đồng',
    'silver' => 'Bạc',
    'gold' => 'Vàng',
    'platinum' => 'Bạch Kim'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCustomerId = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    $newLevel = $_POST['customer_level'] ?? '';

    if (!$postedCustomerId || $postedCustomerId !== $customerId) {
        $_SESSION['customer_level_error'] = 'Yêu cầu không hợp lệ.';
    } elseif (!array_key_exists($newLevel, $levelOptions)) {
        $_SESSION['customer_level_error'] = 'Cấp độ khách hàng không hợp lệ.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET customer_level = ? WHERE id = ?");
        if ($stmt === false) {
            $_SESSION['customer_level_error'] = 'Không thể chuẩn bị câu lệnh cập nhật.';
        } else {
            $stmt->bind_param("si", $newLevel, $postedCustomerId);
            if ($stmt->execute()) {
                $_SESSION['customer_level_success'] = 'Cập nhật cấp độ khách hàng thành công.';
                $reload = true;
            } else {
                $_SESSION['customer_level_error'] = 'Không thể cập nhật cấp độ khách hàng. Vui lòng thử lại.';
            }
            $stmt->close();
        }
    }

    if ($reload) {
        header("Location: view.php?id=" . $customerId);
        exit();
    }
}

if (!$customerId) {
    header("Location: index.php");
    exit();
}

$customer = new Customer($conn, $customerId);
$loyaltyPoint = new LoyaltyPoint($conn);

if (!$customer->getId()) {
    header("Location: index.php");
    exit();
}

// Lấy thông tin chi tiết
$orderHistory = $customer->getOrderHistory(10);
$pointHistory = $loyaltyPoint->getPointHistory($customerId, 10);

if (!empty($successMessage)) {
    unset($_SESSION['customer_level_success']);
}

if (!empty($errorMessage)) {
    unset($_SESSION['customer_level_error']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết khách hàng - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h4><i class="fas fa-user mr-2"></i>Thông tin khách hàng</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($successMessage); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($customer->getFullname()); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer->getEmail()); ?></p>
                                <p><strong>SĐT:</strong> <?php echo htmlspecialchars($customer->getPhone()); ?></p>
                                <p><strong>Cấp độ:</strong> 
                                    <span class="badge badge-primary"><?php echo $customer->getCustomerLevelName(); ?></span>
                                </p>
                                <form method="POST" class="form-inline mt-3">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer->getId(); ?>">
                                    <label for="customer_level" class="font-weight-bold mr-2 mb-2">Chỉnh cấp độ:</label>
                                    <select name="customer_level" id="customer_level" class="form-control mb-2 mr-2">
                                        <?php foreach ($levelOptions as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" <?php echo $customer->getCustomerLevel() === $value ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary mb-2">
                                        <i class="fas fa-save mr-1"></i>Lưu
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tổng chi tiêu:</strong> 
                                    <span class="text-success font-weight-bold">
                                        <?php echo number_format($customer->getTotalSpent(), 0, ',', '.'); ?>đ
                                    </span>
                                </p>
                                <p><strong>Số đơn hàng:</strong> <?php echo $customer->getTotalOrders(); ?></p>
                                <p><strong>Điểm tích lũy:</strong> 
                                    <span class="badge badge-warning">
                                        <?php echo number_format($customer->getTotalPoints(), 0, ',', '.'); ?> điểm
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử đơn hàng -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-shopping-bag mr-2"></i>Lịch sử đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Ngày đặt</th>
                                        <th>Số lượng SP</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orderHistory)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Chưa có đơn hàng nào</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($orderHistory as $order): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order['order_number'] ?? '#' . $order['id']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                <td><?php echo $order['total_quantity'] ?? 0; ?></td>
                                                <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                                <td>
                                                    <span class="badge badge-info"><?php echo $order['status']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử tích điểm -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-star mr-2"></i>Lịch sử tích điểm</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Loại</th>
                                        <th>Điểm</th>
                                        <th>Mô tả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pointHistory)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Chưa có lịch sử tích điểm</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pointHistory as $point): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($point['created_at'])); ?></td>
                                                <td>
                                                    <?php
                                                    $types = [
                                                        'earned' => '<span class="badge badge-success">Tích lũy</span>',
                                                        'used' => '<span class="badge badge-danger">Sử dụng</span>',
                                                        'expired' => '<span class="badge badge-secondary">Hết hạn</span>',
                                                        'bonus' => '<span class="badge badge-warning">Bonus</span>'
                                                    ];
                                                    echo $types[$point['transaction_type']] ?? $point['transaction_type'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($point['transaction_type'] == 'earned') {
                                                        echo '<span class="text-success">+' . $point['points'] . '</span>';
                                                    } elseif ($point['transaction_type'] == 'used' || $point['transaction_type'] == 'expired') {
                                                        echo '<span class="text-danger">-' . $point['points_used'] . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($point['description'] ?? '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

