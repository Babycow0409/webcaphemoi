<?php
include '../includes/header.php';

$categories = ['arabica', 'robusta', 'chon', 'other'];
$error = '';
$success = '';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$product = $result->fetch_assoc();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $weight = $_POST['weight'];
    $stock = $_POST['stock'];
    
    // Xử lý upload hình ảnh nếu có
    $image = $product['image']; // Giữ nguyên ảnh cũ nếu không upload ảnh mới
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../uploads/products/";
        
        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
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
                
                // Xóa ảnh cũ nếu tồn tại và không phải là ảnh mặc định
                if (!empty($product['image']) && file_exists("../../" . $product['image'])) {
                    unlink("../../" . $product['image']);
                }
            } else {
                $error = 'Có lỗi khi upload hình ảnh';
            }
        }
    }
    
    // Nếu không có lỗi, cập nhật sản phẩm
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, weight = ?, stock = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssdssisi", $name, $description, $price, $category, $weight, $stock, $image, $product_id);
        
        if ($stmt->execute()) {
            $success = 'Cập nhật sản phẩm thành công';
            // Lấy lại thông tin sản phẩm sau khi cập nhật
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            $error = 'Có lỗi khi cập nhật sản phẩm: ' . $conn->error;
        }
    }
}
?>

<div class="content-header">
    <h1>Chỉnh sửa sản phẩm</h1>
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
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Giá (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" min="0" value="<?php echo $product['price']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="arabica" <?php echo $product['category'] == 'arabica' ? 'selected' : ''; ?>>Arabica</option>
                                    <option value="robusta" <?php echo $product['category'] == 'robusta' ? 'selected' : ''; ?>>Robusta</option>
                                    <option value="chon" <?php echo $product['category'] == 'chon' ? 'selected' : ''; ?>>Chồn</option>
                                    <option value="other" <?php echo $product['category'] == 'other' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="weight">Trọng lượng</label>
                                <input type="text" class="form-control" id="weight" name="weight" value="<?php echo htmlspecialchars($product['weight']); ?>" placeholder="VD: 250g, 500g, 1kg">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock">Số lượng trong kho</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="image">Hình ảnh</label>
                        <input type="file" class="form-control-file" id="image" name="image" onchange="previewImage(event)">
                        <small class="form-text text-muted">Chọn hình ảnh mới để thay thế (nếu cần)</small>
                        
                        <div class="mt-3" id="imagePreviewContainer">
                            <p>Hình ảnh hiện tại:</p>
                            <img id="currentImage" src="../../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="max-width: 100%; max-height: 300px;">
                        </div>
                        
                        <div class="mt-3" id="newImagePreviewContainer" style="display: none;">
                            <p>Hình ảnh mới:</p>
                            <img id="imagePreview" src="#" alt="Xem trước" style="max-width: 100%; max-height: 300px;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật sản phẩm
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
        document.getElementById('newImagePreviewContainer').style.display = 'block';
        document.getElementById('currentImage').style.opacity = '0.5';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include '../includes/footer.php'; ?> 