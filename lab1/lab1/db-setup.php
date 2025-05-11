<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra và tạo thư mục images nếu chưa tồn tại
if (!file_exists('images')) {
    mkdir('images', 0777, true);
    echo "Đã tạo thư mục images<br>";
}

// Tạo bảng categories nếu chưa tồn tại
$sql_create_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_categories) === TRUE) {
    echo "Bảng categories đã được tạo hoặc đã tồn tại<br>";
} else {
    echo "Lỗi khi tạo bảng categories: " . $conn->error . "<br>";
}

// Kiểm tra và tạo bảng products nếu chưa tồn tại
$sql_create_products = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT(11) DEFAULT 1,
    active TINYINT(1) DEFAULT 1,
    featured TINYINT(1) DEFAULT 0,
    weight VARCHAR(50),
    stock INT(11) DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_products) === TRUE) {
    echo "Bảng products đã được tạo hoặc đã tồn tại<br>";
} else {
    echo "Lỗi khi tạo bảng products: " . $conn->error . "<br>";
}

// Thêm trường active, category_id vào bảng products nếu chưa có
$sql_check_columns = "SHOW COLUMNS FROM products LIKE 'active'";
$result = $conn->query($sql_check_columns);
if ($result && $result->num_rows == 0) {
    $sql_add_active = "ALTER TABLE products ADD COLUMN active TINYINT(1) DEFAULT 1";
    if ($conn->query($sql_add_active) === TRUE) {
        echo "Đã thêm cột active vào bảng products<br>";
    } else {
        echo "Lỗi khi thêm cột active: " . $conn->error . "<br>";
    }
}

$sql_check_columns = "SHOW COLUMNS FROM products LIKE 'category_id'";
$result = $conn->query($sql_check_columns);
if ($result && $result->num_rows == 0) {
    $sql_add_category_id = "ALTER TABLE products ADD COLUMN category_id INT(11) DEFAULT 1";
    if ($conn->query($sql_add_category_id) === TRUE) {
        echo "Đã thêm cột category_id vào bảng products<br>";
    } else {
        echo "Lỗi khi thêm cột category_id: " . $conn->error . "<br>";
    }
}

// Thêm trường featured vào bảng products nếu chưa có
$sql_check_columns = "SHOW COLUMNS FROM products LIKE 'featured'";
$result = $conn->query($sql_check_columns);
if ($result && $result->num_rows == 0) {
    $sql_add_featured = "ALTER TABLE products ADD COLUMN featured TINYINT(1) DEFAULT 0";
    if ($conn->query($sql_add_featured) === TRUE) {
        echo "Đã thêm cột featured vào bảng products<br>";
    } else {
        echo "Lỗi khi thêm cột featured: " . $conn->error . "<br>";
    }
}

// Định nghĩa đường dẫn ảnh cho các danh mục
$category_images = [
    1 => 'https://lh6.googleusercontent.com/proxy/ULqvKQ2UCFsMhYAqAbJE1VXiCR4I6IDe6dtj5t5h7qBXzhy4bqhlzOC3FlzOXHrOcvWBb_oiCQRi0U4ZXBOK3vA',
    2 => 'https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003',
    3 => 'https://vn-live-01.slatic.net/p/cdf5f80d6feaa2e85e10968606ea4df6.jpg',
    4 => 'https://image.made-in-china.com/2f0j00ftbUBTwEaGcq/Vietnam-Kopi-Luwak-Coffee-Bean-Civet-Coffee-Kopi-Civet-Cat-Coffee-Bean.jpg'
];

// Thêm một số danh mục mặc định
$sql_insert_categories = "INSERT INTO categories (id, name, description, image) 
VALUES 
(1, 'Arabica', 'Cà phê Arabica là loại cà phê cao cấp, có hương vị thơm ngon, chua thanh, ít đắng và cafein thấp', '{$category_images[1]}'),
(2, 'Robusta', 'Cà phê Robusta có vị đắng mạnh, hương thơm mạnh mẽ và hàm lượng cafein cao', '{$category_images[2]}'),
(3, 'Chồn', 'Cà phê chồn là loại cà phê đặc biệt được sản xuất từ quá trình tiêu hóa của cầy vòi đốm (chồn)', '{$category_images[3]}'),
(4, 'Khác', 'Các loại cà phê khác', '{$category_images[4]}')";

