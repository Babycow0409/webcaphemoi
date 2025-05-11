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

// Thêm đoạn sau vào phần header của trang index.php
if (isset($_GET['message'])): ?>
    <div class="alert alert-success">
        <?php echo $_GET['message']; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php echo $_GET['error']; ?>
    </div>
<?php endif;

// Lấy danh sách sản phẩm với thông tin danh mục
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$result = $conn->query($sql);
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
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
                    <h2>Quản lý sản phẩm</h2>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm sản phẩm mới
                    </a>
                </div>
                
                <div class="content">
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_GET['message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Danh sách sản phẩm</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hình ảnh</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Giá</th>
                                            <th>Danh mục</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($products) > 0): ?>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td><?php echo $product['id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($product['image'])): ?>
                                                            <img src="../../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="50">
                                                        <?php else: ?>
                                                            <span class="text-muted">Không có ảnh</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $product['name']; ?></td>
                                                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                                                    <td><?php echo $product['category_name']; ?></td>
                                                    <td>
                                                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-edit"></i> Sửa
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                                           onclick="return confirmDelete(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" 
                                                           class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có sản phẩm nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
    function confirmDelete(id, name) {
        return confirm(`Bạn có chắc chắn muốn xóa sản phẩm "${name}" không?\n\nCảnh báo: Việc này sẽ làm các liên kết đến sản phẩm này không còn hoạt động và người dùng có thể gặp lỗi 404.`);
    }
    </script>
</body>
</html> 
=======
$page_title = "Quản lý sản phẩm";
include '../includes/header.php';

// Xử lý lọc theo danh mục
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Xây dựng câu truy vấn
$sql = "SELECT * FROM products";
$params = [];
$types = "";

if (!empty($category_filter)) {
    $sql .= " WHERE category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="content-header">
    <h1>Quản lý sản phẩm</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm sản phẩm
    </a>
</div>

<!-- Lọc sản phẩm -->
<div class="card mb-4">
    <div class="card-body">
        <form class="form-inline" action="" method="get">
            <div class="form-group mr-2">
                <label for="category" class="mr-2">Danh mục:</label>
                <select class="form-control" id="category" name="category">
                    <option value="">Tất cả</option>
                    <option value="arabica" <?php echo $category_filter == 'arabica' ? 'selected' : ''; ?>>Arabica</option>
                    <option value="robusta" <?php echo $category_filter == 'robusta' ? 'selected' : ''; ?>>Robusta</option>
                    <option value="chon" <?php echo $category_filter == 'chon' ? 'selected' : ''; ?>>Chồn</option>
                    <option value="other" <?php echo $category_filter == 'other' ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Lọc</button>
        </form>
    </div>
</div>

<!-- Danh sách sản phẩm -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Trọng lượng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" height="50">
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td>
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
                            </td>
                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                            <td><?php echo $product['weight']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $product['id']; ?>)" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(productId) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        window.location.href = 'delete.php?id=' + productId;
    }
}
</script>

<?php include '../includes/footer.php'; ?> 
>>>>>>> 36298127f1deb9f590e3222166af251932b728fd
