<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$success_messages = [];
$error_messages = [];

// Tạo bảng admin_users
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    $success_messages[] = "Bảng admin_users đã được tạo thành công!";
} else {
    $error_messages[] = "Lỗi khi tạo bảng admin_users: " . $conn->error;
}

// Kiểm tra xem đã có tài khoản admin chưa
$sql = "SELECT id FROM admin_users WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Thêm tài khoản admin mặc định
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_users (username, password, name, email, role) 
            VALUES ('admin', '$admin_password', 'Administrator', 'admin@example.com', 'super_admin')";
    
    if ($conn->query($sql) === TRUE) {
        $success_messages[] = "Tài khoản admin mặc định đã được tạo thành công!";
    } else {
        $error_messages[] = "Lỗi khi tạo tài khoản admin: " . $conn->error;
    }
} else {
    $success_messages[] = "Tài khoản admin đã tồn tại!";
}

// Kiểm tra các bảng cần thiết khác
$required_tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        city VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    'products' => "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50) NOT NULL,
        image VARCHAR(255),
        weight VARCHAR(50),
        stock INT DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    'orders' => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50),
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(20) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )"
];

foreach ($required_tables as $table => $create_sql) {
    // Kiểm tra xem bảng đã tồn tại chưa
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    
    if ($result->num_rows == 0) {
        // Nếu bảng chưa tồn tại, tạo mới
        if ($conn->query($create_sql) === TRUE) {
            $success_messages[] = "Bảng $table đã được tạo thành công!";
        } else {
            $error_messages[] = "Lỗi khi tạo bảng $table: " . $conn->error;
        }
    } else {
        $success_messages[] = "Bảng $table đã tồn tại!";
    }
}

// Kiểm tra cột order_number trong bảng orders
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_number'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) AFTER user_id";
    if ($conn->query($sql) === TRUE) {
        $success_messages[] = "Cột order_number đã được thêm vào bảng orders!";
    } else {
        $error_messages[] = "Lỗi khi thêm cột order_number: " . $conn->error;
    }
}

// Tạo file login.php nếu chưa tồn tại
$login_file = 'login.php';
if (!file_exists($login_file)) {
    $login_content = '<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $sql = "SELECT id, username, password, name FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            $_SESSION["admin_id"] = $row["id"];
            $_SESSION["admin_name"] = $row["name"];
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Mật khẩu không đúng";
        }
    } else {
        $error = "Tài khoản không tồn tại";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-form {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Đăng nhập Admin</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>';
    
    file_put_contents($login_file, $login_content);
    $success_messages[] = "Đã tạo file login.php";
}

// Tạo file index.php nếu chưa tồn tại
$index_file = 'index.php';
if (!file_exists($index_file)) {
    $index_content = '<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            padding-top: 20px;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .menu {
            list-style: none;
            padding: 0;
        }
        .menu li {
            padding: 15px;
            border-bottom: 1px solid #444;
        }
        .menu li a {
            color: white;
            text-decoration: none;
        }
        .menu li:hover {
            background-color: #444;
        }
        .header {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: #f4f4f4;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul class="menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="#">Sản phẩm</a></li>
                <li><a href="#">Đơn hàng</a></li>
                <li><a href="#">Người dùng</a></li>
                <li><a href="#">Thống kê</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </div>
        
        <div class="content">
            <div class="header">
                <h1>Dashboard</h1>
                <div>
                    Xin chào, <?php echo $_SESSION["admin_name"]; ?>
                </div>
            </div>
            
            <div class="dashboard-content">
                <h2>Chào mừng đến với trang quản trị!</h2>
                <p>Đây là trang quản trị của website Cà Phê Đậm Đà.</p>
            </div>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents($index_file, $index_content);
    $success_messages[] = "Đã tạo file index.php";
}

// Tạo file logout.php nếu chưa tồn tại
$logout_file = 'logout.php';
if (!file_exists($logout_file)) {
    $logout_content = '<?php
session_start();
// Hủy session
session_unset();
session_destroy();
// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?>';
    
    file_put_contents($logout_file, $logout_content);
    $success_messages[] = "Đã tạo file logout.php";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .success {
            color: green;
            background-color: #dff0d8;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .error {
            color: #a94442;
            background-color: #f2dede;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        ul {
            padding-left: 20px;
        }
        .buttons {
            margin-top: 20px;
            text-align: center;
        }
        .buttons a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .buttons a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thiết lập Admin - Cà Phê Đậm Đà</h1>
        
        <?php if (!empty($success_messages)): ?>
            <div class="success">
                <strong>Thành công!</strong>
                <ul>
                    <?php foreach ($success_messages as $message): ?>
                        <li><?php echo $message; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_messages)): ?>
            <div class="error">
                <strong>Lỗi!</strong>
                <ul>
                    <?php foreach ($error_messages as $message): ?>
                        <li><?php echo $message; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div>
            <h2>Thông tin đăng nhập:</h2>
            <p><strong>Tên đăng nhập:</strong> admin</p>
            <p><strong>Mật khẩu:</strong> admin123</p>
        </div>
        
        <div class="buttons">
            <a href="login.php">Đi đến trang đăng nhập</a>
            <a href="../index.php">Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html> 