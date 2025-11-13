-- Sửa lỗi bảng promotions
-- Xóa bảng cũ nếu có (CẨN THẬN: sẽ mất dữ liệu)
-- DROP TABLE IF EXISTS `promotion_usage`;
-- DROP TABLE IF EXISTS `promotions`;

-- Tạo lại bảng promotions (đã sửa lỗi)
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
  UNIQUE KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

