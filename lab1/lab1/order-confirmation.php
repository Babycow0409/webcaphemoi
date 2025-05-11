<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kiểm tra order_number
if (!isset($_GET['order_number'])) {
    header('Location: my-orders.php');
    exit;
}

$order_number = $_GET['order_number'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->bind_param("si", $order_number, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: my-orders.php');
    exit;
}

$order = $result->fetch_assoc();

// Lấy chi tiết đơn hàng
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$result = $stmt->get_result();

$order_items = [];
while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng | Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .order-confirmation {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .success-header .fas {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-details, .shipping-details, .items-details {
            margin-bottom: 30px;
        }
        .order-details h2, .shipping-details h2, .items-details h2 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #6f4e37;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            flex: 1;
            font-weight: bold;
        }
        .detail-value {
            flex: 2;
        }
        .order-items {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th, .order-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .order-items th {
            background-color: #f5f5f5;
        }
        .order-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .actions {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #6f4e37;
            color: white;
        }
        .btn-primary:hover {
            background-color: #5d4229;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .print-header, .print-footer {
            display: none;
        }
        
        @media print {
            .header, .footer, .actions, .print-hide {
                display: none;
            }
            .order-confirmation {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .print-header, .print-footer {
                display: block;
                text-align: center;
                margin: 20px 0;
            }
            .print-header h1 {
                color: #6f4e37;
            }
            .print-footer p {
                font-size: 14px;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h1>Coffee Shop</h1>
        <p>Địa chỉ: 123 Đường Cà Phê, Quận 1, TP. Hồ Chí Minh</p>
        <p>Hotline: 1900-1234 | Email: info@coffeeshop.com</p>
    </div>
    
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="order-confirmation">
            <div class="success-header">
                <i class="fas fa-check-circle"></i>
                <h1>Đặt hàng thành công!</h1>
                <p>Cảm ơn bạn đã đặt hàng. Chúng tôi sẽ xử lý đơn hàng của bạn ngay lập tức.</p>
            </div>
            
            <div class="order-details">
                <h2>Thông tin đơn hàng</h2>
                <div class="detail-row">
                    <div class="detail-label">Mã đơn hàng:</div>
                    <div class="detail-value"><?php echo $order['order_number']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Ngày đặt hàng:</div>
                    <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Trạng thái:</div>
                    <div class="detail-value">
                        <?php
                        $status_text = '';
                        switch ($order['status']) {
                            case 'pending':
                                $status_text = 'Chờ xác nhận';
                                break;
                            case 'processing':
                                $status_text = 'Đang xử lý';
                                break;
                            case 'shipped':
                                $status_text = 'Đang giao hàng';
                                break;
                            case 'delivered':
                                $status_text = 'Đã giao hàng';
                                break;
                            case 'cancelled':
                                $status_text = 'Đã hủy';
                                break;
                            default:
                                $status_text = ucfirst($order['status']);
                        }
                        echo $status_text;
                        ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phương thức thanh toán:</div>
                    <div class="detail-value">
                        <?php
                        $payment_text = '';
                        switch ($order['payment_method']) {
                            case 'cod':
                                $payment_text = 'Thanh toán khi nhận hàng (COD)';
                                break;
                            case 'banking':
                                $payment_text = 'Chuyển khoản ngân hàng';
                                break;
                            case 'momo':
                                $payment_text = 'Ví MoMo';
                                break;
                            case 'vnpay':
                                $payment_text = 'VNPay';
                                break;
                            default:
                                $payment_text = $order['payment_method'];
                        }
                        echo $payment_text;
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="shipping-details">
                <h2>Thông tin giao hàng</h2>
                <div class="detail-row">
                    <div class="detail-label">Họ tên:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['fullname']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Số điện thoại:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Địa chỉ:</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($order['address']); ?>, 
                        <?php echo htmlspecialchars($order['city']); ?>
                    </div>
                </div>
            </div>
            
            <div class="items-details">
                <h2>Sản phẩm đã đặt</h2>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="order-total">
                    Tổng cộng: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ
                </div>
            </div>
            
            <div class="actions print-hide">
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> In hóa đơn
                </button>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
    
    <div class="print-footer">
        <p>Coffee Shop - Địa chỉ: 123 Đường Cà Phê, Quận 1, TP. Hồ Chí Minh</p>
        <p>Hotline: 1900-1234 | Website: www.coffeeshop.com</p>
        <p>Cảm ơn quý khách đã mua hàng!</p>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Xóa giỏ hàng sau khi đặt hàng thành công
        localStorage.removeItem('cart');
    </script>
</body>
</html> 