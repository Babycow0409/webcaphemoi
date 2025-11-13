<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS coffee_shop";
if ($conn->query($sql) === TRUE) {
    echo "Cơ sở dữ liệu đã được tạo thành công<br>";
} else {
    echo "Lỗi khi tạo cơ sở dữ liệu: " . $conn->error . "<br>";
}

// Use database
$conn->select_db("coffee_shop");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(100) UNIQUE,
    fullname VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng users đã được tạo thành công<br>";
} else {
    echo "Lỗi khi tạo bảng users: " . $conn->error . "<br>";
}

// Create addresses table
$sql = "CREATE TABLE IF NOT EXISTS addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    address VARCHAR(255),
    city VARCHAR(100),
    is_default BOOLEAN DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng addresses đã được tạo thành công<br>";
} else {
    echo "Lỗi khi tạo bảng addresses: " . $conn->error . "<br>";
}

// Create products table (if not exists)
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    category VARCHAR(50),
    weight INT DEFAULT 250,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng products đã được tạo thành công<br>";
} else {
    echo "Lỗi khi tạo bảng products: " . $conn->error . "<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_number VARCHAR(20) UNIQUE,
    total_amount DECIMAL(10,2),
    fullname VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    payment_method VARCHAR(50),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng orders đã được tạo thành công<br>";
} else {
    echo "Lỗi khi tạo bảng orders: " . $conn->error . "<br>";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Bảng order_items đã được tạo thành công<br>";
} else {
    echo "Lỗi khi tạo bảng order_items: " . $conn->error . "<br>";
}

// Insert sample products if not exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Get products from data/products.php
    include 'data/products.php';
    
    if (isset($all_products) && is_array($all_products)) {
        $stmt = $conn->prepare("INSERT INTO products (id, name, price, image, description, category, weight) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($all_products as $product) {
            $id = $product['id'];
            $name = $product['name'];
            $price = $product['price'];
            $image = $product['image'];
            $description = isset($product['description']) ? $product['description'] : '';
            $category = $product['category'];
            $weight = isset($product['weight']) ? $product['weight'] : 250;
            
            $stmt->bind_param("isdssis", $id, $name, $price, $image, $description, $category, $weight);
            $stmt->execute();
        }
        
        echo "Các sản phẩm mẫu đã được thêm vào<br>";
    }
}

$conn->close();
echo "Thiết lập cơ sở dữ liệu hoàn tất!";
?> 