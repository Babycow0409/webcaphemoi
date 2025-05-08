<?php
include '../includes/header.php';

$categories = ['arabica', 'robusta', 'chon', 'other'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $weight = $_POST['weight'];
    $stock = $_POST['stock'];
    
    // Xử lý upload hình ảnh
    $target_dir = "../../uploads/products/";
    $image = "";
    
    // Tạo thư mục nếu chưa tồn tại
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $filename;
        
        // Kiểm tra loại file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            $error = 'Chỉ chấp nhận file hình ảnh (jpg, jpeg, png, gif)';
        } else {
            // Upload file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = 'uploads/products/' . $filename;
            } else {
                $error = 'Có lỗi khi upload hình ảnh';
            }
        }
    } else {
        $error = 'Vui lòng chọn hình ảnh cho sản phẩm';
    }
    
    // Nếu không có lỗi, thêm sản phẩm vào database
    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, weight, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssis", $name, $description, $price, $category, $weight, $stock, $image);
        
        if ($stmt->execute()) {
            $success = 'Thêm sản phẩm thành công';
        } else {
            $error = 'Có lỗi khi thêm sản phẩm: ' . $conn->error;
        }
    }
}
?>

<div class="content-header">
    <h1>Thêm sản phẩm mới</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post" action="" enctype="multipart/form-data">
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
                                <label for="price">Giá (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="arabica">Arabica</option>
                                    <option value="robusta">Robusta</option>
                                    <option value="chon">Chồn</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="weight">Trọng lượng</label>
                                <input type="text" class="form-control" id="weight" name="weight" placeholder="VD: 250g, 500g, 1kg">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock">Số lượng trong kho</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="image">Hình ảnh <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="image" name="image" required onchange="previewImage(event)">
                        <small class="form-text text-muted">Chọn hình ảnh định dạng JPG, PNG hoặc GIF</small>
                        
                        <div class="mt-3" id="imagePreviewContainer" style="display: none;">
                            <p>Xem trước:</p>
                            <img id="imagePreview" src="#" alt="Xem trước" style="max-width: 100%; max-height: 300px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Thêm sản phẩm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('imagePreview');
        output.src = reader.result;
        document.getElementById('imagePreviewContainer').style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include '../includes/footer.php'; ?> 