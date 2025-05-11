<?php
session_start();
include 'db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-orders.php");
    exit;
}

$order_id = (int)$_GET['id'];

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my-orders.php");
    exit;
}

$order = $result->fetch_assoc();

// Lấy các sản phẩm trong đơn hàng
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .order-container {
            margin-top: 20px;
            background: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .order-info {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
            margin-left: 10px;
        }
        
        .order-status.processing {
            background-color: #ff9800;
        }
        
        .order-status.shipped {
            background-color: #2196F3;
        }
        
        .order-status.delivered {
            background-color: #4CAF50;
        }
        
        .order-status.canceled {
            background-color: #f44336;
        }
        
        .order-section {
            margin-bottom: 20px;
        }
        
        .order-section h3 {
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .order-items {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-items th, .order-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-items th {
            background-color: #f2f2f2;
        }
        
        .order-total {
            text-align: right;
            font-weight: bold;
            margin-top: 15px;
            font-size: 1.2em;
        }
        
        .back-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1>Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
        
        <div class="order-container">
            <div class="order-info">
                <h2>
                    Đơn hàng #<?php echo $order_id; ?>
                    <span class="order-status <?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </h2>
                <p>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                <p>Phương thức thanh toán: <?php echo htmlspecialchars($order['payment_method']); ?></p>
            </div>
            
            <div class="order-section">
                <h3>Thông tin giao hàng</h3>
                <p>Họ tên: <?php echo htmlspecialchars($order['shipping_name']); ?></p>
                <p>Địa chỉ: <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p>Thành phố: <?php echo htmlspecialchars($order['shipping_city']); ?></p>
                <p>Số điện thoại: <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
            </div>
            
            <div class="order-section">
                <h3>Các sản phẩm</h3>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="order-total">
                    Tổng cộng: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ
                </div>
            </div>
        </div>
        
        <a href="my-orders.php" class="back-btn">← Quay lại danh sách đơn hàng</a>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html> 