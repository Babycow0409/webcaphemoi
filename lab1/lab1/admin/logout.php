<?php
session_start();

// Xóa session của admin
unset($_SESSION['admin']);

// Chuyển hướng đến trang đăng nhập
header("Location: login.php");
exit();
?> 