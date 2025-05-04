<?php
session_start();

// Kiểm tra nếu đã đăng nhập, chuyển hướng đến trang admin
if (isset($_SESSION["admin"])) {
    header("Location: index.php");
    exit();
}

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

$error = "";

// Xử lý form đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    
    // Kiểm tra xem bảng admin có tồn tại không
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin'");
    
    if ($tableCheck->num_rows > 0) {
        // Truy vấn từ bảng admin
        $sql = "SELECT * FROM admin WHERE email = '$email'";
    } else {
        // Truy vấn từ bảng users với quyền admin - không cần join
        $sql = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
    }
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra mật khẩu
        if (password_verify($password, $user["password"]) || 
            ($user["password"] == '*38AFCAF55503A1679F96CF62072E9E890301BABA' && $password == 'admin123')) {
            // Đăng nhập thành công
            $_SESSION["admin"] = [
                "id" => $user["id"],
                "email" => $user["email"],
                "name" => $user["fullname"] ?? $user["name"] ?? "Admin" // Sử dụng fullname hoặc name hoặc "Admin"
            ];
            
            // Chuyển hướng đến trang admin
            header("Location: index.php");
            exit();
        } else {
            // Debug để xem mật khẩu hash
            echo "DEBUG: ";
            echo "Mật khẩu nhập: " . $password . "<br>";
            echo "Mật khẩu hash trong DB: " . $user["password"] . "<br>";
            echo "Kết quả password_verify: " . (password_verify($password, $user["password"]) ? "TRUE" : "FALSE") . "<br>";
            $error = "Mật khẩu không đúng";
        }
    } else {
        $error = "Email không tồn tại";
        
        // Nếu chưa có tài khoản admin, tạo tài khoản admin mặc định
        $adminCheck = $conn->query("SELECT * FROM users WHERE role='admin'");
        if ($adminCheck->num_rows == 0 && $email == "admin@example.com" && $password == "admin123") {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Kiểm tra xem bảng users đã tồn tại chưa
            $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
            if ($tableCheck->num_rows > 0) {
                // Thêm admin vào bảng users với fullname trong cùng bảng
                $sql = "INSERT INTO users (email, password, role, fullname, created_at) 
                        VALUES ('$email', '$hashedPassword', 'admin', 'Administrator', NOW())";
                if ($conn->query($sql) === TRUE) {
                    $userId = $conn->insert_id;
                    
                    // Đăng nhập với tài khoản admin mới
                    $_SESSION["admin"] = [
                        "id" => $userId,
                        "email" => $email,
                        "name" => "Administrator"
                    ];
                    
                    // Chuyển hướng đến trang admin
                    header("Location: index.php");
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Cà Phê Đậm Đà</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .form-signin {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            margin: auto;
        }
        .form-signin .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-signin .card-header {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .form-signin .card-body {
            padding: 30px;
        }
        .form-signin .form-control {
            position: relative;
            box-sizing: border-box;
            height: auto;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .form-signin .btn-primary {
            background-color: #343a40;
            border-color: #343a40;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-top: 20px;
        }
        .form-signin .btn-primary:hover {
            background-color: #23272b;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-signin">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Admin Dashboard</h4>
                <p class="mb-0">Cà Phê Đậm Đà</p>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
                </form>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">Sử dụng email: admin@example.com và password: admin123 nếu chưa có tài khoản admin</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 