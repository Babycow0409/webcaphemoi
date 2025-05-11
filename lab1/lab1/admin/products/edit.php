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

$error = '';
$success = '';
$product = [];

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Lấy thông tin sản phẩm
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?error=Không tìm thấy sản phẩm");
    exit();
}

$product = $result->fetch_assoc();

// Xử lý cập nhật sản phẩm
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category'];
    $weight = trim($_POST['weight'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    
    // Xử lý upload ảnh nếu có
    $image = $product['image']; // Giữ nguyên ảnh cũ nếu không có ảnh mới
    
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
            
            // Xóa ảnh cũ nếu có
            if (!empty($product['image']) && file_exists("../../" . $product['image'])) {
                unlink("../../" . $product['image']);
            }
        } else {
            $error = "Có lỗi xảy ra khi upload file.";
        }
    }
    
    // Cập nhật sản phẩm vào database
    if (empty($error)) {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, weight = ?, stock = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiissi", $name, $description, $price, $category_id, $weight, $stock, $image, $product_id);
        
        if ($stmt->execute()) {
            $success = "Cập nhật sản phẩm thành công!";
            // Cập nhật thông tin sản phẩm sau khi cập nhật
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            $error = "Lỗi khi cập nhật sản phẩm: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sản phẩm - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
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
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
            border-left: 3px solid #17a2b8;
        }
        .content {
            padding: 20px;
        }
        .header {
            background-color: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        #preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
        }
        .card-header {
            background-color: #17a2b8;
            color: white;
            font-weight: bold;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        .form-control:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }
        .btn-primary {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-primary:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .image-preview-container {
            border: 1px dashed #ced4da;
            padding: 15px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .required-field {
            color: #dc3545;
            margin-left: 4px;
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
                    <h2>Chỉnh sửa sản phẩm</h2>
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
                        <div class="card-header">
                            <i class="fas fa-edit mr-2"></i>Thông tin sản phẩm
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="name">Tên sản phẩm <span class="required-field">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="Nhập tên sản phẩm">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description">Mô tả sản phẩm</label>
                                            <textarea class="form-control" id="summernote" name="description" rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="price">Giá <span class="required-field">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="price" name="price" min="0" step="1000" required value="<?php echo $product['price']; ?>" placeholder="Nhập giá sản phẩm">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">VNĐ</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="category">Danh mục <span class="required-field">*</span></label>
                                                    <select class="form-control" id="category" name="category" required>
                                                        <option value="">-- Chọn danh mục --</option>
                                                        <?php
                                                        // Lấy danh sách danh mục từ database
                                                        $sql_categories = "SELECT * FROM categories ORDER BY name ASC";
                                                        $result_categories = $conn->query($sql_categories);
                                                        
                                                        if ($result_categories && $result_categories->num_rows > 0) {
                                                            while($cat = $result_categories->fetch_assoc()) {
                                                                $selected = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                                                                echo '<option value="'.$cat['id'].'" '.$selected.'>'.$cat['name'].'</option>';
                                                            }
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
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="weight" name="weight" placeholder="Ví dụ: 250g, 500g, 1kg" value="<?php echo htmlspecialchars($product['weight'] ?? ''); ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-weight"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stock">Số lượng tồn kho</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo $product['stock'] ?? 0; ?>" placeholder="Nhập số lượng">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <i class="fas fa-image mr-2"></i>Hình ảnh sản phẩm
                                            </div>
                                            <div class="card-body">
                                                <div class="image-preview-container">
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img id="preview-image" src="../../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="mb-2">
                                                    <?php else: ?>
                                                        <img id="preview-image" src="#" alt="Preview" style="display: none;" class="mb-2">
                                                        <p class="text-muted mb-0"><i class="fas fa-camera"></i> Chưa có hình ảnh</p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="custom-file mb-3">
                                                    <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                                    <label class="custom-file-label" for="image">Chọn ảnh</label>
                                                </div>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i>Chấp nhận các định dạng: JPG, PNG, GIF (tối đa 5MB)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save mr-2"></i> Cập nhật sản phẩm
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo Summernote
            $('#summernote').summernote({
                placeholder: 'Nhập mô tả chi tiết về sản phẩm',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        alert('Để thêm hình ảnh vào mô tả, vui lòng tải lên và chèn URL hình ảnh.');
                    }
                }
            });
            
            // Custom file input
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName || "Chọn ảnh");
            });
        });

        function previewImage(input) {
            var preview = document.getElementById('preview-image');
            
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    preview.parentElement.querySelector('p.text-muted')?.remove();
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                <?php if (empty($product['image'])): ?>
                preview.style.display = 'none';
                <?php endif; ?>
            }
        }
    </script>
</body>
</html> 