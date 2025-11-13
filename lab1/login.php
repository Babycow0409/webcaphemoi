<?php
session_start();

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

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrUsername = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Truy vấn thông tin đăng nhập từ bảng users (kiểm tra cả email và username)
    $stmt = $conn->prepare("SELECT id, username, email, password, fullname, role, active FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $emailOrUsername, $emailOrUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra trạng thái tài khoản
        if ($user['active'] == 0) {
            $error = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để được hỗ trợ.";
        } else {
            $login_success = false;
            
            // Kiểm tra mật khẩu với bcrypt (dùng cho người dùng tạo từ admin panel)
            if (password_verify($password, $user['password'])) {
                $login_success = true;
            } else {
                // Kiểm tra mật khẩu với định dạng SHA1 (dùng cho người dùng cũ)
                $hashed_input_password = '*' . strtoupper(sha1(sha1($password, true)));
                if ($hashed_input_password === $user['password']) {
                    $login_success = true;
                }
            }
            
            if ($login_success) {
                // Đăng nhập thành công, lưu thông tin vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                
                // Always redirect to home page after login
                header("Location: index.php");
                exit();
            } else {
                $error = "Mật khẩu không chính xác.";
            }
        }
    } else {
        $error = "Email hoặc tên đăng nhập không tồn tại trong hệ thống.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { 
            padding-top: 100px; 
            line-height: 1.6; 
            min-height: 100vh;
            background-color: #f5f5f5;
            background-image: url('images/coffee-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        header { 
            background-color: #3c2f2f; 
            color: white; 
            padding: 1rem; 
            position: fixed; 
            width: 100%; 
            top: 0; 
            z-index: 1000; 
        }
        
        nav { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        
        .logo { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.8em; 
            padding: 10px; 
        }
        
        .nav-links { 
            display: flex; 
            flex-wrap: wrap; 
            align-items: center; 
            padding: 10px; 
        }
        
        nav a { 
            color: white; 
            text-decoration: none; 
            margin: 10px 15px; 
            font-weight: bold; 
        }
        
        nav a:hover { 
            color: #d4a373; 
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #3c2f2f;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
        }
        
        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        
        .dropdown-content a:hover {
            background-color: #d4a373;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .container {
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 200px);
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            margin: 20px 0;
        }
    align-items: center;
    min-height: calc(100vh - 200px);
}

.login-container {
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    padding: 40px;
    width: 100%;
    max-width: 500px;
    margin: 20px 0;
}
        
        h1 {
            font-family: 'Playfair Display', serif;
            text-align: center;
            color: #5d4037;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #5d4037;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #f8f9fa;
        }
        
        button {
            background-color: #5d4037;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #3e2723;
        }
        
        .error {
            color: #e53935;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ffcdd2;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .register-link a {
            color: #5d4037;
            text-decoration: none;
            font-weight: bold;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cà Phê Đậm Đà</div>
            <div class="nav-links">
                <a href="index.php">Trang chủ</a>
                <div class="dropdown">
                    <a href="products.php">Sản phẩm</a>
                    <div class="dropdown-content">
                        <a href="products.php">Tất cả</a>
                        <a href="arabica.php">Arabica</a>
                        <a href="robusta.php">Robusta</a>
                        <a href="chon.php">Chồn</a>
                        <a href="Khac.php">Khác</a>
                    </div>
                </div>
                <a href="index.php#about">Giới thiệu</a>
                <a href="index.php#contact">Liên hệ</a>
                <a href="cart.php">Giỏ hàng</a>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <div class="login-container">
        <h1>Đăng Nhập</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Email hoặc Tên đăng nhập:</label>
                <input type="text" id="username" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Đăng Nhập</button>
        </form>
        
        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
        </div>
    </div>
    
    <footer id="contact">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <!-- Your existing footer content here -->
        </div>
    </footer>
</body>
</html> 