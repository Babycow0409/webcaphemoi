-- Cập nhật cơ sở dữ liệu lab1 - Sửa đường dẫn hình ảnh và thêm sản phẩm mới

-- Sửa đường dẫn hình ảnh trong bảng products
-- Bỏ tiền tố 'uploads/' trong đường dẫn hình ảnh
UPDATE products SET 
  image = REPLACE(image, 'uploads/', '') 
WHERE image LIKE 'uploads/%';

-- Thêm sản phẩm mới vào danh mục Arabica
INSERT INTO products (name, description, price, image, category_id, created_at, active, featured, weight, stock) 
VALUES 
('Arabica Colombia Supremo', 'Cà phê Arabica Colombia Supremo là một trong những loại cà phê có chất lượng cao nhất của Colombia. Hạt cà phê được trồng ở độ cao từ 1.200 đến 1.800 mét so với mực nước biển, tạo ra hương vị đặc trưng với vị chua dịu nhẹ, hương thơm của hoa quả và caramel.', 400000.00, 'products/product_68219f7ddcfbd.jpg', 1, NOW(), 1, 1, '250', 50),
('Arabica Ethiopia Yirgacheffe', 'Cà phê Arabica Ethiopia Yirgacheffe nổi tiếng với hương thơm hoa cỏ, vị chua thanh của chanh và trái cây nhiệt đới. Đây là một trong những loại cà phê được đánh giá cao nhất thế giới, được trồng ở vùng Yirgacheffe, Ethiopia - quê hương của cà phê.', 450000.00, 'products/product_68219e68e4d34.webp', 1, NOW(), 1, 1, '250', 30),
('Arabica Guatemala Antigua', 'Cà phê Arabica Guatemala Antigua có hương vị đặc trưng với vị chua thanh, hậu vị chocolate và hương hoa quả nhẹ. Được trồng ở thung lũng Antigua, Guatemala, một khu vực với đất núi lửa màu mỡ ở độ cao khoảng 1.500 mét.', 420000.00, 'products/product_682219ecb5b787.jpg', 1, NOW(), 1, 0, '250', 40);

-- Thêm sản phẩm mới vào danh mục Chồn
INSERT INTO products (name, description, price, image, category_id, created_at, active, featured, weight, stock) 
VALUES 
('Cà Phê Chồn Túi Lọc Premium', 'Sản phẩm cà phê chồn túi lọc cao cấp, giữ nguyên hương vị đặc trưng của cà phê chồn tự nhiên. Tiện lợi khi sử dụng, không cần dụng cụ pha chế phức tạp. Mỗi túi lọc chứa 10g cà phê chồn nguyên chất.', 1500000.00, 'products/product_68204dc0ae5fb.png', 3, NOW(), 1, 1, '100', 15),
('Cà Phê Chồn Nguyên Hạt Đặc Biệt', 'Cà phê chồn nguyên hạt, được thu hoạch và chế biến theo phương pháp truyền thống, giữ trọn vẹn hương vị đặc trưng. Mỗi hạt cà phê đều trải qua quá trình kiểm soát chất lượng nghiêm ngặt.', 2000000.00, 'products/product_68219da3ee85b.jpg', 3, NOW(), 1, 0, '100', 10);

-- Thêm sản phẩm mới vào danh mục Khác
INSERT INTO products (name, description, price, image, category_id, created_at, active, featured, weight, stock) 
VALUES 
('Cà Phê Hòa Tan 3in1', 'Sản phẩm cà phê hòa tan 3in1 tiện lợi, kết hợp giữa cà phê, đường và sữa. Thích hợp cho người bận rộn, muốn thưởng thức cà phê nhanh chóng. Hương vị đậm đà, thơm ngon.', 120000.00, 'products/product_6822070129660.webp', 4, NOW(), 1, 1, '300', 100),
('Cà Phê Rang Xay Pha Phin Truyền Thống', 'Cà phê được rang xay đặc biệt phù hợp với phương pháp pha phin truyền thống của Việt Nam. Hương vị đậm đà, đắng nhẹ, hậu vị ngọt, thích hợp uống đá hoặc nóng.', 180000.00, 'products/product_68220633a8dba.jpg', 4, NOW(), 1, 1, '250', 80);

-- Đảm bảo mọi sản phẩm đều có hình ảnh
UPDATE products SET 
  image = 'products/default-product.jpg' 
WHERE image = '' OR image IS NULL; 