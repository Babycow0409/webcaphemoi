-- MODULE 4: QUẢN LÝ KHÁCH HÀNG & BÁO CÁO
-- Tạo các bảng cho hệ thống CRM, tích điểm, khuyến mãi, báo cáo

-- Bảng Loyalty Points (Tích điểm)
CREATE TABLE IF NOT EXISTS `loyalty_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0 COMMENT 'Số điểm tích lũy',
  `points_used` int(11) NOT NULL DEFAULT 0 COMMENT 'Số điểm đã sử dụng',
  `points_available` int(11) NOT NULL DEFAULT 0 COMMENT 'Số điểm còn lại',
  `order_id` int(11) DEFAULT NULL COMMENT 'ID đơn hàng (nếu tích điểm từ đơn hàng)',
  `transaction_type` enum('earned','used','expired','bonus') NOT NULL DEFAULT 'earned' COMMENT 'Loại giao dịch',
  `description` text DEFAULT NULL COMMENT 'Mô tả giao dịch',
  `expiry_date` date DEFAULT NULL COMMENT 'Ngày hết hạn điểm',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Promotions (Khuyến mãi)
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL COMMENT 'Mã khuyến mãi',
  `name` varchar(255) NOT NULL COMMENT 'Tên chương trình khuyến mãi',
  `description` text DEFAULT NULL COMMENT 'Mô tả',
  `discount_type` enum('percentage','fixed','free_shipping') NOT NULL DEFAULT 'percentage' COMMENT 'Loại giảm giá',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá trị giảm giá',
  `min_order_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Đơn hàng tối thiểu',
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'Giảm giá tối đa (nếu là %)',
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Giới hạn số lần sử dụng',
  `used_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Số lần đã sử dụng',
  `user_limit` int(11) DEFAULT 1 COMMENT 'Giới hạn số lần sử dụng cho mỗi user',
  `start_date` datetime NOT NULL COMMENT 'Ngày bắt đầu',
  `end_date` datetime NOT NULL COMMENT 'Ngày kết thúc',
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái',
  `applicable_products` text DEFAULT NULL COMMENT 'Danh sách product_id áp dụng (JSON)',
  `applicable_categories` text DEFAULT NULL COMMENT 'Danh sách category_id áp dụng (JSON)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Promotion Usage (Lịch sử sử dụng khuyến mãi)
CREATE TABLE IF NOT EXISTS `promotion_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotion_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL COMMENT 'Số tiền đã giảm',
  `order_amount` decimal(10,2) NOT NULL COMMENT 'Tổng tiền đơn hàng',
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `promotion_id` (`promotion_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `promotion_usage_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Sales Reports (Báo cáo doanh thu)
CREATE TABLE IF NOT EXISTS `sales_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` date NOT NULL COMMENT 'Ngày báo cáo',
  `report_type` enum('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'daily' COMMENT 'Loại báo cáo',
  `total_orders` int(11) NOT NULL DEFAULT 0 COMMENT 'Tổng số đơn hàng',
  `total_revenue` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng doanh thu',
  `total_discount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng giảm giá',
  `total_shipping` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng phí vận chuyển',
  `net_revenue` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Doanh thu thuần',
  `total_customers` int(11) NOT NULL DEFAULT 0 COMMENT 'Tổng số khách hàng',
  `new_customers` int(11) NOT NULL DEFAULT 0 COMMENT 'Số khách hàng mới',
  `top_product_id` int(11) DEFAULT NULL COMMENT 'ID sản phẩm bán chạy nhất',
  `top_product_name` varchar(255) DEFAULT NULL COMMENT 'Tên sản phẩm bán chạy nhất',
  `top_product_quantity` int(11) DEFAULT 0 COMMENT 'Số lượng sản phẩm bán chạy nhất',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report` (`report_date`,`report_type`),
  KEY `report_date` (`report_date`),
  KEY `report_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm cột promotion_id và discount_amount vào bảng orders
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `promotion_id` int(11) DEFAULT NULL COMMENT 'ID khuyến mãi áp dụng',
ADD COLUMN IF NOT EXISTS `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Số tiền giảm giá',
ADD COLUMN IF NOT EXISTS `points_used` int(11) DEFAULT 0 COMMENT 'Số điểm đã sử dụng',
ADD COLUMN IF NOT EXISTS `points_earned` int(11) DEFAULT 0 COMMENT 'Số điểm tích lũy',
ADD KEY `promotion_id` (`promotion_id`);

-- Thêm constraint cho promotion_id
ALTER TABLE `orders`
ADD CONSTRAINT `orders_ibfk_promotion` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL;

-- Thêm cột total_points vào bảng users để lưu tổng điểm tích lũy
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `total_points` int(11) NOT NULL DEFAULT 0 COMMENT 'Tổng điểm tích lũy';

-- Thêm cột customer_level vào bảng users để phân loại khách hàng
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `customer_level` enum('bronze','silver','gold','platinum') NOT NULL DEFAULT 'bronze' COMMENT 'Cấp độ khách hàng',
ADD COLUMN IF NOT EXISTS `total_spent` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền đã chi tiêu',
ADD COLUMN IF NOT EXISTS `last_order_date` datetime DEFAULT NULL COMMENT 'Ngày đơn hàng cuối cùng';

-- Tạo index cho hiệu năng
CREATE INDEX IF NOT EXISTS `idx_users_total_spent` ON `users` (`total_spent`);
CREATE INDEX IF NOT EXISTS `idx_orders_promotion` ON `orders` (`promotion_id`);
CREATE INDEX IF NOT EXISTS `idx_loyalty_points_user` ON `loyalty_points` (`user_id`, `transaction_type`);
CREATE INDEX IF NOT EXISTS `idx_promotions_active` ON `promotions` (`status`, `start_date`, `end_date`);

-- Insert dữ liệu mẫu cho promotions
INSERT INTO `promotions` (`code`, `name`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `start_date`, `end_date`, `status`) VALUES
('WELCOME10', 'Chào mừng khách hàng mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10.00, 100000.00, 50000.00, 100, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'active'),
('FREESHIP', 'Miễn phí vận chuyển', 'Miễn phí vận chuyển cho đơn hàng trên 300.000đ', 'free_shipping', 0.00, 300000.00, NULL, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'active'),
('SALE20', 'Giảm giá 20%', 'Giảm 20% cho đơn hàng trên 500.000đ', 'percentage', 20.00, 500000.00, 200000.00, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 'active')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

