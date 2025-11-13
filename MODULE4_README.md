# MODULE 4 - QUẢN LÝ KHÁCH HÀNG & BÁO CÁO

## Tổng quan

Module này bao gồm các chức năng:
- Quản lý thông tin khách hàng (CRM cơ bản)
- Hệ thống tích điểm khách hàng
- Quản lý khuyến mãi
- Báo cáo doanh thu và thống kê
- Top sản phẩm bán chạy
- Top khách hàng

## Cài đặt

### Bước 1: Import Database

Chạy file SQL để tạo các bảng cần thiết:

```sql
-- Truy cập phpMyAdmin: http://localhost/phpmyadmin
-- Chọn database: lab1
-- Import file: database_module4.sql
```

Hoặc chạy từ command line:

```bash
mysql -u root -p lab1 < database_module4.sql
```

### Bước 2: Kiểm tra cấu trúc

Đảm bảo các bảng sau đã được tạo:
- `loyalty_points`
- `promotions`
- `promotion_usage`
- `sales_reports`

Và các cột mới đã được thêm vào:
- `users`: total_points, customer_level, total_spent, last_order_date
- `orders`: promotion_id, discount_amount, points_used, points_earned

## Cấu trúc thư mục

```
lab1/
├── classes/
│   ├── Customer.php          # Class quản lý khách hàng
│   ├── LoyaltyPoint.php      # Class quản lý tích điểm
│   ├── Promotion.php         # Class quản lý khuyến mãi
│   ├── SalesAnalytics.php    # Class phân tích doanh số
│   └── Report.php            # Class tạo báo cáo
├── includes/
│   └── order_processor.php   # Xử lý đơn hàng với khuyến mãi và tích điểm
├── admin/
│   ├── customers/
│   │   ├── index.php         # Danh sách khách hàng
│   │   └── view.php          # Chi tiết khách hàng
│   └── reports/
│       ├── index.php         # Dashboard báo cáo
│       └── export.php        # Xuất báo cáo Excel
├── database_module4.sql      # Script tạo database
└── MODULE4_DIAGRAMS.md       # Các diagram thiết kế
```

## Sử dụng

### 1. Quản lý khách hàng

Truy cập: `http://localhost/webcaphe/lab1/admin/customers/`

**Chức năng:**
- Xem danh sách khách hàng
- Tìm kiếm khách hàng
- Lọc theo cấp độ (Đồng, Bạc, Vàng, Bạch Kim)
- Xem chi tiết khách hàng
- Xem lịch sử đơn hàng
- Xem lịch sử tích điểm

### 2. Hệ thống tích điểm

**Quy tắc tích điểm:**
- 1 điểm cho mỗi 1,000 VNĐ đơn hàng
- Điểm hết hạn sau 12 tháng
- 100 điểm = 10,000 VNĐ (khi sử dụng)
- Tối đa sử dụng 50% giá trị đơn hàng

**API sử dụng:**

```php
require_once 'classes/LoyaltyPoint.php';

$loyaltyPoint = new LoyaltyPoint($conn);

// Tích điểm
$points = $loyaltyPoint->earnPoints($userId, $orderId, $orderAmount, $description);

// Sử dụng điểm
$loyaltyPoint->usePoints($userId, $orderId, $pointsToUse, $discountAmount);

// Lấy điểm có sẵn
$availablePoints = $loyaltyPoint->getAvailablePoints($userId);

// Lịch sử tích điểm
$history = $loyaltyPoint->getPointHistory($userId, 20);
```

### 3. Quản lý khuyến mãi

**Các loại khuyến mãi:**
- **Percentage**: Giảm theo phần trăm (ví dụ: 10%)
- **Fixed**: Giảm số tiền cố định (ví dụ: 50,000 VNĐ)
- **Free Shipping**: Miễn phí vận chuyển

**Tạo khuyến mãi:**

```php
require_once 'classes/Promotion.php';

$promotion = new Promotion($conn);

// Kiểm tra khuyến mãi hợp lệ
$validation = $promotion->isValid($userId, $orderAmount, $cartItems);

if ($validation['valid']) {
    // Tính giảm giá
    $discount = $promotion->calculateDiscount($orderAmount);
    
    // Áp dụng khuyến mãi
    $promotion->applyPromotion($userId, $orderId, $orderAmount, $discount);
}
```

**Mã khuyến mãi mẫu:**
- `WELCOME10`: Giảm 10% cho đơn hàng đầu tiên
- `FREESHIP`: Miễn phí vận chuyển cho đơn hàng > 300,000đ
- `SALE20`: Giảm 20% cho đơn hàng > 500,000đ

### 4. Xử lý đơn hàng với khuyến mãi và tích điểm

**Sử dụng OrderProcessor:**

