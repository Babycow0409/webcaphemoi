<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Cà Phê Đậm Đà</title>
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

        .register-container {
            max-width: 500px;
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
        }

        .links a:hover {
            text-decoration: underline;
        }

        .success-message {
            color: #28a745;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 5px;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
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

    <div class="register-container">
        <h1>Đăng ký tài khoản</h1>
        <?php
        session_start();
        require_once 'config/database.php';

        if(isset($_POST['register'])) {
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Mã hóa mật khẩu
            $phone = $_POST['phone'];
            
            try {
                // Kiểm tra email đã tồn tại chưa
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if($stmt->rowCount() > 0) {
                    echo "<p class='error-message'>Email này đã được đăng ký!</p>";
                } else {
                    // Thêm người dùng mới
                    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, phone) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$fullname, $email, $password, $phone]);
                    
                    echo "<p class='success-message'>Đăng ký thành công! Vui lòng <a href='login.php'>đăng nhập</a> để tiếp tục.</p>";
                }
            } catch(PDOException $e) {
                echo "<p class='error-message'>Có lỗi xảy ra: " . $e->getMessage() . "</p>";
            }
        }
        ?>
        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="fullname">Họ và tên:</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="register" class="btn">Đăng ký</button>
        </form>
        <div class="links">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
        </div>
    </div>

    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const phone = document.getElementById('phone').value;
            
            // Kiểm tra mật khẩu trùng khớp
            if(password !== confirmPassword) {
                alert('Mật khẩu không trùng khớp!');
                return false;
            }
            
            // Kiểm tra định dạng số điện thoại
            const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
            if(!phoneRegex.test(phone)) {
                alert('Số điện thoại không hợp lệ!');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html> 