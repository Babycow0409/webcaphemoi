# MODULE 4 - QUẢN LÝ KHÁCH HÀNG & BÁO CÁO
## Các Diagram Thiết Kế

---

## 1. USE CASE DIAGRAM

```
┌─────────────────────────────────────────────────────────────────┐
│                    QUẢN LÝ KHÁCH HÀNG & BÁO CÁO                  │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   Admin      │
└──────┬───────┘
       │
       ├─── Thêm/Sửa khách hàng
       ├─── Xem thông tin khách hàng
       ├─── Xem lịch sử mua hàng
       ├─── Quản lý khuyến mãi
       ├─── Quản lý tích điểm
       ├─── Tạo báo cáo doanh thu
       ├─── Xem top sản phẩm bán chạy
       ├─── Xem top khách hàng
       └─── Xuất báo cáo Excel/PDF

┌──────────────┐
│  Customer    │
└──────┬───────┘
       │
       ├─── Xem thông tin cá nhân
       ├─── Xem lịch sử đơn hàng
       ├─── Xem điểm tích lũy
       ├─── Sử dụng mã khuyến mãi
       ├─── Sử dụng điểm tích lũy
       └─── Xem cấp độ khách hàng
```

### Use Cases Chi Tiết:

1. **Thêm/Sửa khách hàng**
   - Actor: Admin
   - Mô tả: Admin có thể thêm mới hoặc chỉnh sửa thông tin khách hàng
   - Precondition: Admin đã đăng nhập
   - Postcondition: Thông tin khách hàng được cập nhật

2. **Xem lịch sử mua hàng**
   - Actor: Admin, Customer
   - Mô tả: Xem danh sách các đơn hàng của khách hàng
   - Precondition: Đã có đơn hàng

3. **Tạo báo cáo doanh thu**
   - Actor: Admin
   - Mô tả: Tạo báo cáo doanh thu theo khoảng thời gian
   - Precondition: Có dữ liệu đơn hàng

4. **Tích điểm khách hàng**
   - Actor: System (tự động)
   - Mô tả: Tích điểm cho khách hàng sau khi đặt hàng thành công
   - Precondition: Đơn hàng được xác nhận

---

## 2. SEQUENCE DIAGRAM

### 2.1. Sequence Diagram: Luồng tích điểm khách hàng

```
Customer          Checkout          OrderProcessor      LoyaltyPoint      Database
   │                  │                    │                  │              │
   │  Đặt hàng        │                    │                  │              │
   │─────────────────>│                    │                  │              │
   │                  │                    │                  │              │
   │                  │  Process Order     │                  │              │
   │                  │───────────────────>│                  │              │
   │                  │                    │                  │              │
   │                  │                    │  Tạo đơn hàng    │              │
   │                  │                    │─────────────────>│              │
   │                  │                    │<─────────────────│              │
   │                  │                    │                  │              │
   │                  │                    │  Tính điểm       │              │
   │                  │                    │─────────────────>│              │
   │                  │                    │                  │              │
   │                  │                    │                  │  Lưu điểm    │
   │                  │                    │                  │─────────────>│
   │                  │                    │                  │<─────────────│
   │                  │                    │                  │              │
   │                  │                    │  Cập nhật điểm   │              │
   │                  │                    │  trong users     │              │
   │                  │                    │─────────────────>│              │
   │                  │                    │                  │  UPDATE users│
   │                  │                    │                  │─────────────>│
   │                  │                    │                  │<─────────────│
   │                  │                    │                  │              │
   │                  │  Order Success     │                  │              │
   │<─────────────────│                    │                  │              │
   │                  │                    │                  │              │
```

### 2.2. Sequence Diagram: Xuất báo cáo doanh thu

