<?php
$page_title = "Quản lý đơn hàng";
include '../includes/header.php';

// Xử lý lọc đơn hàng
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';

// Xây dựng câu truy vấn
$sql = "SELECT o.*, u.fullname, u.city FROM orders o JOIN users u ON o.user_id = u.id";
$where_clauses = [];
$params = [];
$types = "";

// Lọc theo trạng thái
if (!empty($status_filter)) {
    $where_clauses[] = "o.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Lọc theo khoảng thời gian
if (!empty($from_date)) {
    $where_clauses[] = "DATE(o.created_at) >= ?";
    $params[] = $from_date;
    $types .= "s";
}

if (!empty($to_date)) {
    $where_clauses[] = "DATE(o.created_at) <= ?";
    $params[] = $to_date;
    $types .= "s";
}

// Lọc theo thành phố
if (!empty($city)) {
    $where_clauses[] = "u.city LIKE ?";
    $params[] = "%$city%";
    $types .= "s";
}

// Thêm điều kiện WHERE nếu có
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY o.created_at DESC";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách các thành phố để tạo dropdown
$cities = $conn->query("SELECT DISTINCT city FROM users WHERE city IS NOT NULL AND city != '' ORDER BY city");
?>

<h2>Quản lý đơn hàng</h2>
<p>Nội dung quản lý đơn hàng sẽ được hiển thị ở đây.</p>

<!-- Form lọc đơn hàng -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Trạng thái đơn hàng</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Tất cả</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="shipping" <?php echo $status_filter == 'shipping' ? 'selected' : ''; ?>>Đang giao hàng</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="from_date">Từ ngày</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="<?php echo $from_date; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="to_date">Đến ngày</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="<?php echo $to_date; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="city">Thành phố</label>
                        <select class="form-control" id="city" name="city">
                            <option value="">Tất cả</option>
                            <?php while ($cityRow = $cities->fetch_assoc()): ?>
                                <option value="<?php echo $cityRow['city']; ?>" <?php echo $city == $cityRow['city'] ? 'selected' : ''; ?>>
                                    <?php echo $cityRow['city']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-sync"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách đơn hàng -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Địa chỉ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <?php echo $order['order_number'] ?? 'ORDER' . $order['id']; ?>
                                </td>
                                <td><?php echo $order['fullname']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <?php
                                        switch ($order['status']) {
                                            case 'pending':
                                                echo '<span class="badge badge-warning">Chờ xử lý</span>';
                                                break;
                                            case 'processing':
                                                echo '<span class="badge badge-info">Đang xử lý</span>';
                                                break;
                                            case 'shipping':
                                                echo '<span class="badge badge-primary">Đang giao</span>';
                                                break;
                                            case 'completed':
                                                echo '<span class="badge badge-success">Hoàn thành</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="badge badge-danger">Đã hủy</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Không xác định</span>';
                                        }
                                    ?>
                                </td>
                                <td><?php echo $order['city']; ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có đơn hàng nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 