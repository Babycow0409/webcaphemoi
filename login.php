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
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'] ?? $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                
                // Chuyển hướng đến trang chủ
                header("Location: index.php");
                exit;
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
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('images/coffee-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
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
</body>
</html> 