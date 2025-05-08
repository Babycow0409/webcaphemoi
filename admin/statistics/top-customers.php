<?php
$page_title = "Thống kê top khách hàng";
include '../includes/header.php';

// Xử lý form thống kê
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$top_customers = [];

if (!empty($start_date) || !empty($end_date)) {
    // Xây dựng câu truy vấn thống kê top 5 khách hàng có tổng mua cao nhất
    $sql = "SELECT u.id, u.fullname, u.email, u.phone, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
           FROM users u 
           JOIN orders o ON u.id = o.user_id 
           WHERE o.status = 'completed'";
    
    $params = [];
    $types = "";
    
    // Thêm điều kiện khoảng thời gian
    if (!empty($start_date)) {
        $sql .= " AND DATE(o.created_at) >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    
    if (!empty($end_date)) {
        $sql .= " AND DATE(o.created_at) <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    
    $sql .= " GROUP BY u.id 
             ORDER BY total_spent DESC 
             LIMIT 5";
    
    // Thực thi truy vấn
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $top_customers = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="content-header">
    <h1>Top khách hàng mua nhiều nhất</h1>
</div>

<!-- Form chọn khoảng thời gian -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="start_date">Từ ngày</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="end_date">Đến ngày</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Thống kê
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Kết quả thống kê -->
<?php if (count($top_customers) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Top 5 khách hàng có mức mua hàng cao nhất
                <?php if (!empty($start_date) && !empty($end_date)): ?>
                    từ <?php echo date('d/m/Y', strtotime($start_date)); ?> đến <?php echo date('d/m/Y', strtotime($end_date)); ?>
                <?php elseif (!empty($start_date)): ?>
                    từ <?php echo date('d/m/Y', strtotime($start_date)); ?>
                <?php elseif (!empty($end_date)): ?>
                    đến <?php echo date('d/m/Y', strtotime($end_date)); ?>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Thứ hạng</th>
                            <th>Khách hàng</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Số đơn hàng</th>
                            <th>Tổng tiền mua</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_customers as $index => $customer): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $customer['fullname']; ?></td>
                                <td><?php echo $customer['email']; ?></td>
                                <td><?php echo $customer['phone']; ?></td>
                                <td><?php echo $customer['order_count']; ?> đơn</td>
                                <td><?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" onclick="showOrderDetails(<?php echo $customer['id']; ?>)">
                                        <i class="fas fa-list"></i> Xem chi tiết
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Chi tiết đơn hàng của khách hàng (Modal) -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Chi tiết đơn hàng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Đang tải dữ liệu...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && (!empty($start_date) || !empty($end_date))): ?>
    <div class="alert alert-info">
        Không tìm thấy thông tin khách hàng nào trong khoảng thời gian đã chọn.
    </div>
<?php endif; ?>

<script>
function showOrderDetails(userId) {
    // Hiển thị modal
    $('#orderDetailsModal').modal('show');
    
    // Lấy dữ liệu về các đơn hàng của khách hàng
    $.ajax({
        url: 'get_customer_orders.php',
        type: 'GET',
        data: {
            user_id: userId,
            start_date: '<?php echo $start_date; ?>',
            end_date: '<?php echo $end_date; ?>'
        },
        success: function(response) {
            $('#orderDetailsContent').html(response);
        },
        error: function() {
            $('#orderDetailsContent').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu.</div>');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?> 