```
Admin           Reports Page      Report Class    SalesAnalytics    Database
  │                  │                  │                │              │
  │  Chọn khoảng     │                  │                │              │
  │  thời gian       │                  │                │              │
  │─────────────────>│                  │                │              │
  │                  │                  │                │              │
  │                  │  Generate Report │                │              │
  │                  │─────────────────>│                │              │
  │                  │                  │                │              │
  │                  │                  │  Get Stats     │              │
  │                  │                  │───────────────>│              │
  │                  │                  │                │              │
  │                  │                  │                │  Query DB    │
  │                  │                  │                │─────────────>│
  │                  │                  │                │<─────────────│
  │                  │                  │                │              │
  │                  │                  │<───────────────│              │
  │                  │                  │                │              │
  │                  │  Export CSV      │                │              │
  │                  │─────────────────>│                │              │
  │                  │                  │                │              │
  │  Download File   │                  │                │              │
  │<─────────────────│                  │                │              │
  │                  │                  │                │              │
```

### 2.3. Sequence Diagram: Áp dụng khuyến mãi

```
Customer      Checkout Page    OrderProcessor    Promotion Class    Database
   │                │                 │                  │              │
   │  Nhập mã KM    │                 │                  │              │
   │───────────────>│                 │                  │              │
   │                │                 │                  │              │
   │                │  Validate Code  │                  │              │
   │                │────────────────>│                  │              │
   │                │                 │                  │              │
   │                │                 │  Load Promotion  │              │
   │                │                 │─────────────────>│              │
   │                │                 │                  │              │
   │                │                 │                  │  SELECT      │
   │                │                 │                  │─────────────>│
   │                │                 │                  │<─────────────│
   │                │                 │                  │              │
   │                │                 │  Check Valid     │              │
   │                │                 │─────────────────>│              │
   │                │                 │                  │              │
   │                │                 │  Calculate       │              │
   │                │                 │  Discount        │              │
   │                │                 │─────────────────>│              │
   │                │                 │<─────────────────│              │
   │                │                 │                  │              │
   │                │  Return Discount│                  │              │
   │                │<────────────────│                  │              │
   │                │                 │                  │              │
   │  Hiển thị giá  │                 │                  │              │
   │<───────────────│                 │                  │              │
   │                │                 │                  │              │
```

---

