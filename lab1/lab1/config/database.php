<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Kết nối MySQL không cần chọn database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
    
    // Tạo database nếu chưa tồn tại
    $conn->exec("CREATE DATABASE IF NOT EXISTS coffee_shop");
    $conn->exec("USE coffee_shop");
    
    // Tạo bảng users nếu chưa tồn tại
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(15),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Tạo bảng orders nếu chưa tồn tại
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        order_code VARCHAR(50) UNIQUE,
        total_amount DECIMAL(10,2),
        status ENUM('pending', 'processing', 'shipping', 'completed', 'cancelled'),
        shipping_name VARCHAR(100),
        shipping_email VARCHAR(100),
        shipping_phone VARCHAR(15),
        shipping_address TEXT,
        shipping_city VARCHAR(100),
        payment_method VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Tạo bảng order_items nếu chưa tồn tại
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_name VARCHAR(255),
        quantity INT,
        price DECIMAL(10,2),
        FOREIGN KEY (order_id) REFERENCES orders(id)
    )");
    
    // Thêm bảng addresses trong database
    $conn->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        name VARCHAR(100),
        phone VARCHAR(15),
        address TEXT,
        city VARCHAR(100),
        is_default BOOLEAN DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
} catch(PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
    die();
}
?> 