```php
require_once 'includes/order_processor.php';

$processor = new OrderProcessor($conn);

// Tính tổng tiền với khuyến mãi và điểm
$orderTotal = $processor->calculateOrderTotal(
    $cart, 
    $promotionCode, 
    $pointsToUse, 
    $userId
);

// Xử lý đặt hàng
$result = $processor->processOrder($orderData, $cart, $userId);

if ($result['success']) {
    echo "Đơn hàng thành công!";
    echo "Điểm tích lũy: " . $result['points_earned'];
}
```

### 5. Báo cáo và thống kê

Truy cập: `http://localhost/webcaphe/lab1/admin/reports/`

**Chức năng:**
- Thống kê tổng quan (tổng đơn hàng, doanh thu, giảm giá)
- Top 10 sản phẩm bán chạy
- Top 10 khách hàng
- Biểu đồ doanh thu theo ngày
- Xuất báo cáo Excel (CSV)

**Sử dụng SalesAnalytics:**

```php
require_once 'classes/SalesAnalytics.php';

$analytics = new SalesAnalytics($conn);

// Lấy thống kê tổng quan
$stats = $analytics->getSummaryStats($dateFrom, $dateTo);

// Top sản phẩm
$topProducts = $analytics->getTopProducts($dateFrom, $dateTo, 10);

// Top khách hàng
$topCustomers = $analytics->getTopCustomers($dateFrom, $dateTo, 10);

// Doanh thu theo ngày
$revenueByDate = $analytics->getRevenueByDateRange($dateFrom, $dateTo);
```

### 6. Xuất báo cáo Excel

**URL:**
```
http://localhost/webcaphe/lab1/admin/reports/export.php?type=revenue&date_from=2025-01-01&date_to=2025-01-31
```

**Các loại báo cáo:**
- `revenue`: Báo cáo doanh thu
- `products`: Báo cáo sản phẩm
- `customers`: Báo cáo khách hàng

## Cấp độ khách hàng

Hệ thống tự động phân loại khách hàng dựa trên tổng chi tiêu:

- **Bronze (Đồng)**: < 500,000 VNĐ
- **Silver (Bạc)**: 500,000 - 2,000,000 VNĐ
- **Gold (Vàng)**: 2,000,000 - 5,000,000 VNĐ
- **Platinum (Bạch Kim)**: ≥ 5,000,000 VNĐ

## Tích hợp vào checkout

Để tích hợp vào trang checkout hiện tại:

1. Thêm form nhập mã khuyến mãi
2. Thêm checkbox sử dụng điểm tích lũy
3. Sử dụng `OrderProcessor` để xử lý đơn hàng

**Ví dụ form:**

```html
<form action="place-order.php" method="post">
    <!-- Thông tin khách hàng -->
    
    <!-- Mã khuyến mãi -->
    <div class="form-group">
        <label>Mã khuyến mãi (nếu có)</label>
        <input type="text" name="promotion_code" class="form-control" placeholder="Nhập mã khuyến mãi">
    </div>
    
    <!-- Sử dụng điểm tích lũy -->
    <div class="form-group">
        <label>
            <input type="checkbox" name="use_points" value="1">
            Sử dụng điểm tích lũy (Bạn có: <?php echo $availablePoints; ?> điểm)
        </label>
        <input type="number" name="points_to_use" class="form-control" min="0" max="<?php echo $availablePoints; ?>" value="0">
    </div>
    
    <!-- Tổng tiền sẽ được tính tự động bằng JavaScript hoặc PHP -->
</form>
```

## Lưu ý

1. **Bảo mật**: Đảm bảo kiểm tra quyền admin trước khi truy cập các trang quản lý
2. **Transaction**: Sử dụng transaction khi xử lý đơn hàng để đảm bảo tính nhất quán dữ liệu
3. **Validation**: Luôn kiểm tra tính hợp lệ của mã khuyến mãi và điểm tích lũy
4. **Stock**: Kiểm tra tồn kho trước khi đặt hàng
5. **Points Expiry**: Chạy cron job để xóa điểm hết hạn định kỳ

## Cron Jobs

Để tự động xóa điểm hết hạn, thêm vào crontab:

```bash
# Xóa điểm hết hạn mỗi ngày lúc 2:00 AM
0 2 * * * php /path/to/lab1/cron/expire_points.php
```

File `cron/expire_points.php`:

```php
<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../classes/LoyaltyPoint.php';

$loyaltyPoint = new LoyaltyPoint($conn);
$loyaltyPoint->expirePoints();

echo "Points expired successfully at " . date('Y-m-d H:i:s') . "\n";
?>
```

## Troubleshooting

### Lỗi: "Class not found"
- Kiểm tra đường dẫn `require_once` có đúng không
- Đảm bảo file class đã được tạo

### Lỗi: "Table doesn't exist"
- Chạy lại file `database_module4.sql`
- Kiểm tra tên database có đúng không

### Lỗi: "Column doesn't exist"
- Kiểm tra các cột mới đã được thêm vào bảng `users` và `orders` chưa
- Chạy lại các câu lệnh ALTER TABLE

## Tác giả

Module 4 - Quản lý khách hàng & Báo cáo
Phát triển theo yêu cầu phân tích thiết kế hướng đối tượng

