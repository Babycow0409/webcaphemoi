<?php
include '../includes/header.php';

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['id'];

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $success_msg = "Cập nhật trạng thái đơn hàng thành công!";
    } else {
        $error_msg = "Có lỗi xảy ra khi cập nhật trạng thái đơn hàng.";
    }
}

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT o.*, u.fullname, u.email, u.phone, u.address, u.city FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$order = $result->fetch_assoc();

// Lấy chi tiết đơn hàng
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="content-header">
    <h1>Chi tiết đơn hàng #<?php echo $order['order_number'] ?? 'ORDER' . $order['id']; ?></h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php endif; ?>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
<?php endif; ?>

<div class="row">
    <!-- Thông tin đơn hàng -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <p><strong>Mã đơn hàng:</strong> <?php echo $order['order_number'] ?? 'ORDER' . $order['id']; ?></p>
                <p><strong>Ngày đặt hàng:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</p>
                <p><strong>Phương thức thanh toán:</strong> 
                    <?php 
                        switch ($order['payment_method']) {
                            case 'cod':
                                echo 'Thanh toán khi nhận hàng (COD)';
                                break;
                            case 'banking':
                                echo 'Chuyển khoản ngân hàng';
                                break;
                            case 'momo':
                                echo 'Ví MoMo';
                                break;
                            case 'vnpay':
                                echo 'VNPay';
                                break;
                            default:
                                echo 'Không xác định';
                        }
                    ?>
                </p>
                <p><strong>Trạng thái đơn hàng:</strong>
                    <?php
                        switch ($order['status']) {
                            case 'pending':
                                echo '<span class="badge badge-warning">Chờ xử lý</span>';
                                break;
                            case 'processing':
                                echo '<span class="badge badge-info">Đang xử lý</span>';
                                break;
                            case 'shipping':
                                echo '<span class="badge badge-primary">Đang giao</span>';
                                break;
                            case 'completed':
                                echo '<span class="badge badge-success">Hoàn thành</span>';
                                break;
                            case 'cancelled':
                                echo '<span class="badge badge-danger">Đã hủy</span>';
                                break;
                            default:
                                echo '<span class="badge badge-secondary">Không xác định</span>';
                        }
                    ?>
                </p>
                
                <!-- Form cập nhật trạng thái -->
                <form method="post" action="" class="mt-3">
                    <div class="form-group">
                        <label for="status">Cập nhật trạng thái</label>
                        <select class="form-control" id="status" name="status">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="shipping" <?php echo $order['status'] == 'shipping' ? 'selected' : ''; ?>>Đang giao hàng</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Thông tin khách hàng -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?php echo $order['fullname']; ?></p>
                <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo $order['phone']; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Địa chỉ giao hàng -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Địa chỉ giao hàng</h5>
            </div>
            <div class="card-body">
                <p><strong>Địa chỉ:</strong> <?php echo $order['address']; ?></p>
                <p><strong>Thành phố:</strong> <?php echo $order['city']; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Chi tiết đơn hàng -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Chi tiết đơn hàng</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../../<?php echo $item['image']; ?>" alt="<?php echo $item['product_name']; ?>" style="width: 50px; margin-right: 10px;">
                                    <?php echo $item['product_name']; ?>
                                </div>
                            </td>
                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold">
                        <td colspan="3" class="text-right">Tổng cộng:</td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Nút in hóa đơn -->
<div class="my-3 text-right">
    <button onclick="printOrder()" class="btn btn-primary">
        <i class="fas fa-print"></i> In hóa đơn
    </button>
</div>

<script>
function printOrder() {
    window.print();
}
</script>

<?php include '../includes/footer.php'; ?> 