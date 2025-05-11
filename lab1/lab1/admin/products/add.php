<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
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

// Kiểm tra bảng products đã tồn tại chưa
$check_table = $conn->query("SHOW TABLES LIKE 'products'");
if ($check_table->num_rows == 0) {
    // Tạo bảng products nếu chưa tồn tại
    $sql_create_products = "CREATE TABLE `products` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text,
        `price` decimal(10,2) NOT NULL,
        `image` varchar(255) DEFAULT NULL,
        `stock` int(11) DEFAULT '0',
        `weight` varchar(50) DEFAULT NULL,
        `category_id` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql_create_products) === FALSE) {
        die("Lỗi khi tạo bảng products: " . $conn->error);
    }
}

// Kiểm tra bảng categories đã tồn tại chưa
$check_table = $conn->query("SHOW TABLES LIKE 'categories'");
if ($check_table->num_rows == 0) {
    // Tạo bảng categories
    $sql_create_categories = "CREATE TABLE `categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `description` text,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql_create_categories) === FALSE) {
        die("Lỗi khi tạo bảng categories: " . $conn->error);
    }
    
    // Thêm các danh mục mặc định
    $default_categories = [
        ['name' => 'Arabica'],
        ['name' => 'Robusta'],
        ['name' => 'Cà phê Chồn'],
        ['name' => 'Cà phê pha sẵn'],
        ['name' => 'Cà phê hòa tan']
    ];
    
    foreach ($default_categories as $cat) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $cat['name']);
        $stmt->execute();
    }
}

// Kiểm tra cấu trúc bảng products
$fields = [];
$result = $conn->query("SHOW COLUMNS FROM products");
while ($row = $result->fetch_assoc()) {
    $fields[] = $row['Field'];
}

// Thêm cột nếu thiếu
if (!in_array('weight', $fields)) {
    $conn->query("ALTER TABLE products ADD COLUMN weight varchar(50) DEFAULT NULL");
}
if (!in_array('stock', $fields)) {
    $conn->query("ALTER TABLE products ADD COLUMN stock int(11) DEFAULT '0'");
}
if (!in_array('category_id', $fields)) {
    $conn->query("ALTER TABLE products ADD COLUMN category_id int(11) DEFAULT NULL");
}

$error = '';
$success = '';

// Xử lý thêm sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category'];
    $weight = trim($_POST['weight'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    
    // Xử lý upload ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../uploads/products/";
        
        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . uniqid('product_') . '.' . $imageFileType;
        
        // Kiểm tra loại file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedExtensions)) {
            $error = "Chỉ chấp nhận file ảnh JPG, JPEG, PNG và GIF.";
        }
        // Kiểm tra kích thước file (tối đa 5MB)
        else if ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
            $error = "Kích thước file quá lớn (tối đa 5MB).";
        }
        // Upload file
        else if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = str_replace("../../", "", $target_file);
        } else {
            $error = "Có lỗi xảy ra khi upload file.";
        }
    } else {
        // Không bắt buộc upload ảnh
        $image = '';
    }
    
    // Thêm sản phẩm vào database
    if (empty($error)) {
        try {
            $sql = "INSERT INTO products (name, description, price, category_id, weight, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiiss", $name, $description, $price, $category_id, $weight, $stock, $image);
            
            if ($stmt->execute()) {
                $success = "Thêm sản phẩm thành công!";
            } else {
                $error = "Lỗi khi thêm sản phẩm: " . $conn->error;
            }
        } catch (Exception $e) {
            $error = "Lỗi khi thêm sản phẩm: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container-fluid {
            padding: 0;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            min-height: 100vh;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .content {
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        #preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../products/index.php">
                            <i class="fas fa-coffee mr-2"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../orders/index.php">
                            <i class="fas fa-shopping-cart mr-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../statistics/top-customers.php">
                            <i class="fas fa-chart-bar mr-2"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Thêm sản phẩm mới</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <div class="content">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description">Mô tả</label>
                                            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="price">Giá <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="price" name="price" min="0" step="1000" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category">Danh mục <span class="text-danger">*</span></label>
                                                    <select class="form-control" id="category" name="category" required>
                                                        <option value="">-- Chọn danh mục --</option>
                                                        <?php
                                                        // Lấy danh sách danh mục từ database
                                                        $sql_categories = "SELECT * FROM categories ORDER BY name ASC";
                                                        $result_categories = $conn->query($sql_categories);
                                                        
                                                        if ($result_categories && $result_categories->num_rows > 0) {
                                                            while($cat = $result_categories->fetch_assoc()) {
                                                                echo '<option value="'.$cat['id'].'">'.$cat['name'].'</option>';
                                                            }
                                                        } else {
                                                            echo '<option value="" disabled>Không có danh mục</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="weight">Trọng lượng</label>
                                                    <input type="text" class="form-control" id="weight" name="weight" placeholder="Ví dụ: 250g, 500g, 1kg">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stock">Số lượng tồn kho</label>
                                                    <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="image">Hình ảnh</label>
                                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                            <small class="form-text text-muted">Chọn ảnh JPG, PNG hoặc GIF (tối đa 5MB)</small>
                                            <img id="preview-image" src="#" alt="Preview">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Lưu sản phẩm
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function previewImage(input) {
            var preview = document.getElementById('preview-image');
            
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html> 