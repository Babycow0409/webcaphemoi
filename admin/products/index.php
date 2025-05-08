<?php
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