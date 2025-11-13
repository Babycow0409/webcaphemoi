<?php
include 'includes/db_connect.php';
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Xử lý phân trang
$productsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

// Xử lý tìm kiếm nâng cao
$searchConditions = [];
$params = [];
$types = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchConditions[] = "name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $types .= 's';
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $searchConditions[] = "category_id = ?";
    $params[] = $_GET['category'];
    $types .= 'i';
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $searchConditions[] = "price >= ?";
    $params[] = $_GET['min_price'];
    $types .= 'd';
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $searchConditions[] = "price <= ?";
    $params[] = $_GET['max_price'];
    $types .= 'd';
}

// Xây dựng truy vấn SQL
$whereClause = !empty($searchConditions) ? " WHERE " . implode(" AND ", $searchConditions) : "";
$countQuery = "SELECT COUNT(*) as total FROM products" . $whereClause;
$productsQuery = "SELECT * FROM products" . $whereClause . " LIMIT ? OFFSET ?";

// Đếm tổng số sản phẩm
$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalResult = $stmt->get_result();
$totalProducts = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// Lấy sản phẩm theo phân trang
$stmt = $conn->prepare($productsQuery);
if (!empty($params)) {
    $params[] = $productsPerPage;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $productsPerPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm nâng cao</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f8f8;
            border-radius: 5px;
        }
        
        .search-form input, .search-form select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Tìm kiếm nâng cao</h1>
        
        <form class="search-form" method="GET" action="advanced-search.php">
            <div>
                <label for="search">Tên sản phẩm:</label>
                <input type="text" id="search" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>
            
            <div>
                <label for="category">Phân loại:</label>
                <select id="category" name="category">
                    <option value="">Tất cả phân loại</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="min_price">Giá từ:</label>
                <input type="number" id="min_price" name="min_price" min="0" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
            </div>
            
            <div>
                <label for="max_price">Đến:</label>
                <input type="number" id="max_price" name="max_price" min="0" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
            </div>
            
            <button type="submit">Tìm kiếm</button>
        </form>
        
        <div class="products">
            <?php if (empty($products)): ?>
                <p>Không tìm thấy sản phẩm nào.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
                        <div class="product-buttons">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn view-detail-btn">Xem chi tiết</a>
                            <a href="add-to-cart.php?id=<?php echo urlencode($product['id']); ?>&name=<?php echo urlencode($product['name']); ?>&price=<?php echo urlencode($product['price']); ?>&image=<?php echo urlencode($product['image']); ?>&quantity=1" class="btn add-to-cart-btn">
                                Thêm vào giỏ
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search='.htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category='.htmlspecialchars($_GET['category']) : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price='.htmlspecialchars($_GET['min_price']) : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price='.htmlspecialchars($_GET['max_price']) : ''; ?>" <?php echo ($page == $i) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/cart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cập nhật số lượng sản phẩm trong giỏ hàng
            updateCartCount();
        });
    </script>
</body>
</html>
