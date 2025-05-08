<?php
include '../../includes/db_connect.php';
include '../includes/auth.php';

// Yêu cầu đăng nhập
requireLogin();

// Kiểm tra tham số đầu vào
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo '<div class="alert alert-danger">Thiếu thông tin khách hàng.</div>';
    exit;
}

$user_id = $_GET['user_id'];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Lấy thông tin khách hàng
$stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Không tìm thấy thông tin khách hàng.</div>';
    exit;
}

$customer = $result->fetch_assoc();

// Lấy danh sách đơn hàng của khách hàng
$sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'completed'";
$params = [$user_id];
$types = "i";

if (!empty($start_date)) {
    $sql .= " AND DATE(created_at) >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if (!empty($end_date)) {
    $sql .= " AND DATE(created_at) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="customer-info mb-4">
    <h4><?php echo $customer['fullname']; ?></h4>
    <p><strong>Email:</strong> <?php echo $customer['email']; ?></p>
    <p><strong>Số điện thoại:</strong> <?php echo $customer['phone']; ?></p>
</div>

<?php if (count($orders) > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Mã đơn hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number'] ?? 'ORDER' . $order['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                        <td>
                            <a href="../orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="font-weight-bold">
                    <td colspan="2" class="text-right">Tổng cộng:</td>
                    <td colspan="2">
                        <?php
                            $total = 0;
                            foreach ($orders as $order) {
                                $total += $order['total_amount'];
                            }
                            echo number_format($total, 0, ',', '.') . 'đ';
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">Không có đơn hàng nào trong khoảng thời gian đã chọn.</div>
<?php endif; ?> 