// Chỉ thêm nếu bảng categories chưa có dữ liệu
$sql_check_categories = "SELECT * FROM categories LIMIT 1";
$result = $conn->query($sql_check_categories);
if ($result && $result->num_rows == 0) {
    if ($conn->multi_query($sql_insert_categories) === TRUE) {
        echo "Đã thêm dữ liệu vào bảng categories<br>";
        $conn->next_result(); // Cần thiết để thực hiện các truy vấn tiếp theo
    } else {
        echo "Lỗi khi thêm dữ liệu vào categories: " . $conn->error . "<br>";
    }
} else {
    // Cập nhật hình ảnh cho danh mục đã tồn tại
    foreach ($category_images as $id => $image) {
        // Kiểm tra xem cột image có tồn tại không
        $check_image_column = $conn->query("SHOW COLUMNS FROM categories LIKE 'image'");
        if ($check_image_column && $check_image_column->num_rows > 0) {
            $sql_update_image = "UPDATE categories SET image = '$image' WHERE id = $id AND (image IS NULL OR image = '')";
            $conn->query($sql_update_image);
        }
    }
    echo "Đã cập nhật hình ảnh cho các danh mục<br>";
}

// Thêm một số sản phẩm mẫu nếu bảng products chưa có dữ liệu
$sql_check_products = "SELECT * FROM products LIMIT 1";
$result = $conn->query($sql_check_products);
if ($result && $result->num_rows == 0) {
    // Mảng sản phẩm mẫu
   
    
    // Thêm sản phẩm vào database
    foreach ($sample_products as $product) {
        $sql_insert_product = "INSERT INTO products (name, description, price, image, category_id, weight, featured) 
                              VALUES (
                                  '{$product['name']}', 
                                  '{$product['description']}', 
                                  {$product['price']}, 
                                  '{$product['image']}', 
                                  {$product['category_id']}, 
                                  '{$product['weight']}',
                                  {$product['featured']}
                              )";
        $conn->query($sql_insert_product);
    }
    
    echo "Đã thêm sản phẩm mẫu vào database<br>";
}

// Cập nhật category_id cho các sản phẩm hiện có
$sql_update_products = "UPDATE products SET active = 1, category_id = FLOOR(1 + RAND() * 4) WHERE category_id = 0 OR category_id IS NULL";
if ($conn->query($sql_update_products) === TRUE) {
    echo "Đã cập nhật category_id cho các sản phẩm<br>";
} else {
    echo "Lỗi khi cập nhật sản phẩm: " . $conn->error . "<br>";
}

// Đặt một vài sản phẩm mẫu là nổi bật nếu chưa có sản phẩm nổi bật nào
$sql_check_featured = "SELECT COUNT(*) as count FROM products WHERE featured = 1";
$result_featured = $conn->query($sql_check_featured);
if ($result_featured && $result_featured->num_rows > 0) {
    $row = $result_featured->fetch_assoc();
    if ($row['count'] == 0) {
        // Đặt 3 sản phẩm đầu tiên làm nổi bật
        $sql_update_featured = "UPDATE products SET featured = 1 ORDER BY id ASC LIMIT 3";
        if ($conn->query($sql_update_featured) === TRUE) {
            echo "Đã đặt 3 sản phẩm đầu tiên làm nổi bật<br>";
        } else {
            echo "Lỗi khi đặt sản phẩm nổi bật: " . $conn->error . "<br>";
        }
    }
}

echo "Hoàn tất thiết lập cơ sở dữ liệu. <a href='index.php'>Quay lại trang chủ</a>";

$conn->close();
?> 