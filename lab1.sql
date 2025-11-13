-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 12, 2025 lúc 11:43 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `lab1`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(50) NOT NULL,
  `district` varchar(50) NOT NULL,
  `ward` varchar(50) NOT NULL,
  `address_detail` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `phone`, `province`, `district`, `ward`, `address_detail`, `is_default`, `created_at`) VALUES
(1, 2, 'Nguyễn Phúc Đăng Khoa', '0865545705', 'hồ chí minh', 'hồ chí minh', 'hồ chí minh', '135/3a tân kì tân quý phường tân sơn nhì', 1, '2025-05-11 10:41:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Arabica', 'Cà phê Arabica với hương vị thơm ngon, chua nhẹ'),
(2, 'Robusta', 'Cà phê Robusta đậm đà, hương vị mạnh mẽ'),
(3, 'Chồn', 'Cà phê Chồn đặc biệt, hương vị độc đáo'),
(4, 'Khác', 'Các loại cà phê khác');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(30) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_name` varchar(100) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','confirmed','shipping','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `status_note` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `shipping_name`, `shipping_address`, `shipping_city`, `shipping_phone`, `payment_method`, `total_amount`, `status`, `status_note`, `updated_at`, `order_date`) VALUES
(1, NULL, 1, 'đăng khoa', '135 tan ki tan quy', 'hồ chí minh', '04092005', 'cod', 150000.00, 'processing', '', '2025-05-12 19:57:05', '2025-04-21 02:19:09'),
(3, 'ORDER19700101010000704', 2, 'đăng khoa', '135 tan ki tan quy', 'hồ chí minh', '0865545705', 'cod', 1400000.00, 'shipping', '', '2025-05-12 19:56:55', '2025-04-22 00:30:34'),
(5, 'ORDER19700101010000854', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 700000.00, 'shipping', '', '2025-05-12 19:56:39', '2025-05-02 09:25:26'),
(6, 'ORDER19700101010000721', 2, 'Nguyễn Phúc Đăng Khoa', '135/3a tân ký tân quý', 'Không rõ', '0865545705', 'cod', 550000.00, 'delivered', '', '2025-05-12 19:56:33', '2025-05-05 10:03:14'),
(7, 'ORDER19700101010000733', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 700000.00, 'cancelled', '', '2025-05-12 19:56:27', '2025-05-05 16:29:18'),
(8, 'ORDER19700101010000653', 2, 'Nguyễn Phúc Đăng Khoa', '135/3a tân', 'Không rõ', '0865545705', 'cod', 1050000.00, 'cancelled', '', '2025-05-12 19:56:19', '2025-05-07 09:08:56'),
(9, 'ORDER19700101010000396', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 150000.00, 'delivered', '', '2025-05-12 19:56:13', '2025-05-07 09:22:23'),
(10, 'ORDER19700101010000479', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 150000.00, 'shipping', '', '2025-05-12 19:56:06', '2025-05-07 09:27:55'),
(11, 'ORDER19700101010000748', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 850000.00, 'processing', '', '2025-05-12 19:55:59', '2025-05-07 09:34:41'),
(12, 'ORDER20250509062619283', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 350000.00, 'pending', '', '2025-05-12 19:55:51', '2025-05-09 06:26:19'),
(13, 'ORDER20250512093543433', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 600000.00, 'confirmed', '', '2025-05-12 21:06:07', '2025-05-12 09:35:43'),
(14, 'ORDER20250512230715896', 3, 'le hieu', 'tphcm\r\ntphcm', 'Không rõ', '122123231231', 'cod', 3100000.00, 'pending', '', '2025-05-12 21:20:45', '2025-05-12 23:07:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `quantity`, `price`) VALUES
(1, 1, 'Robusta Việt Nam (Cà phê Vối)', 1, 150000.00),
(2, 3, 'Robusta Ấn Độ (Indian Robusta Cherry)', 2, 350000.00),
(3, 3, 'Robusta Ấn Độ (Indian Robusta Cherry)', 2, 350000.00),
(4, 5, 'Robusta Ấn Độ (Indian Robusta Cherry)', 1, 350000.00),
(5, 5, 'Robusta Ấn Độ (Indian Robusta Cherry)', 1, 350000.00),
(6, 6, 'Robusta Việt Nam (Cà phê Vối)', 1, 150000.00),
(7, 6, 'Robusta Uganda', 1, 200000.00),
(8, 6, 'Robusta Uganda', 1, 200000.00),
(9, 7, 'Robusta Ấn Độ (Indian Robusta Cherry)', 1, 350000.00),
(10, 7, 'Robusta Ấn Độ (Indian Robusta Cherry)', 1, 350000.00),
(11, 8, 'Robusta Ấn Độ (Indian Robusta Cherry)', 2, 350000.00),
(12, 8, 'Robusta Việt Nam (Cà phê Vối)', 1, 150000.00),
(13, 8, 'Robusta Uganda', 1, 200000.00),
(14, 9, 'Robusta Việt Nam (Cà phê Vối)', 1, 150000.00),
(15, 10, 'Robusta Việt Nam (Cà phê Vối)', 1, 150000.00),
(16, 11, 'Robusta Việt Nam (Cà phê Vối)', 2, 150000.00),
(17, 11, 'Robusta Uganda', 1, 200000.00),
(18, 11, 'Robusta Ấn Độ (Indian Robusta Cherry)', 1, 350000.00),
(19, 12, 'Robusta Việt Nam (Cà phê Vối)', 1, 150000.00),
(20, 12, 'Robusta Uganda', 1, 200000.00),
(21, 13, 'Bourbon', 1, 300000.00),
(22, 13, 'SL28', 1, 300000.00),
(23, 14, 'Robusta Việt Nam (Cà phê Vối)', 2, 150000.00),
(24, 14, 'Robusta Ấn Độ (Indian Robusta Cherry)', 2, 350000.00),
(25, 14, 'Robusta Buôn Ma Thuột', 1, 150000.00),
(26, 14, 'Robusta Brazil (Conilon)', 2, 350000.00),
(27, 14, 'Robusta Uganda', 1, 200000.00),
(28, 14, 'Robusta Indonesia (Java, Sumatra)', 1, 300000.00),
(29, 14, 'Robusta Congo (DRC – Democratic Republic of Congo)', 1, 350000.00),
(30, 14, 'Robusta Cameroon', 1, 400000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `weight` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `created_at`, `active`, `featured`, `weight`, `stock`) VALUES
(20, 'Robusta Uganda', 'Nổi tiếng vì chất lượng ổn định, vị đậm đà.\r\n\r\nThường được dùng trong các loại cà phê hòa tan và espresso blend', 200000.00, 'uploads/products/product_6805d87d6a14b.jpg', 2, '2025-04-21 05:32:45', 1, 0, '250', 100),
(21, 'Robusta Việt Nam (Cà phê Vối)', 'Trồng chủ yếu ở Tây Nguyên như Đắk Lắk, Gia Lai, Lâm Đồng.\r\n\r\nVị đậm, đắng mạnh, ít chua.\r\n\r\nHàm lượng caffeine cao, thích hợp pha phin truyền thống', 150000.00, 'uploads/products/product_6805d8b4a0501.jpg', 2, '2025-04-21 05:33:40', 1, 0, '250', 100),
(22, 'Robusta Ấn Độ (Indian Robusta Cherry)', 'Có mùi thơm hơi ngọt, ít đắng hơn so với Robusta Việt Nam.\r\n\r\nĐược dùng trong pha trộn với Arabica để tạo hương vị cân bằng.', 350000.00, 'uploads/products/product_6805da1d277cd.png', 2, '2025-04-21 05:39:41', 1, 0, '250', 50),
(23, 'Robusta Buôn Ma Thuột', 'Nguồn gốc: Thành phố Buôn Ma Thuột – thủ phủ cà phê Việt Nam.\r\n\r\nHương vị: Mạnh mẽ, đậm đà, hậu vị socola.\r\n\r\nĐặc điểm: Cà phê rang đậm, thường dùng cho gu mạnh.\r\n\r\nỨng dụng: Rất phổ biến trong các dòng cà phê hòa tan.', 150000.00, 'uploads/products/product_6820415606312.jpg', 2, '2025-05-11 06:19:02', 1, 0, '250', 100),
(24, 'Robusta Indonesia (Java, Sumatra)', 'Nguồn gốc: Các đảo như Java, Sumatra.\r\n\r\nHương vị: Thân đầy, đậm vị đất, mùi khói nhẹ.\r\n\r\nĐặc điểm: Được ủ ẩm lâu (wet hulling), tạo vị rất riêng biệt.\r\n\r\nỨng dụng: Espresso blend phương Tây.', 300000.00, 'uploads/products/product_682041dee6967.jpg', 2, '2025-05-11 06:21:18', 1, 0, '250', 100),
(25, 'Robusta Brazil (Conilon)', 'Nguồn gốc: Espírito Santo – miền Đông Nam Brazil.\r\n\r\nHương vị: Đắng nhẹ, hậu vị ngắn, ít hương thơm hơn Arabica Brazil.\r\n\r\nĐặc điểm: Năng suất cao, chi phí sản xuất thấp.\r\n\r\nỨng dụng: Pha trộn, cà phê hoà tan công nghiệp.', 350000.00, 'uploads/products/product_6820472686640.jpg', 2, '2025-05-11 06:43:50', 1, 0, '250', 100),
(26, 'Robusta Congo (DRC – Democratic Republic of Congo)', 'Nguồn gốc: Trung Phi.\r\n\r\nHương vị: Vị cacao đậm, hậu vị dày, có độ đắng rõ rệt.\r\n\r\nĐặc điểm: Phát triển tự nhiên, không quá công nghiệp hóa.\r\n\r\nỨng dụng: Pha trộn, cà phê hòa tan.', 350000.00, 'uploads/products/product_68204dc0ae5fb.png', 2, '2025-05-11 07:12:00', 1, 0, '500', 100),
(27, 'Robusta Cameroon', 'Nguồn gốc: Vùng núi phía Tây Cameroon.\r\n\r\nHương vị: Thơm nhẹ, vị cacao, ít chua.\r\n\r\nĐặc điểm: Canh tác theo mô hình truyền thống, bền vững.\r\n\r\nỨng dụng: Chế biến theo hướng cao cấp và xuất khẩu sang châu Âu.', 400000.00, 'uploads/products/product_68204e70a7ab8.jpg', 2, '2025-05-11 07:14:56', 1, 0, '250', 100),
(28, 'Robusta Côte d’Ivoire (Ivory Coast)', 'Nguồn gốc: Tây Phi.\r\n\r\nHương vị: Đậm, đắng, ít hậu vị, thân nhẹ.\r\n\r\nĐặc điểm: Là quốc gia sản xuất Robusta hàng đầu châu Phi.\r\n\r\nỨng dụng: Sản xuất cà phê hòa tan quy mô lớn.', 250000.00, 'uploads/products/product_68204e9fe695d.webp', 2, '2025-05-11 07:15:43', 1, 0, '250', 100),
(29, 'Robusta Laos (Bolaven Plateau)', 'Nguồn gốc: Cao nguyên Bolaven, miền Nam Lào.\r\n\r\nHương vị: Vị dịu, hậu ngọt nhẹ, thơm nhẹ.\r\n\r\nĐặc điểm: Độ cao tương đối (1000m), khí hậu tương đồng Tây Nguyên Việt Nam.\r\n\r\nỨng dụng: Dùng pha máy hoặc phin, đang dần phát triển thương hiệu riêng.', 250000.00, 'uploads/products/product_682053717a90c.jpg', 2, '2025-05-11 07:36:17', 1, 0, '250', 100),
(30, 'Robusta Cầu Đất (Lâm Đồng)', '<ul data-start=\"179\" data-end=\"451\"><li data-start=\"179\" data-end=\"264\" class=\"\"><p data-start=\"181\" data-end=\"264\" class=\"\"><strong data-start=\"181\" data-end=\"195\">Nguồn gốc:</strong> Cao nguyên Cầu Đất, nơi chủ yếu trồng Arabica nhưng cũng có Robusta.</p>\r\n</li>\r\n<li data-start=\"265\" data-end=\"344\" class=\"\">\r\n<p data-start=\"267\" data-end=\"344\" class=\"\"><strong data-start=\"267\" data-end=\"280\">Hương vị:</strong> Đậm đà, hậu vị ngọt nhẹ, ít đắng hơn so với Robusta Tây Nguyên.</p>\r\n</li>\r\n<li data-start=\"345\" data-end=\"451\" class=\"\">\r\n<p data-start=\"347\" data-end=\"451\" class=\"\"><strong data-start=\"347\" data-end=\"360\">Đặc điểm:</strong> Trồng ở độ cao cao hiếm hoi với Robusta (~900–1000m), nên có sự pha trộn hương vị tinh tế.</p>\r\n</li>\r\n</ul>', 150000.00, 'uploads/products/product_6820750c95cce.jpg', 2, '2025-05-11 09:59:40', 1, 0, '250', 0),
(31, 'Robusta Chư Sê (Gia Lai)', '<ul data-start=\"494\" data-end=\"726\"><li data-start=\"494\" data-end=\"593\" class=\"\"><p data-start=\"496\" data-end=\"593\" class=\"\"><strong data-start=\"496\" data-end=\"510\">Nguồn gốc:</strong> Huyện Chư Sê, tỉnh Gia Lai – một trong những vùng trồng cà phê lớn của Tây Nguyên.</p>\r\n</li>\r\n<li data-start=\"594\" data-end=\"645\" class=\"\">\r\n<p data-start=\"596\" data-end=\"645\" class=\"\"><strong data-start=\"596\" data-end=\"609\">Hương vị:</strong> Đậm, đắng vừa, ít chua, vị hậu sâu.</p>\r\n</li>\r\n<li data-start=\"646\" data-end=\"726\" class=\"\">\r\n<p data-start=\"648\" data-end=\"726\" class=\"\"><strong data-start=\"648\" data-end=\"662\">Thích hợp:</strong> Làm nền cho các loại cà phê pha máy hoặc pha phin truyền thống.</p>\r\n</li>\r\n</ul>', 150000.00, 'uploads/products/product_682191bf2e731.webp', 2, '2025-05-12 06:14:23', 1, 0, '250', 100),
(32, 'Robusta Đắk Song (Đắk Nông)', '<ul data-start=\"772\" data-end=\"968\"><li data-start=\"772\" data-end=\"840\" class=\"\"><p data-start=\"774\" data-end=\"840\" class=\"\"><strong data-start=\"774\" data-end=\"788\">Nguồn gốc:</strong> Vùng cao Đắk Song, nơi có khí hậu mát mẻ quanh năm.</p>\r\n</li>\r\n<li data-start=\"841\" data-end=\"898\" class=\"\">\r\n<p data-start=\"843\" data-end=\"898\" class=\"\"><strong data-start=\"843\" data-end=\"856\">Hương vị:</strong> Đắng nhẹ, thơm hạt rang, hậu vị cân bằng.</p>\r\n</li>\r\n<li data-start=\"899\" data-end=\"968\" class=\"\">\r\n<p data-start=\"901\" data-end=\"968\" class=\"\"><strong data-start=\"901\" data-end=\"914\">Đặc điểm:</strong> Được nhiều nhà rang xay lựa chọn để pha trộn (blend).</p>\r\n</li>\r\n</ul>', 200000.00, 'uploads/products/product_682191facdd42.jpg', 2, '2025-05-12 06:15:22', 1, 0, '250', 100),
(33, 'Robusta Buôn Hồ (Đắk Lắk)', '<li data-start=\"1012\" data-end=\"1083\" class=\"\"><p data-start=\"1014\" data-end=\"1083\" class=\"\"><strong data-start=\"1014\" data-end=\"1028\">Nguồn gốc:</strong> Buôn Hồ là vùng có diện tích Robusta lớn tại Việt Nam.</p>\r\n</li><li data-start=\"1084\" data-end=\"1151\" class=\"\">\r\n<p data-start=\"1086\" data-end=\"1151\" class=\"\"><strong data-start=\"1086\" data-end=\"1099\">Hương vị:</strong> Cực kỳ đậm, hậu vị kéo dài, rất \"gắt\" nếu rang đậm.</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"1152\" data-end=\"1207\" class=\"\">\r\n<p data-start=\"1154\" data-end=\"1207\" class=\"\"><strong data-start=\"1154\" data-end=\"1166\">Phù hợp:</strong> Pha phin kiểu Việt Nam (nâu đá, đen đá).</p></li>', 200000.00, 'uploads/products/product_68219261c3384.jpg', 2, '2025-05-12 06:17:05', 1, 0, '250', 100),
(34, 'Robusta Sơn La', '<ul data-start=\"1247\" data-end=\"1463\"><li data-start=\"1247\" data-end=\"1342\" class=\"\"><p data-start=\"1249\" data-end=\"1342\" class=\"\"><strong data-start=\"1249\" data-end=\"1263\">Nguồn gốc:</strong> Sơn La chủ yếu trồng Arabica, nhưng gần đây một số vùng đã thử nghiệm Robusta.</p>\r\n</li>\r\n<li data-start=\"1343\" data-end=\"1405\" class=\"\">\r\n<p data-start=\"1345\" data-end=\"1405\" class=\"\"><strong data-start=\"1345\" data-end=\"1358\">Hương vị:</strong> Nhẹ, ít đắng, chua nhẹ, mùi thơm khá đặc biệt.</p>\r\n</li>\r\n<li data-start=\"1406\" data-end=\"1463\" class=\"\">\r\n<p data-start=\"1408\" data-end=\"1463\" class=\"\"><strong data-start=\"1408\" data-end=\"1420\">Giá trị:</strong> Robusta vùng lạnh – khá lạ và ít phổ biến.</p>\r\n</li>\r\n</ul>', 400000.00, 'uploads/products/product_682192b56280c.webp', 2, '2025-05-12 06:18:29', 1, 0, '250', 100),
(35, 'Robusta Honey', '<ul data-start=\"1510\" data-end=\"1740\"><li data-start=\"1510\" data-end=\"1594\" class=\"\"><p data-start=\"1512\" data-end=\"1594\" class=\"\"><strong data-start=\"1512\" data-end=\"1526\">Nguồn gốc:</strong> Robusta Việt Nam, được chế biến theo phương pháp <strong data-start=\"1576\" data-end=\"1593\">Honey Process</strong>.</p>\r\n</li>\r\n<li data-start=\"1595\" data-end=\"1643\" class=\"\">\r\n<p data-start=\"1597\" data-end=\"1643\" class=\"\"><strong data-start=\"1597\" data-end=\"1610\">Hương vị:</strong> Đậm, ngọt hậu tự nhiên, ít đắng.</p>\r\n</li>\r\n<li data-start=\"1644\" data-end=\"1740\" class=\"\">\r\n<p data-start=\"1646\" data-end=\"1740\" class=\"\"><strong data-start=\"1646\" data-end=\"1659\">Đặc điểm:</strong> Chế biến giữ lại phần chất nhầy (mucilage) trên hạt → tăng hương thơm &amp; vị ngọt.</p>\r\n</li>\r\n</ul>', 300000.00, 'uploads/products/product_682192e06c915.jpg', 2, '2025-05-12 06:19:12', 1, 0, '250', 100),
(36, 'Typica', '<li data-start=\"183\" data-end=\"235\" class=\"\"><p data-start=\"185\" data-end=\"235\" class=\"\"><strong data-start=\"185\" data-end=\"199\">Nguồn gốc:</strong> Ethiopia → Yemen → các nước châu Mỹ</p>\r\n</li><li data-start=\"236\" data-end=\"274\" class=\"\">\r\n<p data-start=\"238\" data-end=\"274\" class=\"\"><strong data-start=\"238\" data-end=\"251\">Hương vị:</strong> Dịu, ngọt, hậu vị sạch</p>\r\n</li><li data-start=\"275\" data-end=\"325\" class=\"\">\r\n<p data-start=\"277\" data-end=\"325\" class=\"\"><strong data-start=\"277\" data-end=\"291\">Nơi trồng:</strong> Trung và Nam Mỹ, Đông Phi, châu Á</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"326\" data-end=\"392\" class=\"\">\r\n<p data-start=\"328\" data-end=\"392\" class=\"\"><strong data-start=\"328\" data-end=\"340\">Ghi chú:</strong> Là giống mẹ của hầu hết các giống Arabica hiện nay.</p></li>', 200000.00, 'uploads/products/product_6821955ca0af3.webp', 1, '2025-05-12 06:29:48', 1, 0, '250', 100),
(37, 'Bourbon', '<li data-start=\"418\" data-end=\"493\" class=\"\"><p data-start=\"420\" data-end=\"493\" class=\"\"><strong data-start=\"420\" data-end=\"434\">Nguồn gốc:</strong> Biến thể đột biến của Typica (đảo Bourbon, nay là Réunion)</p>\r\n</li><li data-start=\"494\" data-end=\"538\" class=\"\">\r\n<p data-start=\"496\" data-end=\"538\" class=\"\"><strong data-start=\"496\" data-end=\"509\">Hương vị:</strong> Ngọt ngào, chua nhẹ, tròn vị</p>\r\n</li><li data-start=\"539\" data-end=\"579\" class=\"\">\r\n<p data-start=\"541\" data-end=\"579\" class=\"\"><strong data-start=\"541\" data-end=\"555\">Nơi trồng:</strong> Rwanda, Burundi, Brazil</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"580\" data-end=\"623\" class=\"\">\r\n<p data-start=\"582\" data-end=\"623\" class=\"\"><strong data-start=\"582\" data-end=\"594\">Ghi chú:</strong> Có năng suất cao hơn Typica.</p></li>', 300000.00, 'uploads/products/product_68219596c2a5d.jpg', 1, '2025-05-12 06:30:46', 1, 0, '250', 100),
(38, 'SL28', '<li data-start=\"646\" data-end=\"696\" class=\"\"><p data-start=\"648\" data-end=\"696\" class=\"\"><strong data-start=\"648\" data-end=\"662\">Nguồn gốc:</strong> Kenya (chọn lọc từ giống Bourbon)</p>\r\n</li><li data-start=\"697\" data-end=\"748\" class=\"\">\r\n<p data-start=\"699\" data-end=\"748\" class=\"\"><strong data-start=\"699\" data-end=\"712\">Hương vị:</strong> Trái cây đỏ, acid cao, rất phức tạp</p>\r\n</li><li data-start=\"749\" data-end=\"781\" class=\"\">\r\n<p data-start=\"751\" data-end=\"781\" class=\"\"><strong data-start=\"751\" data-end=\"765\">Nơi trồng:</strong> Kenya, Tanzania</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"782\" data-end=\"839\" class=\"\">\r\n<p data-start=\"784\" data-end=\"839\" class=\"\"><strong data-start=\"784\" data-end=\"796\">Ghi chú:</strong> Rất được ưa chuộng trong Specialty Coffee.</p></li>', 300000.00, 'uploads/products/product_682196070202f.jpg', 1, '2025-05-12 06:32:39', 1, 0, '250', 100),
(39, 'SL34', '<li data-start=\"862\" data-end=\"884\" class=\"\"><p data-start=\"864\" data-end=\"884\" class=\"\"><strong data-start=\"864\" data-end=\"878\">Nguồn gốc:</strong> Kenya</p>\r\n</li><li data-start=\"885\" data-end=\"924\" class=\"\">\r\n<p data-start=\"887\" data-end=\"924\" class=\"\"><strong data-start=\"887\" data-end=\"900\">Hương vị:</strong> Đầy đặn, ngọt, chua nhẹ</p>\r\n</li><li data-start=\"925\" data-end=\"947\" class=\"\">\r\n<p data-start=\"927\" data-end=\"947\" class=\"\"><strong data-start=\"927\" data-end=\"941\">Nơi trồng:</strong> Kenya</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"948\" data-end=\"987\" class=\"\">\r\n<p data-start=\"950\" data-end=\"987\" class=\"\"><strong data-start=\"950\" data-end=\"962\">Ghi chú:</strong> Kháng bệnh tốt hơn SL28.</p></li>', 350000.00, 'uploads/products/product_6821964fd88b5.webp', 1, '2025-05-12 06:33:51', 1, 0, '250', 100),
(40, 'Gesha', '<li data-start=\"1020\" data-end=\"1079\" class=\"\"><p data-start=\"1022\" data-end=\"1079\" class=\"\"><strong data-start=\"1022\" data-end=\"1036\">Nguồn gốc:</strong> Ethiopia (vùng Gesha), phát triển ở Panama</p>\r\n</li><li data-start=\"1080\" data-end=\"1131\" class=\"\">\r\n<p data-start=\"1082\" data-end=\"1131\" class=\"\"><strong data-start=\"1082\" data-end=\"1095\">Hương vị:</strong> Hoa nhài, cam quýt, cực kỳ phức tạp</p>\r\n</li><li data-start=\"1132\" data-end=\"1175\" class=\"\">\r\n<p data-start=\"1134\" data-end=\"1175\" class=\"\"><strong data-start=\"1134\" data-end=\"1148\">Nơi trồng:</strong> Panama, Colombia, Ethiopia</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"1176\" data-end=\"1230\" class=\"\">\r\n<p data-start=\"1178\" data-end=\"1230\" class=\"\"><strong data-start=\"1178\" data-end=\"1190\">Ghi chú:</strong> Một trong những loại đắt nhất thế giới.</p></li>', 400000.00, 'uploads/products/product_682196bf88b90.jpg', 1, '2025-05-12 06:35:43', 1, 0, '300', 100),
(41, 'Caturra', '<li data-start=\"1256\" data-end=\"1310\" class=\"\"><p data-start=\"1258\" data-end=\"1310\" class=\"\"><strong data-start=\"1258\" data-end=\"1272\">Nguồn gốc:</strong> Đột biến tự nhiên từ Bourbon (Brazil)</p>\r\n</li><li data-start=\"1311\" data-end=\"1353\" class=\"\">\r\n<p data-start=\"1313\" data-end=\"1353\" class=\"\"><strong data-start=\"1313\" data-end=\"1326\">Hương vị:</strong> Dịu, mượt, acid trung bình</p>\r\n</li><li data-start=\"1354\" data-end=\"1387\" class=\"\">\r\n<p data-start=\"1356\" data-end=\"1387\" class=\"\"><strong data-start=\"1356\" data-end=\"1370\">Nơi trồng:</strong> Brazil, Colombia</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"1388\" data-end=\"1426\" class=\"\">\r\n<p data-start=\"1390\" data-end=\"1426\" class=\"\"><strong data-start=\"1390\" data-end=\"1402\">Ghi chú:</strong> Cây thấp, dễ thu hoạch</p></li>', 30000.00, 'uploads/products/product_682197aec360e.jpg', 1, '2025-05-12 06:39:42', 1, 0, '250', 100),
(42, 'Catuai', '<li data-start=\"1451\" data-end=\"1498\" class=\"\"><p data-start=\"1453\" data-end=\"1498\" class=\"\"><strong data-start=\"1453\" data-end=\"1467\">Nguồn gốc:</strong> Lai giữa Mundo Novo và Caturra</p>\r\n</li><li data-start=\"1499\" data-end=\"1535\" class=\"\">\r\n<p data-start=\"1501\" data-end=\"1535\" class=\"\"><strong data-start=\"1501\" data-end=\"1514\">Hương vị:</strong> Trung tính, cân bằng</p>\r\n</li><li data-start=\"1536\" data-end=\"1569\" class=\"\">\r\n<p data-start=\"1538\" data-end=\"1569\" class=\"\"><strong data-start=\"1538\" data-end=\"1552\">Nơi trồng:</strong> Brazil, Trung Mỹ</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"1570\" data-end=\"1606\" class=\"\">\r\n<p data-start=\"1572\" data-end=\"1606\" class=\"\"><strong data-start=\"1572\" data-end=\"1584\">Ghi chú:</strong> Chịu được gió và mưa.</p></li>', 250000.00, 'uploads/products/product_682197ee9a7a8.jpg', 1, '2025-05-12 06:40:46', 1, 0, '250', 100),
(43, 'Pacamara', '<li data-start=\"1633\" data-end=\"1678\" class=\"\"><p data-start=\"1635\" data-end=\"1678\" class=\"\"><strong data-start=\"1635\" data-end=\"1649\">Nguồn gốc:</strong> Lai giữa Pacas và Maragogipe</p>\r\n</li><li data-start=\"1679\" data-end=\"1719\" class=\"\">\r\n<p data-start=\"1681\" data-end=\"1719\" class=\"\"><strong data-start=\"1681\" data-end=\"1694\">Hương vị:</strong> Đậm, phức tạp, acid sáng</p>\r\n</li><li data-start=\"1720\" data-end=\"1759\" class=\"\">\r\n<p data-start=\"1722\" data-end=\"1759\" class=\"\"><strong data-start=\"1722\" data-end=\"1736\">Nơi trồng:</strong> El Salvador, Nicaragua</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"1760\" data-end=\"1786\" class=\"\">\r\n<p data-start=\"1762\" data-end=\"1786\" class=\"\"><strong data-start=\"1762\" data-end=\"1774\">Ghi chú:</strong> Hạt rất to.</p></li>', 250000.00, 'uploads/products/product_68219d0cd956c.webp', 1, '2025-05-12 07:02:36', 1, 0, '250', 100),
(44, 'Maragogipe', '<ul data-start=\"1815\" data-end=\"1981\"><li data-start=\"1815\" data-end=\"1859\" class=\"\"><p data-start=\"1817\" data-end=\"1859\" class=\"\"><strong data-start=\"1817\" data-end=\"1831\">Nguồn gốc:</strong> Đột biến từ Typica (Brazil)</p>\r\n</li>\r\n<li data-start=\"1860\" data-end=\"1894\" class=\"\">\r\n<p data-start=\"1862\" data-end=\"1894\" class=\"\"><strong data-start=\"1862\" data-end=\"1875\">Hương vị:</strong> Dịu nhẹ, body thấp</p>\r\n</li>\r\n<li data-start=\"1895\" data-end=\"1927\" class=\"\">\r\n<p data-start=\"1897\" data-end=\"1927\" class=\"\"><strong data-start=\"1897\" data-end=\"1911\">Nơi trồng:</strong> Trung và Nam Mỹ</p>\r\n</li>\r\n<li data-start=\"1928\" data-end=\"1981\" class=\"\">\r\n<p data-start=\"1930\" data-end=\"1981\" class=\"\"><strong data-start=\"1930\" data-end=\"1942\">Ghi chú:</strong> Hạt cực kỳ to, gọi là “Elephant Bean”.</p>\r\n</li>\r\n</ul>', 350000.00, 'uploads/products/product_68219d6e2d5cf.jpg', 1, '2025-05-12 07:04:14', 1, 0, '300', 100),
(45, 'Pacas', '<li data-start=\"2006\" data-end=\"2056\" class=\"\"><p data-start=\"2008\" data-end=\"2056\" class=\"\"><strong data-start=\"2008\" data-end=\"2022\">Nguồn gốc:</strong> Đột biến từ Bourbon (El Salvador)</p>\r\n</li><li data-start=\"2057\" data-end=\"2094\" class=\"\">\r\n<p data-start=\"2059\" data-end=\"2094\" class=\"\"><strong data-start=\"2059\" data-end=\"2072\">Hương vị:</strong> Cân bằng, hậu vị sạch</p>\r\n</li><li data-start=\"2095\" data-end=\"2123\" class=\"\">\r\n<p data-start=\"2097\" data-end=\"2123\" class=\"\"><strong data-start=\"2097\" data-end=\"2111\">Nơi trồng:</strong> El Salvador</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"2124\" data-end=\"2164\" class=\"\">\r\n<p data-start=\"2126\" data-end=\"2164\" class=\"\"><strong data-start=\"2126\" data-end=\"2138\">Ghi chú:</strong> Là mẹ của giống Pacamara.</p></li>', 200000.00, 'uploads/products/product_68219da3ee85b.jpg', 1, '2025-05-12 07:05:07', 1, 0, '250', 100),
(46, 'Mundo Novo', '<li data-start=\"2194\" data-end=\"2237\" class=\"\"><p data-start=\"2196\" data-end=\"2237\" class=\"\"><strong data-start=\"2196\" data-end=\"2210\">Nguồn gốc:</strong> Lai giữa Typica và Bourbon</p>\r\n</li><li data-start=\"2238\" data-end=\"2274\" class=\"\">\r\n<p data-start=\"2240\" data-end=\"2274\" class=\"\"><strong data-start=\"2240\" data-end=\"2253\">Hương vị:</strong> Ngọt dịu, hậu vị dài</p>\r\n</li><li data-start=\"2275\" data-end=\"2304\" class=\"\">\r\n<p data-start=\"2277\" data-end=\"2304\" class=\"\"><strong data-start=\"2277\" data-end=\"2291\">Nơi trồng:</strong> Brazil, Peru</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"2305\" data-end=\"2350\" class=\"\">\r\n<p data-start=\"2307\" data-end=\"2350\" class=\"\"><strong data-start=\"2307\" data-end=\"2319\">Ghi chú:</strong> Năng suất cao, kháng bệnh tốt.</p></li>', 150000.00, 'uploads/products/product_68219e2c3906b.webp', 1, '2025-05-12 07:07:24', 1, 0, '300', 100),
(47, 'Villa Sarchi', '<li data-start=\"2382\" data-end=\"2440\" class=\"\"><p data-start=\"2384\" data-end=\"2440\" class=\"\"><strong data-start=\"2384\" data-end=\"2398\">Nguồn gốc:</strong> Đột biến tự nhiên từ Bourbon (Costa Rica)</p>\r\n</li><li data-start=\"2441\" data-end=\"2478\" class=\"\">\r\n<p data-start=\"2443\" data-end=\"2478\" class=\"\"><strong data-start=\"2443\" data-end=\"2456\">Hương vị:</strong> Acid cao, vị trái cây</p>\r\n</li><li data-start=\"2479\" data-end=\"2506\" class=\"\">\r\n<p data-start=\"2481\" data-end=\"2506\" class=\"\"><strong data-start=\"2481\" data-end=\"2495\">Nơi trồng:</strong> Costa Rica</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"2507\" data-end=\"2552\" class=\"\">\r\n<p data-start=\"2509\" data-end=\"2552\" class=\"\"><strong data-start=\"2509\" data-end=\"2521\">Ghi chú:</strong> Dùng để lai tạo các giống mới.</p></li>', 300000.00, 'uploads/products/product_68219e68e4d34.webp', 1, '2025-05-12 07:08:24', 1, 0, '250', 100),
(48, 'Ruiru 11', '<li data-start=\"2580\" data-end=\"2640\" class=\"\"><p data-start=\"2582\" data-end=\"2640\" class=\"\"><strong data-start=\"2582\" data-end=\"2596\">Nguồn gốc:</strong> Kenya (lai nhiều giống, bao gồm SL28, SL34)</p>\r\n</li><li data-start=\"2641\" data-end=\"2687\" class=\"\">\r\n<p data-start=\"2643\" data-end=\"2687\" class=\"\"><strong data-start=\"2643\" data-end=\"2656\">Hương vị:</strong> Cân bằng, ít phức tạp hơn SL28</p>\r\n</li><li data-start=\"2688\" data-end=\"2710\" class=\"\">\r\n<p data-start=\"2690\" data-end=\"2710\" class=\"\"><strong data-start=\"2690\" data-end=\"2704\">Nơi trồng:</strong> Kenya</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"2711\" data-end=\"2759\" class=\"\">\r\n<p data-start=\"2713\" data-end=\"2759\" class=\"\"><strong data-start=\"2713\" data-end=\"2725\">Ghi chú:</strong> Kháng bệnh gỉ sắt, năng suất cao.</p></li>', 350000.00, 'uploads/products/product_68219ecb5b787.jpg', 1, '2025-05-12 07:10:03', 1, 0, '250', 100),
(49, 'Castillo', '<ul data-start=\"2787\" data-end=\"2969\"><li data-start=\"2787\" data-end=\"2849\" class=\"\"><p data-start=\"2789\" data-end=\"2849\" class=\"\"><strong data-start=\"2789\" data-end=\"2803\">Nguồn gốc:</strong> Colombia (lai từ Caturra và giống kháng bệnh)</p>\r\n</li>\r\n<li data-start=\"2850\" data-end=\"2889\" class=\"\">\r\n<p data-start=\"2852\" data-end=\"2889\" class=\"\"><strong data-start=\"2852\" data-end=\"2865\">Hương vị:</strong> Cân bằng, không nổi bật</p>\r\n</li>\r\n<li data-start=\"2890\" data-end=\"2915\" class=\"\">\r\n<p data-start=\"2892\" data-end=\"2915\" class=\"\"><strong data-start=\"2892\" data-end=\"2906\">Nơi trồng:</strong> Colombia</p>\r\n</li>\r\n<li data-start=\"2916\" data-end=\"2969\" class=\"\">\r\n<p data-start=\"2918\" data-end=\"2969\" class=\"\"><strong data-start=\"2918\" data-end=\"2930\">Ghi chú:</strong> Thay thế Caturra do kháng bệnh gỉ sắt.</p>\r\n</li>\r\n</ul>', 200000.00, 'uploads/products/product_68219f18c786c.webp', 1, '2025-05-12 07:11:20', 1, 0, '250', 100),
(50, 'Ethiopia Heirloom (Di sản Ethiopia)', '<ul data-start=\"3024\" data-end=\"3184\"><li data-start=\"3024\" data-end=\"3049\" class=\"\"><p data-start=\"3026\" data-end=\"3049\" class=\"\"><strong data-start=\"3026\" data-end=\"3040\">Nguồn gốc:</strong> Ethiopia</p>\r\n</li>\r\n<li data-start=\"3050\" data-end=\"3084\" class=\"\">\r\n<p data-start=\"3052\" data-end=\"3084\" class=\"\"><strong data-start=\"3052\" data-end=\"3065\">Hương vị:</strong> Hoa, cam quýt, trà</p>\r\n</li>\r\n<li data-start=\"3085\" data-end=\"3110\" class=\"\">\r\n<p data-start=\"3087\" data-end=\"3110\" class=\"\"><strong data-start=\"3087\" data-end=\"3101\">Nơi trồng:</strong> Ethiopia</p>\r\n</li>\r\n<li data-start=\"3111\" data-end=\"3184\" class=\"\">\r\n<p data-start=\"3113\" data-end=\"3184\" class=\"\"><strong data-start=\"3113\" data-end=\"3125\">Ghi chú:</strong> Là tập hợp hàng trăm giống bản địa chưa được phân loại kỹ.</p>\r\n</li>\r\n</ul>', 300000.00, 'uploads/products/product_68219f4b432ea.webp', 1, '2025-05-12 07:12:11', 1, 0, '250', 100),
(51, 'Blue Mountain', '<li data-start=\"3217\" data-end=\"3250\" class=\"\"><p data-start=\"3219\" data-end=\"3250\" class=\"\"><strong data-start=\"3219\" data-end=\"3233\">Nguồn gốc:</strong> Typica (Jamaica)</p>\r\n</li><li data-start=\"3251\" data-end=\"3296\" class=\"\">\r\n<p data-start=\"3253\" data-end=\"3296\" class=\"\"><strong data-start=\"3253\" data-end=\"3266\">Hương vị:</strong> Dịu nhẹ, ít đắng, hậu vị sạch</p>\r\n</li><li data-start=\"3297\" data-end=\"3331\" class=\"\">\r\n<p data-start=\"3299\" data-end=\"3331\" class=\"\"><strong data-start=\"3299\" data-end=\"3313\">Nơi trồng:</strong> Núi Blue, Jamaica</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"3332\" data-end=\"3376\" class=\"\">\r\n<p data-start=\"3334\" data-end=\"3376\" class=\"\"><strong data-start=\"3334\" data-end=\"3346\">Ghi chú:</strong> Cực kỳ nổi tiếng và đắt tiền.</p></li>', 250000.00, 'uploads/products/product_68219f7ddcfbd.jpg', 1, '2025-05-12 07:13:01', 1, 0, '250', 100),
(52, 'Cà phê chồn Indonesia – Kopi Luwak', '<li data-start=\"530\" data-end=\"559\" class=\"\"><p data-start=\"532\" data-end=\"559\" class=\"\"><strong data-start=\"532\" data-end=\"542\">Giống:</strong> Arabica, Robusta</p>\r\n</li><li data-start=\"563\" data-end=\"607\" class=\"\">\r\n<p data-start=\"565\" data-end=\"607\" class=\"\"><strong data-start=\"565\" data-end=\"578\">Đặc điểm:</strong> Vị nhẹ, ít đắng, hậu vị sạch</p>\r\n</li><li data-start=\"611\" data-end=\"648\" class=\"\">\r\n<p data-start=\"613\" data-end=\"648\" class=\"\"><strong data-start=\"613\" data-end=\"627\">Nguồn gốc:</strong> Chồn hoang/nuôi nhốt</p>\r\n</li><p>\r\n\r\n\r\n</p><li data-start=\"652\" data-end=\"690\" class=\"\">\r\n<p data-start=\"654\" data-end=\"690\" class=\"\"><strong data-start=\"654\" data-end=\"666\">Ghi chú:</strong> Nổi tiếng nhất thế giới</p></li>', 400000.00, 'uploads/products/product_6821d721df0ee.webp', 3, '2025-05-12 11:10:25', 1, 0, '250', 0),
(53, 'Cà phê chồn Bali – Kopi Luwak Bali', '<li data-start=\"737\" data-end=\"757\" class=\"\"><p data-start=\"739\" data-end=\"757\" class=\"\"><strong data-start=\"739\" data-end=\"749\">Giống:</strong> Arabica</p>\r\n</li><li data-start=\"761\" data-end=\"798\" class=\"\">\r\n<p data-start=\"763\" data-end=\"798\" class=\"\"><strong data-start=\"763\" data-end=\"776\">Đặc điểm:</strong> Thơm, nhẹ, hậu vị dài</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"802\" data-end=\"843\" class=\"\">\r\n<p data-start=\"804\" data-end=\"843\" class=\"\"><strong data-start=\"804\" data-end=\"816\">Ghi chú:</strong> Tập trung ở vùng Kintamani</p></li>', 450000.00, 'uploads/products/product_6821d75c9e1fa.jpg', 3, '2025-05-12 11:11:24', 1, 0, '250', 100),
(54, 'Cà phê chồn Sumatra', '<li data-start=\"845\" data-end=\"991\" class=\"\"><ul data-start=\"875\" data-end=\"991\"><li data-start=\"875\" data-end=\"908\" class=\"\"><p data-start=\"877\" data-end=\"908\" class=\"\"><strong data-start=\"877\" data-end=\"887\">Giống:</strong> Mandheling (Arabica)</p>\r\n</li>\r\n<li data-start=\"912\" data-end=\"953\" class=\"\">\r\n<p data-start=\"914\" data-end=\"953\" class=\"\"><strong data-start=\"914\" data-end=\"927\">Đặc điểm:</strong> Earthy, đậm vị, thấp acid</p>\r\n</li>\r\n<li data-start=\"957\" data-end=\"991\" class=\"\">\r\n<p data-start=\"959\" data-end=\"991\" class=\"\"><strong data-start=\"959\" data-end=\"969\">Nguồn:</strong> Tự nhiên và nuôi nhốt</p>\r\n</li>\r\n</ul>\r\n</li><p>\r\n</p><li data-start=\"993\" data-end=\"1163\" class=\"\">\r\n<p data-start=\"996\" data-end=\"1016\" class=\"\"></p></li>', 350000.00, 'uploads/products/product_6821d7a32902f.jpg', 3, '2025-05-12 11:12:35', 1, 0, '250', 100),
(55, 'Cà phê chồn Java', '<li data-start=\"1020\" data-end=\"1054\" class=\"\"><p data-start=\"1022\" data-end=\"1054\" class=\"\"><strong data-start=\"1022\" data-end=\"1032\">Giống:</strong> Java Typica (Arabica)</p>\r\n</li><li data-start=\"1058\" data-end=\"1109\" class=\"\">\r\n<p data-start=\"1060\" data-end=\"1109\" class=\"\"><strong data-start=\"1060\" data-end=\"1073\">Đặc điểm:</strong> Ngọt dịu, chua nhẹ, body trung bình</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"1113\" data-end=\"1163\" class=\"\">\r\n<p data-start=\"1115\" data-end=\"1163\" class=\"\"><strong data-start=\"1115\" data-end=\"1127\">Ghi chú:</strong> Một trong những loại cà phê lâu đời</p></li>', 500000.00, 'uploads/products/product_6821d8103c92f.jpg', 3, '2025-05-12 11:14:24', 1, 0, '250', 100),
(56, 'Cà phê chồn Philippines – Kape Alamid', '<li data-start=\"1213\" data-end=\"1251\" class=\"\"><p data-start=\"1215\" data-end=\"1251\" class=\"\"><strong data-start=\"1215\" data-end=\"1225\">Giống:</strong> Arabica, Robusta, Excelsa</p>\r\n</li><li data-start=\"1255\" data-end=\"1297\" class=\"\">\r\n<p data-start=\"1257\" data-end=\"1297\" class=\"\"><strong data-start=\"1257\" data-end=\"1270\">Đặc điểm:</strong> Ngọt, ít đắng, hương cacao</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"1301\" data-end=\"1331\" class=\"\">\r\n<p data-start=\"1303\" data-end=\"1331\" class=\"\"><strong data-start=\"1303\" data-end=\"1317\">Nguồn gốc:</strong> Chồn hoang dã</p></li>', 400000.00, 'uploads/products/product_6821d855b6239.jpg', 3, '2025-05-12 11:15:33', 1, 0, '250', 100),
(57, 'Cà phê chồn Việt Nam – Cà phê chồn Tây Nguyên', '<li data-start=\"1333\" data-end=\"1523\" class=\"\"><ul data-start=\"1389\" data-end=\"1523\"><li data-start=\"1389\" data-end=\"1439\" class=\"\"><p data-start=\"1391\" data-end=\"1439\" class=\"\"><strong data-start=\"1391\" data-end=\"1401\">Giống:</strong> Arabica (Lâm Đồng), Robusta (Đắk Lắk)</p>\r\n</li>\r\n<li data-start=\"1443\" data-end=\"1486\" class=\"\">\r\n<p data-start=\"1445\" data-end=\"1486\" class=\"\"><strong data-start=\"1445\" data-end=\"1458\">Đặc điểm:</strong> Hương thơm quyến rũ, đậm đà</p>\r\n</li>\r\n<li data-start=\"1490\" data-end=\"1523\" class=\"\">\r\n<p data-start=\"1492\" data-end=\"1523\" class=\"\"><strong data-start=\"1492\" data-end=\"1502\">Nguồn:</strong> Chủ yếu từ chồn nuôi</p>\r\n</li>\r\n</ul>\r\n</li><p>\r\n</p><li data-start=\"1525\" data-end=\"1694\" class=\"\">\r\n<p data-start=\"1528\" data-end=\"1563\" class=\"\"></p></li>', 350000.00, 'uploads/products/product_6821d8e74fd6d.webp', 3, '2025-05-12 11:17:59', 1, 0, '250', 100),
(58, 'Cà phê chồn Lâm Đồng (Việt Nam)', '<li data-start=\"1525\" data-end=\"1694\" class=\"\"><ul data-start=\"1567\" data-end=\"1694\"><li data-start=\"1567\" data-end=\"1595\" class=\"\"><p data-start=\"1569\" data-end=\"1595\" class=\"\"><strong data-start=\"1569\" data-end=\"1579\">Giống:</strong> Arabica Bourbon</p>\r\n</li>\r\n<li data-start=\"1599\" data-end=\"1640\" class=\"\">\r\n<p data-start=\"1601\" data-end=\"1640\" class=\"\"><strong data-start=\"1601\" data-end=\"1614\">Đặc điểm:</strong> Thanh, acid nhẹ, hậu ngọt</p>\r\n</li>\r\n<li data-start=\"1644\" data-end=\"1694\" class=\"\">\r\n<p data-start=\"1646\" data-end=\"1694\" class=\"\"><strong data-start=\"1646\" data-end=\"1658\">Ghi chú:</strong> Nổi bật nhờ khí hậu cao nguyên lạnh</p>\r\n</li>\r\n</ul>\r\n</li><p>\r\n</p><li data-start=\"1696\" data-end=\"1811\" class=\"\">\r\n<p data-start=\"1699\" data-end=\"1739\" class=\"\"></p></li>', 300000.00, 'uploads/products/product_6821d97a0930e.jpg', 3, '2025-05-12 11:20:26', 1, 0, '250', 100),
(59, 'Cà phê chồn Buôn Ma Thuột (Việt Nam)', '<li data-start=\"1696\" data-end=\"1811\" class=\"\"><ul data-start=\"1743\" data-end=\"1811\"><li data-start=\"1743\" data-end=\"1763\" class=\"\"><p data-start=\"1745\" data-end=\"1763\" class=\"\"><strong data-start=\"1745\" data-end=\"1755\">Giống:</strong> Robusta</p>\r\n</li>\r\n<li data-start=\"1767\" data-end=\"1811\" class=\"\">\r\n<p data-start=\"1769\" data-end=\"1811\" class=\"\"><strong data-start=\"1769\" data-end=\"1782\">Đặc điểm:</strong> Đậm, nhiều caffeine, ít acid</p>\r\n</li>\r\n</ul>\r\n</li><p>\r\n</p><li data-start=\"1813\" data-end=\"1966\" class=\"\">\r\n<p data-start=\"1816\" data-end=\"1847\" class=\"\"></p></li>', 300000.00, 'uploads/products/product_6821d9a2eee39.jpg', 3, '2025-05-12 11:21:06', 1, 0, '250', 100),
(60, 'Cà phê chồn Ethiopia', '<li data-start=\"1851\" data-end=\"1872\" class=\"\"><p data-start=\"1853\" data-end=\"1872\" class=\"\"><strong data-start=\"1853\" data-end=\"1863\">Giống:</strong> Heirloom</p>\r\n</li><li data-start=\"1876\" data-end=\"1920\" class=\"\">\r\n<p data-start=\"1878\" data-end=\"1920\" class=\"\"><strong data-start=\"1878\" data-end=\"1891\">Đặc điểm:</strong> Hương hoa, chua cao, hậu trà</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"1924\" data-end=\"1966\" class=\"\">\r\n<p data-start=\"1926\" data-end=\"1966\" class=\"\"><strong data-start=\"1926\" data-end=\"1938\">Ghi chú:</strong> Hiếm, do chồn sống hoang dã</p></li>', 400000.00, 'uploads/products/product_6821da0d6cdf8.jpg', 3, '2025-05-12 11:22:53', 1, 0, '250', 100),
(61, 'Cà phê chồn Ấn Độ – Indian Civet Coffee', '<li data-start=\"2020\" data-end=\"2049\" class=\"\"><p data-start=\"2022\" data-end=\"2049\" class=\"\"><strong data-start=\"2022\" data-end=\"2032\">Giống:</strong> Arabica, Robusta</p>\r\n</li><li data-start=\"2054\" data-end=\"2098\" class=\"\">\r\n<p data-start=\"2056\" data-end=\"2098\" class=\"\"><strong data-start=\"2056\" data-end=\"2069\">Đặc điểm:</strong> Tròn vị, hương đất, body đậm</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"2103\" data-end=\"2144\" class=\"\">\r\n<p data-start=\"2105\" data-end=\"2144\" class=\"\"><strong data-start=\"2105\" data-end=\"2117\">Ghi chú:</strong> Bắt đầu được chú ý gần đây</p></li>', 450000.00, 'uploads/products/product_6821da583fee0.jpg', 3, '2025-05-12 11:24:08', 1, 0, '250', 100),
(62, 'Cà phê chồn hoang dã (Wild Civet Coffee)', '<li data-start=\"2246\" data-end=\"2277\" class=\"\"><p data-start=\"2248\" data-end=\"2277\" class=\"\"><strong data-start=\"2248\" data-end=\"2258\">Nguồn:</strong> Chồn sống tự nhiên</p>\r\n</li><li data-start=\"2282\" data-end=\"2323\" class=\"\">\r\n<p data-start=\"2284\" data-end=\"2323\" class=\"\"><strong data-start=\"2284\" data-end=\"2299\">Chất lượng:</strong> Rất cao, sản lượng thấp</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"2328\" data-end=\"2349\" class=\"\">\r\n<p data-start=\"2330\" data-end=\"2349\" class=\"\"><strong data-start=\"2330\" data-end=\"2338\">Giá:</strong> Rất đắt đỏ</p></li>', 550000.00, 'uploads/products/product_682203e351868.jpg', 3, '2025-05-12 14:21:23', 1, 0, '250', 100),
(63, 'Cà phê chồn nuôi (Farmed Civet Coffee)', '<li data-start=\"2402\" data-end=\"2437\" class=\"\"><p data-start=\"2404\" data-end=\"2437\" class=\"\"><strong data-start=\"2404\" data-end=\"2414\">Nguồn:</strong> Chồn nuôi trong chuồng</p>\r\n</li><li data-start=\"2442\" data-end=\"2491\" class=\"\">\r\n<p data-start=\"2444\" data-end=\"2491\" class=\"\"><strong data-start=\"2444\" data-end=\"2459\">Chất lượng:</strong> Thấp hơn, gây tranh cãi đạo đức</p>\r\n</li><p>\r\n\r\n</p><li data-start=\"2496\" data-end=\"2527\" class=\"\">\r\n<p data-start=\"2498\" data-end=\"2527\" class=\"\"><strong data-start=\"2498\" data-end=\"2506\">Giá:</strong> Phổ biến hơn, rẻ hơn</p></li>', 300000.00, 'uploads/products/product_6822042aeffd6.jpg', 3, '2025-05-12 14:22:34', 1, 0, '250', 100),
(64, 'Cà phê chồn bán hoang dã', '<li data-start=\"2529\" data-end=\"2700\" class=\"\"><ul data-start=\"2566\" data-end=\"2700\"><li data-start=\"2566\" data-end=\"2615\" class=\"\"><p data-start=\"2568\" data-end=\"2615\" class=\"\"><strong data-start=\"2568\" data-end=\"2578\">Nguồn:</strong> Chồn bán tự nhiên, kiểm soát thức ăn</p>\r\n</li>\r\n<li data-start=\"2620\" data-end=\"2652\" class=\"\">\r\n<p data-start=\"2622\" data-end=\"2652\" class=\"\"><strong data-start=\"2622\" data-end=\"2637\">Chất lượng:</strong> Trung bình cao</p>\r\n</li>\r\n<li data-start=\"2657\" data-end=\"2700\" class=\"\">\r\n<p data-start=\"2659\" data-end=\"2700\" class=\"\"><strong data-start=\"2659\" data-end=\"2671\">Ghi chú:</strong> Mô hình thay thế đạo đức hơn</p>\r\n</li>\r\n</ul>\r\n</li><p>\r\n</p><li data-start=\"2702\" data-end=\"2885\" class=\"\">\r\n<p data-start=\"2706\" data-end=\"2764\" class=\"\"></p></li>', 300000.00, 'uploads/products/product_68220488affcc.jpg', 3, '2025-05-12 14:24:08', 1, 0, '250', 100),
(65, 'Espresso', '<ul data-start=\"180\" data-end=\"289\"><li data-start=\"180\" data-end=\"228\" class=\"\"><p data-start=\"182\" data-end=\"228\" class=\"\">Cà phê đậm đặc, pha bằng máy dưới áp suất cao.</p>\r\n</li>\r\n<li data-start=\"229\" data-end=\"289\" class=\"\">\r\n<p data-start=\"231\" data-end=\"289\" class=\"\">Là nền tảng của nhiều loại cà phê khác như Latte, Mocha...</p>\r\n</li>\r\n</ul>', 100000.00, 'uploads/products/product_682204ffacd93.webp', 4, '2025-05-12 14:26:07', 1, 0, '0', 100),
(66, 'Americano', '<li data-start=\"319\" data-end=\"354\" class=\"\"><p data-start=\"321\" data-end=\"354\" class=\"\">Espresso pha loãng với nước nóng.</p>\r\n</li><p>\r\n</p><li data-start=\"355\" data-end=\"407\" class=\"\">\r\n<p data-start=\"357\" data-end=\"407\" class=\"\">Vị nhẹ hơn Espresso, nhưng vẫn giữ được độ đậm đà.</p></li>', 150000.00, 'uploads/products/product_68220576d9ec5.jpg', 4, '2025-05-12 14:28:06', 1, 0, '250', 100),
(67, 'Latte Vị Dừa', '<li data-start=\"433\" data-end=\"496\" class=\"\"><p data-start=\"435\" data-end=\"496\" class=\"\">1 phần Espresso + 3 phần sữa hấp nóng + một lớp bọt sữa mỏng.</p>\r\n</li><p>\r\n</p><li data-start=\"497\" data-end=\"535\" class=\"\">\r\n<p data-start=\"499\" data-end=\"535\" class=\"\">Vị béo, nhẹ, dễ uống – rất phổ biến.</p></li>', 130000.00, 'uploads/products/product_682205daa2783.jpg', 4, '2025-05-12 14:29:46', 1, 0, '250', 100),
(68, 'Macchiato', '<li data-start=\"565\" data-end=\"613\" class=\"\"><p data-start=\"567\" data-end=\"613\" class=\"\">Espresso <strong data-start=\"576\" data-end=\"590\">\"đánh dấu\"</strong> bằng một chút bọt sữa.</p>\r\n</li><p>\r\n</p><li data-start=\"614\" data-end=\"646\" class=\"\">\r\n<p data-start=\"616\" data-end=\"646\" class=\"\">Vị mạnh hơn Latte, ít sữa hơn.</p></li>', 150000.00, 'uploads/products/product_68220633a8dba.jpg', 4, '2025-05-12 14:31:15', 1, 0, '250', 100),
(69, 'Mocha', '<ul data-start=\"672\" data-end=\"778\"><li data-start=\"672\" data-end=\"730\" class=\"\"><p data-start=\"674\" data-end=\"730\" class=\"\">Espresso + Sữa + Socola (thường là dạng syrup hoặc bột).</p>\r\n</li>\r\n<li data-start=\"731\" data-end=\"778\" class=\"\">\r\n<p data-start=\"733\" data-end=\"778\" class=\"\">Đậm đà, thơm mùi cà phê hòa quyện với socola.</p>\r\n</li>\r\n</ul>', 100000.00, 'uploads/products/product_6822070129660.webp', 4, '2025-05-12 14:34:41', 1, 0, '250', 100),
(70, 'Egg Coffee (Cà phê trứng – Việt Nam)', '<li data-start=\"1370\" data-end=\"1419\" class=\"\"><p data-start=\"1372\" data-end=\"1419\" class=\"\">Cà phê đậm + lòng đỏ trứng đánh bông + sữa đặc.</p>\r\n</li><p>\r\n</p><li data-start=\"1420\" data-end=\"1472\" class=\"\">\r\n<p data-start=\"1422\" data-end=\"1472\" class=\"\">Béo ngậy, thơm ngọt – độc đáo, nổi tiếng ở Hà Nội.</p></li>', 150000.00, 'uploads/products/product_6822072da64e1.webp', 4, '2025-05-12 14:35:25', 1, 0, '250', 100),
(71, 'Frappe', '<li data-start=\"1220\" data-end=\"1269\" class=\"\"><p data-start=\"1222\" data-end=\"1269\" class=\"\">Cà phê pha cùng đá xay, sữa, kem và hương liệu.</p>\r\n</li><p>\r\n</p><li data-start=\"1270\" data-end=\"1312\" class=\"\">\r\n<p data-start=\"1272\" data-end=\"1312\" class=\"\">Thức uống lạnh, phổ biến trong giới trẻ.</p></li>', 100000.00, 'uploads/products/product_6822078b2e4b4.webp', 4, '2025-05-12 14:36:59', 1, 0, '250', 100),
(72, 'Cold Brew', '<ul data-start=\"1073\" data-end=\"1174\"><li data-start=\"1073\" data-end=\"1124\" class=\"\"><p data-start=\"1075\" data-end=\"1124\" class=\"\">Cà phê ủ lạnh bằng nước thường trong 12–24 tiếng.</p>\r\n</li>\r\n<li data-start=\"1125\" data-end=\"1174\" class=\"\">\r\n<p data-start=\"1127\" data-end=\"1174\" class=\"\">Ít chua, nhẹ hơn cà phê nóng, thường uống lạnh.</p>\r\n</li>\r\n</ul>', 100000.00, 'uploads/products/product_682207b58ea93.png', 4, '2025-05-12 14:37:41', 1, 0, '250', 100),
(73, 'Affogato', '<li data-start=\"932\" data-end=\"996\" class=\"\"><p data-start=\"934\" data-end=\"996\" class=\"\">Món tráng miệng: 1 viên kem vani + rót Espresso nóng lên trên.</p>\r\n</li><p>\r\n</p><li data-start=\"997\" data-end=\"1043\" class=\"\">\r\n<p data-start=\"999\" data-end=\"1043\" class=\"\">Vừa mát lạnh vừa đắng nhẹ – cực kỳ đặc biệt.</p></li>', 100000.00, 'uploads/products/product_682207ea457ac.jpg', 4, '2025-05-12 14:38:34', 1, 0, '250', 100),
(74, 'Flat White', '<ul data-start=\"809\" data-end=\"903\"><li data-start=\"809\" data-end=\"837\" class=\"\"><p data-start=\"811\" data-end=\"837\" class=\"\">Xuất xứ từ Úc/New Zealand.</p>\r\n</li>\r\n<li data-start=\"838\" data-end=\"903\" class=\"\">\r\n<p data-start=\"840\" data-end=\"903\" class=\"\">Giống Latte nhưng dùng ít sữa hơn, tạo cảm giác mịn và đậm hơn.</p>\r\n</li>\r\n</ul>', 100000.00, 'uploads/products/product_68220844c42ef.webp', 4, '2025-05-12 14:40:04', 1, 0, '250', 100);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `fullname`, `phone`, `address`, `city`, `role`, `created_at`, `active`) VALUES
(1, 'admin', '*38AFCAF55503A1679F96CF62072E9E890301BABA', 'admin@example.com', 'Administrator', '', '', '', 'admin', '2025-04-20 17:50:23', 1),
(2, 'Đăng Khoa', '*2599F35A65FBE0337C73FE506BA4C89B137D639E', 'dangkhoanguyenphuc0409@gmail.com', 'Nguyễn Phúc Đăng Khoa', '0865545705', '', '', 'customer', '2025-04-21 17:30:06', 0),
(3, 'hieu_870', '*6BB4837EB74329105EE4568DDA7DC67ED2CA2AD9', 'ahehehihihahahuhu123@gmail.com', 'le hieu', '122123231231', '', '', 'customer', '2025-05-12 21:06:58', 0),
(4, 'mèo con cute hột me', '$2y$10$WPpI3qtI1e.2ngyvXP42LueKGvqy0FHmShr9VHLtiCNJpMYjKxjom', '123@gmail.com', 'mèo', '12345678', 'hcm', 'hcm', 'customer', '2025-05-12 21:40:26', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_details`
--

CREATE TABLE `user_details` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_details`
--

INSERT INTO `user_details` (`user_id`, `email`, `password`, `fullname`, `phone`, `address`, `city`, `created_at`) VALUES
(2, 'dangkhoanguyenphuc0409@gmail.com', '$2y$10$.55E3NgcfabaDS/9duPqyef.9jZbENOlQOfsLtW8WQ8GCUz.vR2MW', 'Nguyễn Phúc Đăng Khoa', '0865545705', '', '', '2025-04-21 17:30:06'),
(3, 'ahehehihihahahuhu123@gmail.com', '$2y$10$ZkhtxtNq/z8fJeBgZkJ4muEKFl0MhLmwqUcAhBZrE4ABN.mvO1x56', 'le hieu', '122123231231', '', '', '2025-05-12 21:06:58');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `user_details`
--
ALTER TABLE `user_details`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