## 3. CLASS DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                         Customer                             │
├─────────────────────────────────────────────────────────────┤
│ - id: int                                                    │
│ - username: string                                           │
│ - email: string                                              │
│ - fullname: string                                           │
│ - phone: string                                              │
│ - totalPoints: int                                           │
│ - totalSpent: decimal                                        │
│ - customerLevel: enum                                        │
│ - lastOrderDate: datetime                                    │
├─────────────────────────────────────────────────────────────┤
│ + loadCustomer(userId: int): bool                            │
│ + getOrderHistory(limit: int): array                         │
│ + getTotalOrders(): int                                      │
│ + updateCustomerLevel(): string                              │
│ + updateTotalSpent(amount: decimal): void                    │
│ + getId(): int                                               │
│ + getFullname(): string                                      │
│ + getTotalPoints(): int                                      │
│ + getCustomerLevel(): string                                 │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                       LoyaltyPoint                           │
├─────────────────────────────────────────────────────────────┤
│ - conn: mysqli                                               │
│ + POINTS_PER_1000_VND: int = 1                              │
│ + POINTS_EXPIRY_MONTHS: int = 12                            │
├─────────────────────────────────────────────────────────────┤
│ + __construct(conn: mysqli)                                 │
│ + earnPoints(userId, orderId, amount, desc): int            │
│ + usePoints(userId, orderId, points, discount): bool        │
│ + getAvailablePoints(userId: int): int                      │
│ + getPointHistory(userId: int, limit: int): array           │
│ - updateUserTotalPoints(userId, pointsChange): void         │
│ + convertPointsToMoney(points: int): decimal                │
│ + expirePoints(): void                                      │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                        Promotion                             │
├─────────────────────────────────────────────────────────────┤
│ - conn: mysqli                                               │
│ - id: int                                                    │
│ - code: string                                               │
│ - name: string                                               │
│ - discountType: enum                                         │
│ - discountValue: decimal                                     │
│ - minOrderAmount: decimal                                    │
│ - maxDiscountAmount: decimal                                 │
│ - usageLimit: int                                            │
│ - usedCount: int                                             │
│ - startDate: datetime                                        │
│ - endDate: datetime                                          │
│ - status: enum                                               │
├─────────────────────────────────────────────────────────────┤
│ + __construct(conn: mysqli, promotionId: int)               │
│ + loadPromotion(promotionId: int): bool                      │
│ + loadByCode(code: string): bool                            │
│ + isValid(userId, orderAmount, cartItems): array            │
│ + calculateDiscount(orderAmount: decimal): decimal          │
│ + applyPromotion(userId, orderId, amount, discount): bool   │
│ - getUserUsageCount(userId: int): int                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                      SalesAnalytics                          │
├─────────────────────────────────────────────────────────────┤
│ - conn: mysqli                                               │
├─────────────────────────────────────────────────────────────┤
│ + __construct(conn: mysqli)                                 │
│ + getRevenueByDateRange(dateFrom, dateTo): array            │
│ + getTopProducts(dateFrom, dateTo, limit): array            │
│ + getTopCustomers(dateFrom, dateTo, limit): array           │
│ + getSummaryStats(dateFrom, dateTo): array                  │
│ + getRevenueByMonth(year: int): array                       │
│ + getRevenueByCategory(dateFrom, dateTo): array             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                         Report                               │
├─────────────────────────────────────────────────────────────┤
│ - conn: mysqli                                               │
│ - analytics: SalesAnalytics                                  │
├─────────────────────────────────────────────────────────────┤
│ + __construct(conn: mysqli)                                 │
│ + generateDailyReport(date: string): array                  │
│ + exportToCSV(dateFrom, dateTo, type): void                 │
│ + getReport(reportDate, reportType): array                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     OrderProcessor                           │
├─────────────────────────────────────────────────────────────┤
│ - conn: mysqli                                               │
│ - promotion: Promotion                                       │
│ - loyaltyPoint: LoyaltyPoint                                 │
│ - customer: Customer                                         │
├─────────────────────────────────────────────────────────────┤
│ + __construct(conn: mysqli)                                 │
│ + calculateOrderTotal(cart, promoCode, points, userId): array│
│ + processOrder(orderData, cart, userId): array              │
│ - generateOrderNumber(): string                             │
│ - createOrder(orderData, orderNumber, total, userId): int   │
│ - createOrderItems(orderId, cart): void                     │
└─────────────────────────────────────────────────────────────┘

                    RELATIONSHIPS:
                    
Customer ──────────┐
                   │ uses
                   ├──────────────────> LoyaltyPoint
                   │
                   │ uses
                   ├──────────────────> Promotion
                   │
OrderProcessor ────┤ uses
                   ├──────────────────> Customer
                   │
                   │ uses
                   ├──────────────────> Promotion
                   │
                   │ uses
                   └──────────────────> LoyaltyPoint

Report ────────────┐ uses
                   └──────────────────> SalesAnalytics
```

---

## 4. ERD (Entity Relationship Diagram)

```
┌─────────────────────┐
│       users         │
├─────────────────────┤
│ PK id               │
│    username         │
│    email            │
│    fullname         │
│    phone            │
│    total_points     │
│    total_spent      │
│    customer_level   │
│    last_order_date  │
│    active           │
│    created_at       │
└──────────┬──────────┘
           │
           │ 1
           │
           │ *
┌──────────▼──────────┐
│      orders         │
├─────────────────────┤
│ PK id               │
│ FK user_id          │──┐
│    order_number     │  │
│    total_amount     │  │
│    discount_amount  │  │
│ FK promotion_id     │──┼──┐
│    points_used      │  │  │
│    points_earned    │  │  │
│    status           │  │  │
│    order_date       │  │  │
└──────────┬──────────┘  │  │
           │             │  │
           │ 1           │  │
           │             │  │
           │ *           │  │
