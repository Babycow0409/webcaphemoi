<?php
<<<<<<< HEAD
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

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Lấy thông tin sản phẩm trước khi xóa
$sql_select = "SELECT * FROM products WHERE id = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $product_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();
$product = $result_select->fetch_assoc();

// Xóa sản phẩm
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    // Xóa file hình ảnh nếu có
    if (!empty($product['image']) && file_exists("../../" . $product['image'])) {
        unlink("../../" . $product['image']);
    }
    header("Location: index.php?message=Sản phẩm đã được xóa thành công");
} else {
    header("Location: index.php?error=Có lỗi xảy ra khi xóa sản phẩm: " . $conn->error);
}

exit();
?> 
=======
include '../includes/header.php';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];
$error = '';
$success = '';

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

// Xử lý xóa sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Kiểm tra xem sản phẩm có đang được sử dụng trong đơn hàng không
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $error = 'Không thể xóa sản phẩm này vì đã có trong đơn hàng. Hãy cân nhắc ẩn sản phẩm thay vì xóa.';
    } else {
        // Xóa sản phẩm
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            // Xóa ảnh sản phẩm nếu có
            if (!empty($product['image']) && file_exists("../../" . $product['image'])) {
                unlink("../../" . $product['image']);
            }
            
            $success = 'Xóa sản phẩm thành công';
            
            // Chuyển hướng sau khi xóa thành công
            header("Refresh: 2; URL=index.php");
        } else {
            $error = 'Có lỗi khi xóa sản phẩm: ' . $conn->error;
        }
    }
}
?>

<div class="content-header">
    <h1>Xóa sản phẩm</h1>
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
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>Cảnh báo:</strong> Bạn đang chuẩn bị xóa sản phẩm này. Hành động này không thể hoàn tác.
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <img src="../../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid">
                </div>
                <div class="col-md-9">
                    <h3><?php echo $product['name']; ?></h3>
                    <p><strong>Danh mục:</strong> 
                        <?php
                            switch ($product['category']) {
                                case 'arabica':
                                    echo 'Arabica';
                                    break;
                                case 'robusta':
                                    echo 'Robusta';
                                    break;
                                case 'chon':
                                    echo 'Chồn';
                                    break;
                                default:
                                    echo 'Khác';
                            }
                        ?>
                    </p>
                    <p><strong>Giá:</strong> <?php echo number_format($product['price'], 0, ',', '.'); ?>đ</p>
                    <p><strong>Trọng lượng:</strong> <?php echo $product['weight']; ?></p>
                    <p><strong>Tồn kho:</strong> <?php echo $product['stock']; ?></p>
                </div>
            </div>
            
            <form method="post" action="">
                <div class="form-group">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xác nhận xóa sản phẩm
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 
>>>>>>> 36298127f1deb9f590e3222166af251932b728fd
