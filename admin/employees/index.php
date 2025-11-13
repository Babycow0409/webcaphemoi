<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

// Kết nối CSDL
$host = "localhost";
$username = "root"; 
$password = "";
$database = "lab1";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


// Lấy danh sách nhân viên
$sql = "SELECT * FROM employees";
$result = $conn->query($sql);
