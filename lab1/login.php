<?php
session_start();
require_once 'config/database.php';

$error_message = '';
$login_successful = false;

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        if(isset($conn)) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user['email'];
                $_SESSION['fullname'] = $user['fullname'];
                $login_successful = true;
                
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Email hoặc mật khẩu không đúng!";
            }
        } else {
            $error_message = "Lỗi kết nối cơ sở dữ liệu!";
        }
    } catch(PDOException $e) {
        $error_message = "Có lỗi xảy ra: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
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
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1956&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        header { background-color: #3c2f2f; color: white; padding: 1rem; position: fixed; width: 100%; top: 0; z-index: 1000; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.8em; }
        .nav-links { display: flex; align-items: center; }
        nav a { color: white; text-decoration: none; margin: 0 15px; }
        nav a:hover { color: #d4a373; }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }

        h1 { 
            font-family: 'Playfair Display', serif; 
            color: #3c2f2f; 
            text-align: center; 
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #3c2f2f;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d4a373;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #d4a373;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #8b4513;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #d4a373;
            text-decoration: none;
            margin: 0 10px;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #ff0000;
            text-align: center;
            margin-bottom: 15px;
        }

        .order-badge {
            background-color: #ff4444;
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cà Phê Đậm Đà</div>
            <div class="nav-links">
                <a href="index.php">Trang chủ</a>
                <a href="products.php">Sản phẩm</a>
                <a href="#about">Giới thiệu</a>
                <a href="#contact">Liên hệ</a>
            </div>
        </nav>
    </header>

    <div class="login-container">
        <h1>Đăng nhập</h1>
        <?php
        if($error_message) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn">Đăng nhập</button>
        </form>
        <div class="links">
            <a href="register.php">Đăng ký tài khoản mới</a>
            <a href="#">Quên mật khẩu?</a>
        </div>
    </div>
</body>
</html> 