┌──────────▼──────────┐  │  │
│   order_items       │  │  │
├─────────────────────┤  │  │
│ PK id               │  │  │
│ FK order_id         │──┘  │
│    product_name     │     │
│    quantity         │     │
│    price            │     │
└─────────────────────┘     │
                            │
┌─────────────────────┐     │
│   promotions        │     │
├─────────────────────┤     │
│ PK id               │     │
│    code             │     │
│    name             │     │
│    discount_type    │     │
│    discount_value   │     │
│    min_order_amount │     │
│    usage_limit      │     │
│    start_date       │     │
│    end_date         │     │
│    status           │     │
└──────────┬──────────┘     │
           │                │
           │ 1              │
           │                │
           │ *              │
┌──────────▼──────────┐     │
│ promotion_usage     │     │
├─────────────────────┤     │
│ PK id               │     │
│ FK promotion_id     │─────┘
│ FK user_id          │──┐
│ FK order_id         │  │
│    discount_amount  │  │
│    used_at          │  │
└─────────────────────┘  │
                         │
┌─────────────────────┐  │
│  loyalty_points     │  │
├─────────────────────┤  │
│ PK id               │  │
│ FK user_id          │──┘
│ FK order_id         │──┐
│    points           │  │
│    points_used      │  │
│    points_available │  │
│    transaction_type │  │
│    description      │  │
│    expiry_date      │  │
│    created_at       │  │
└─────────────────────┘  │
                         │
┌─────────────────────┐  │
│  sales_reports      │  │
├─────────────────────┤  │
│ PK id               │  │
│    report_date      │  │
│    report_type      │  │
│    total_orders     │  │
│    total_revenue    │  │
│    total_discount   │  │
│    net_revenue      │  │
│    total_customers  │  │
│    top_product_id   │  │
│    created_at       │  │
└─────────────────────┘  │
                         │
┌─────────────────────┐  │
│    products         │  │
├─────────────────────┤  │
│ PK id               │  │
│    name             │  │
│    price            │  │
│    stock            │  │
│    category_id      │  │
└─────────────────────┘  │
```

### Mối quan hệ:

1. **users** ──(1:N)──> **orders**
   - Một khách hàng có nhiều đơn hàng

2. **orders** ──(1:N)──> **order_items**
   - Một đơn hàng có nhiều sản phẩm

3. **promotions** ──(1:N)──> **promotion_usage**
   - Một mã khuyến mãi có thể được sử dụng nhiều lần

4. **users** ──(1:N)──> **promotion_usage**
   - Một khách hàng có thể sử dụng nhiều mã khuyến mãi

5. **orders** ──(1:1)──> **promotion_usage**
   - Một đơn hàng chỉ áp dụng một mã khuyến mãi (nếu có)

6. **users** ──(1:N)──> **loyalty_points**
   - Một khách hàng có nhiều giao dịch tích điểm

7. **orders** ──(1:N)──> **loyalty_points**
   - Một đơn hàng có thể sinh ra nhiều giao dịch điểm (tích lũy, sử dụng)

---

## 5. ACTIVITY DIAGRAM

### 5.1. Luồng đặt hàng với khuyến mãi và tích điểm

```
[Start]
   │
   ▼
[Nhập thông tin thanh toán]
   │
   ▼
[Chọn mã khuyến mãi?]
   │
   ├─ Yes ──> [Kiểm tra mã hợp lệ]
   │           │
   │           ├─ Invalid ──> [Thông báo lỗi]
   │           │               │
   │           │               └─> [Quay lại]
   │           │
   │           └─ Valid ──> [Tính giảm giá]
   │
   └─ No ──> [Tiếp tục]
             │
             ▼
