<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy đơn hàng từ database
try {
    $stmt = $conn->prepare("
        SELECT o.*, oi.product_name, oi.quantity, oi.price 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = [];
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if(!isset($orders[$row['id']])) {
            $orders[$row['id']] = [
                'order_code' => $row['order_code'],
                'date' => $row['created_at'],
                'status' => $row['status'],
                'total_amount' => $row['total_amount'],
                'shipping_info' => [
                    'name' => $row['shipping_name'],
                    'email' => $row['shipping_email'],
                    'phone' => $row['shipping_phone'],
                    'address' => $row['shipping_address'],
                    'city' => $row['shipping_city']
                ],
                'payment_method' => $row['payment_method'],
                'items' => []
            ];
        }
        if($row['product_name']) {
            $orders[$row['id']]['items'][] = [
                'name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['price']
            ];
        }
    }
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        /* Copy các style cơ bản từ index.php */
        .orders-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        .order-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-pending {
            background-color: #ffd700;
            color: #000;
        }

        .status-processing {
            background-color: #87ceeb;
            color: #000;
        }

        .status-shipping {
            background-color: #98fb98;
            color: #000;
        }

        .status-completed {
            background-color: #90ee90;
            color: #000;
        }

        .status-cancelled {
            background-color: #ff6b6b;
            color: #fff;
        }

        .order-items {
            margin: 15px 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }

        .order-total {
            text-align: right;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 15px;
        }

        .no-orders {
            text-align: center;
            padding: 50px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Copy header từ index.php -->

    <div class="orders-container">
        <h1>Đơn hàng của tôi</h1>
        
        <?php if(empty($orders)): ?>
            <div class="no-orders">
                <h3>Bạn chưa có đơn hàng nào</h3>
                <p>Hãy khám phá các sản phẩm của chúng tôi và đặt hàng ngay!</p>
                <a href="products.php" class="btn">Xem sản phẩm</a>
            </div>
        <?php else: ?>
            <?php foreach($orders as $order_id => $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Đơn hàng #<?php echo $order['order_code']; ?></h3>
                            <p>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['date'])); ?></p>
                        </div>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php
                            switch($order['status']) {
                                case 'pending':
                                    echo 'Chờ xác nhận';
                                    break;
                                case 'processing':
                                    echo 'Đang xử lý';
                                    break;
                                case 'shipping':
                                    echo 'Đang vận chuyển';
                                    break;
                                case 'completed':
                                    echo 'Đã hoàn thành';
                                    break;
                                case 'cancelled':
                                    echo 'Đã hủy';
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                    <div class="order-items">
                        <?php foreach($order['items'] as $item): ?>
                            <div class="order-item">
                                <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                                <span><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-total">
                        Tổng cộng: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Copy footer từ index.php -->
</body>
</html> 