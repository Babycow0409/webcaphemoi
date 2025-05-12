-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2025 at 01:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: lab1
--

-- --------------------------------------------------------

--
-- Table structure for table addresses
--

CREATE TABLE addresses (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  recipient_name varchar(100) NOT NULL,
  phone varchar(20) NOT NULL,
  province varchar(50) NOT NULL,
  district varchar(50) NOT NULL,
  ward varchar(50) NOT NULL,
  address_detail varchar(255) NOT NULL,
  is_default tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table addresses
--

INSERT INTO addresses (id, user_id, recipient_name, phone, province, district, ward, address_detail, is_default, created_at) VALUES
(1, 2, 'Nguyễn Phúc Đăng Khoa', '0865545705', 'hồ chí minh', 'hồ chí minh', 'hồ chí minh', '135/3a tân kì tân quý phường tân sơn nhì', 1, '2025-05-11 10:41:04');

-- --------------------------------------------------------

--
-- Table structure for table categories
--

CREATE TABLE categories (
  id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  description text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table categories
--

INSERT INTO categories (id, name, description) VALUES
(1, 'Arabica', 'Cà phê Arabica với hương vị thơm ngon, chua nhẹ'),
(2, 'Robusta', 'Cà phê Robusta đậm đà, hương vị mạnh mẽ'),
(3, 'Chồn', 'Cà phê Chồn đặc biệt, hương vị độc đáo'),
(4, 'Khác', 'Các loại cà phê khác');

-- --------------------------------------------------------

--
-- Table structure for table orders
--

CREATE TABLE orders (
  id int(11) NOT NULL,
  order_number varchar(30) DEFAULT NULL,
  user_id int(11) NOT NULL,
  shipping_name varchar(100) NOT NULL,
  shipping_address varchar(255) NOT NULL,
  shipping_city varchar(100) NOT NULL,
  shipping_phone varchar(20) NOT NULL,
  payment_method varchar(50) NOT NULL,
  total_amount decimal(10,2) NOT NULL,
  status enum('processing','shipping','delivered','cancelled') NOT NULL DEFAULT 'processing',
  status_note varchar(255) DEFAULT NULL,
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),  
  order_date datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table orders
--

INSERT INTO orders (id, order_number, user_id, shipping_name, shipping_address, shipping_city, shipping_phone, payment_method, total_amount, status, order_date) VALUES
(1, NULL, 1, 'đăng khoa', '135 tan ki tan quy', 'hồ chí minh', '04092005', 'cod', 150000.00, 'processing', '2025-04-21 02:19:09'),
(3, 'ORDER19700101010000704', 2, 'đăng khoa', '135 tan ki tan quy', 'hồ chí minh', '0865545705', 'cod', 1400000.00, 'processing', '2025-04-22 00:30:34'),
(5, 'ORDER19700101010000854', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 700000.00, 'processing', '2025-05-02 09:25:26'),
(6, 'ORDER19700101010000721', 2, 'Nguyễn Phúc Đăng Khoa', '135/3a tân ký tân quý', 'Không rõ', '0865545705', 'cod', 550000.00, 'processing', '2025-05-05 10:03:14'),
(7, 'ORDER19700101010000733', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 700000.00, 'processing', '2025-05-05 16:29:18'),
(8, 'ORDER19700101010000653', 2, 'Nguyễn Phúc Đăng Khoa', '135/3a tân', 'Không rõ', '0865545705', 'cod', 1050000.00, 'processing', '2025-05-07 09:08:56'),
(9, 'ORDER19700101010000396', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 150000.00, 'processing', '2025-05-07 09:22:23'),
(10, 'ORDER19700101010000479', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 150000.00, 'processing', '2025-05-07 09:27:55'),
(11, 'ORDER19700101010000748', 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 850000.00, 'processing', '2025-05-07 09:34:41'),
(12, NULL, 2, 'Nguyễn Phúc Đăng Khoa', 'hcm', 'Không rõ', '0865545705', 'cod', 350000.00, 'processing', '2025-05-09 06:26:19');

-- --------------------------------------------------------

--
-- Table structure for table order_items
--

CREATE TABLE order_items (
  id int(11) NOT NULL,
  order_id int(11) NOT NULL,
  product_name varchar(100) NOT NULL,
  quantity int(11) NOT NULL,
  price decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table order_items
--

INSERT INTO order_items (id, order_id, product_name, quantity, price) VALUES
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
(20, 12, 'Robusta Uganda', 1, 200000.00);

-- --------------------------------------------------------

--
-- Table structure for table products
--

CREATE TABLE products (
  id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  description text DEFAULT NULL,
  price decimal(10,2) NOT NULL,
  image varchar(255) NOT NULL,
  category_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  active tinyint(1) DEFAULT 1,
  featured tinyint(1) DEFAULT 0,
  weight varchar(50) DEFAULT NULL,
  stock int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table products
--

INSERT INTO products (id, name, description, price, image, category_id, created_at, active, featured, weight, stock) VALUES
(20, 'Robusta Uganda', 'Nổi tiếng vì chất lượng ổn định, vị đậm đà.\nThường được dùng trong các loại cà phê hòa tan và espresso blend', 200000.00, 'uploads/products/product_6805d87d6a14b.jpg', 2, '2025-04-21 05:32:45', 1, 0, '250', 100),
(21, 'Robusta Việt Nam (Cà phê Vối)', 'Trồng chủ yếu ở Tây Nguyên như Đắk Lắk, Gia Lai, Lâm Đồng.\nVị đậm, đắng mạnh, ít chua.\nHàm lượng caffeine cao, thích hợp pha phin truyền thống', 150000.00, 'uploads/products/product_6805d8b4a0501.jpg', 2, '2025-04-21 05:33:40', 1, 0, '250', 100),
(22, 'Robusta Ấn Độ (Indian Robusta Cherry)', 'Có mùi thơm hơi ngọt, ít đắng hơn so với Robusta Việt Nam.\nĐược dùng trong pha trộn với Arabica để tạo hương vị cân bằng.', 350000.00, 'uploads/products/product_6805da1d277cd.png', 2, '2025-04-21 05:39:41', 1, 0, '250', 50),
(23, 'Robusta Buôn Ma Thuột', 'Nguồn gốc: Thành phố Buôn Ma Thuột – thủ phủ cà phê Việt Nam.\nHương vị: Mạnh mẽ, đậm đà, hậu vị socola.\nĐặc điểm: Cà phê rang đậm, thường dùng cho gu mạnh.\nỨng dụng: Rất phổ biến trong các dòng cà phê hòa tan.', 150000.00, 'uploads/products/product_6820415606312.jpg', 2, '2025-05-11 06:19:02', 1, 0, '250', 100),
(24, 'Robusta Indonesia (Java, Sumatra)', 'Nguồn gốc: Các đảo như Java, Sumatra.\nHương vị: Thân đầy, đậm vị đất, mùi khói nhẹ.\nĐặc điểm: Được ủ ẩm lâu (wet hulling), tạo vị rất riêng biệt.\nỨng dụng: Espresso blend phương Tây.', 300000.00, 'uploads/products/product_682041dee6967.jpg', 2, '2025-05-11 06:21:18', 1, 0, '250', 100),
(25, 'Robusta Brazil (Conilon)', 'Nguồn gốc: Espírito Santo – miền Đông Nam Brazil.\nHương vị: Đắng nhẹ, hậu vị ngắn, ít hương thơm hơn Arabica Brazil.\nĐặc điểm: Năng suất cao, chi phí sản xuất thấp.\nỨng dụng: Pha trộn, cà phê hoà tan công nghiệp.', 350000.00, 'uploads/products/product_6820472686640.jpg', 2, '2025-05-11 06:43:50', 1, 0, '250', 100),
(26, 'Robusta Congo (DRC – Democratic Republic of Congo)', 'Nguồn gốc: Trung Phi.\nHương vị: Vị cacao đậm, hậu vị dày, có độ đắng rõ rệt.\nĐặc điểm: Phát triển tự nhiên, không quá công nghiệp hóa.\nỨng dụng: Pha trộn, cà phê hòa tan.', 350000.00, 'uploads/products/product_68204dc0ae5fb.png', 2, '2025-05-11 07:12:00', 1, 0, '500', 100),
(27, 'Robusta Cameroon', 'Nguồn gốc: Vùng núi phía Tây Cameroon.\nHương vị: Thơm nhẹ, vị cacao, ít chua.\nĐặc điểm: Canh tác theo mô hình truyền thống, bền vững.\nỨng dụng: Chế biến theo hướng cao cấp và xuất khẩu sang châu Âu.', 400000.00, 'uploads/products/product_68204e70a7ab8.jpg', 2, '2025-05-11 07:14:56', 1, 0, '250', 100),
(28, 'Robusta Cote d\'Ivoire (Ivory Coast)', 'Nguồn gốc: Tây Phi.\nHương vị: Đậm, đắng, ít hậu vị, thân nhẹ.\nĐặc điểm: Là quốc gia sản xuất Robusta hàng đầu châu Phi.\nỨng dụng: Sản xuất cà phê hòa tan quy mô lớn.', 250000.00, 'uploads/products/product_68204e9fe695d.webp', 2, '2025-05-11 07:15:43', 1, 0, '250', 100),
(29, 'Robusta Laos (Bolaven Plateau)', 'Nguồn gốc: Cao nguyên Bolaven, miền Nam Lào.\nHương vị: Vị dịu, hậu ngọt nhẹ, thơm nhẹ.\nĐặc điểm: Độ cao tương đối (1000m), khí hậu tương đồng Tây Nguyên Việt Nam.\nỨng dụng: Dùng pha máy hoặc phin, đang dần phát triển thương hiệu riêng.', 250000.00, 'uploads/products/product_682053717a90c.jpg', 2, '2025-05-11 07:36:17', 1, 0, '250', 100),
(30, 'Robusta Cầu Đất (Lâm Đồng)', 'Nguồn gốc: Cao nguyên Cầu Đất, nơi chủ yếu trồng Arabica nhưng cũng có Robusta.\nHương vị: Đậm đà, hậu vị ngọt nhẹ, ít đắng hơn so với Robusta Tây Nguyên.\nĐặc điểm: Trồng ở độ cao cao hiếm hoi với Robusta (~900–1000m), nên có sự pha trộn hương vị tinh tế.', 150000.00, 'uploads/products/product_6820750c95cce.jpg', 2, '2025-05-11 09:59:40', 1, 0, '250', 0);

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
  id int(11) NOT NULL,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  email varchar(100) NOT NULL,
  fullname varchar(100) NOT NULL,
  phone varchar(20) NOT NULL,
  address varchar(255) NOT NULL,
  city varchar(100) NOT NULL,
  role enum('admin','customer') NOT NULL DEFAULT 'customer',
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table users
--

INSERT INTO users (id, username, password, email, fullname, phone, address, city, role, created_at) VALUES
(1, 'admin', '*38AFCAF55503A1679F96CF62072E9E890301BABA', 'admin@example.com', 'Administrator', '', '', '', 'admin', '2025-04-20 17:50:23'),
(2, 'Đăng Khoa', '*2599F35A65FBE0337C73FE506BA4C89B137D639E', 'dangkhoanguyenphuc0409@gmail.com', 'Nguyễn Phúc Đăng Khoa', '0865545705', '', '', 'customer', '2025-04-21 17:30:06');

-- --------------------------------------------------------

--
-- Table structure for table user_details
--

CREATE TABLE user_details (
  user_id int(11) NOT NULL,
  email varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  fullname varchar(100) NOT NULL,
  phone varchar(20) DEFAULT NULL,
  address varchar(255) DEFAULT NULL,
  city varchar(50) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table user_details
--

INSERT INTO user_details (user_id, email, password, fullname, phone, address, city, created_at) VALUES
(2, 'dangkhoanguyenphuc0409@gmail.com', '$2y$10$.55E3NgcfabaDS/9duPqyef.9jZbENOlQOfsLtW8WQ8GCUz.vR2MW', 'Nguyễn Phúc Đăng Khoa', '0865545705', '', '', '2025-04-21 17:30:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table addresses
--
ALTER TABLE addresses
  ADD PRIMARY KEY (id),
  ADD KEY user_id (user_id);

--
-- Indexes for table categories
--
ALTER TABLE categories
  ADD PRIMARY KEY (id);

--
-- Indexes for table orders
--
ALTER TABLE orders
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY order_number (order_number),
  ADD KEY user_id (user_id);

--
-- Indexes for table order_items
--
ALTER TABLE order_items
  ADD PRIMARY KEY (id),
  ADD KEY order_id (order_id);

--
-- Indexes for table products
--
ALTER TABLE products
  ADD PRIMARY KEY (id),
  ADD KEY category_id (category_id);

--
-- Indexes for table users
--
ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY username (username),
  ADD UNIQUE KEY email (email);

--
-- Indexes for table user_details
--
ALTER TABLE user_details
  ADD PRIMARY KEY (user_id),
  ADD UNIQUE KEY email (email);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table addresses
--
ALTER TABLE addresses
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table categories
--
ALTER TABLE categories
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table orders
--
ALTER TABLE orders
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table order_items
--
ALTER TABLE order_items
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table products
--
ALTER TABLE products
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table user_details
--
ALTER TABLE user_details
  MODIFY user_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table orders
--
ALTER TABLE orders
  ADD CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id);

--
-- Constraints for table order_items
--
ALTER TABLE order_items
  ADD CONSTRAINT order_items_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (id);

--
-- Constraints for table products
--
ALTER TABLE products
  ADD CONSTRAINT products_ibfk_1 FOREIGN KEY (category_id) REFERENCES categories (id);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;