[Chọn sử dụng điểm?]
   │
   ├─ Yes ──> [Kiểm tra điểm có đủ?]
   │           │
   │           ├─ Không đủ ──> [Thông báo]
   │           │               │
   │           │               └─> [Tiếp tục không dùng điểm]
   │           │
   │           └─ Đủ ──> [Tính giảm giá từ điểm]
   │
   └─ No ──> [Tiếp tục]
             │
             ▼
[Tính tổng tiền cuối cùng]
   │
   ▼
[Kiểm tra tồn kho]
   │
   ├─ Không đủ ──> [Thông báo] ──> [End]
   │
   └─ Đủ ──> [Tạo đơn hàng]
             │
             ▼
[Lưu chi tiết đơn hàng]
   │
   ▼
[Cập nhật tồn kho]
   │
   ▼
[Áp dụng khuyến mãi (nếu có)]
   │
   ▼
[Sử dụng điểm (nếu có)]
   │
   ▼
[Tích điểm cho khách hàng]
   │
   ▼
[Cập nhật thông tin khách hàng]
   │
   ▼
[Xóa giỏ hàng]
   │
   ▼
[Hiển thị trang thành công]
   │
   ▼
[End]
```

---

## 6. COMPONENT DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                    Customer Management Module                │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │   Customer   │    │ LoyaltyPoint │    │  Promotion   │  │
│  │    Class     │    │    Class     │    │    Class     │  │
│  └──────┬───────┘    └──────┬───────┘    └──────┬───────┘  │
│         │                   │                    │           │
│         └───────────────────┼────────────────────┘           │
│                             │                                │
│                    ┌────────▼────────┐                       │
│                    │  Database Layer │                       │
│                    │   (MySQL/MariaDB)                       │
│                    └─────────────────┘                       │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                      Reporting Module                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │    Report    │    │SalesAnalytics│    │   Export     │  │
│  │    Class     │    │    Class     │    │   Service    │  │
│  └──────┬───────┘    └──────┬───────┘    └──────┬───────┘  │
│         │                   │                    │           │
│         └───────────────────┼────────────────────┘           │
│                             │                                │
│                    ┌────────▼────────┐                       │
│                    │  Database Layer │                       │
│                    │   (MySQL/MariaDB)                       │
│                    └─────────────────┘                       │
│                                                              │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                     Order Processing Module                  │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐                                           │
│  │OrderProcessor│                                           │
│  │    Class     │                                           │
│  └──────┬───────┘                                           │
│         │                                                    │
│         ├──> Customer                                        │
│         ├──> Promotion                                       │
│         ├──> LoyaltyPoint                                    │
│         │                                                    │
│         └──> Database                                        │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## TÓM TẮT

### Các thành phần chính:

1. **Customer Class**: Quản lý thông tin khách hàng, lịch sử mua hàng, cấp độ
2. **LoyaltyPoint Class**: Quản lý hệ thống tích điểm, sử dụng điểm
3. **Promotion Class**: Quản lý khuyến mãi, kiểm tra hợp lệ, tính giảm giá
4. **SalesAnalytics Class**: Phân tích doanh số, top sản phẩm, top khách hàng
5. **Report Class**: Tạo báo cáo, xuất Excel/CSV
6. **OrderProcessor Class**: Xử lý đơn hàng với đầy đủ tính năng

### Database Tables:

1. **loyalty_points**: Lưu trữ điểm tích lũy
2. **promotions**: Lưu trữ mã khuyến mãi
3. **promotion_usage**: Lịch sử sử dụng khuyến mãi
4. **sales_reports**: Báo cáo doanh thu
5. **users**: Bổ sung total_points, customer_level, total_spent
6. **orders**: Bổ sung promotion_id, discount_amount, points_used, points_earned

### Workflow chính:

1. Khách hàng đặt hàng → Kiểm tra khuyến mãi → Áp dụng điểm → Tính tổng
2. Tạo đơn hàng → Cập nhật tồn kho → Tích điểm → Cập nhật thông tin khách hàng
3. Admin xem báo cáo → Phân tích dữ liệu → Xuất Excel/PDF

