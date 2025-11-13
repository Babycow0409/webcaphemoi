                        <div class="card-header bg-success text-white">
                            <h5 class="m-0"><i class="fas fa-coffee mr-2"></i>Thống kê sản phẩm bán chạy</h5>
                        </div>
                        <div class="card-body">
                            <!-- Form lọc -->
                            <form method="GET" action="" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_from"><i class="far fa-calendar-alt mr-1"></i>Từ ngày</label>
                                            <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="<?php echo $date_from; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_to"><i class="far fa-calendar-alt mr-1"></i>Đến ngày</label>
                                            <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="<?php echo $date_to; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-success btn-sm d-block w-100">
                                                <i class="fas fa-search mr-1"></i>Thống kê
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <?php if (!$orders_exist): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>Bảng orders chưa tồn tại. Chưa có thông tin đơn hàng nào để thống kê.
                                </div>
                            <?php elseif (empty($product_data)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>Không có sản phẩm nào được bán trong khoảng thời gian từ <?php echo date('d/m/Y', strtotime($date_from)); ?> đến <?php echo date('d/m/Y', strtotime($date_to)); ?>.
                                </div>
                            <?php else: ?>
                                <!-- Biểu đồ -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card shadow-sm">
                                            <div class="card-body">
                                                <canvas id="productChart" height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center" width="10%">STT</th>
                                                <th width="40%">Tên sản phẩm</th>
                                                <th class="text-center" width="20%">Số lượng đã bán</th>
                                                <th class="text-right" width="30%">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_quantity = 0;
                                            $total_revenue = 0;
                                            foreach ($product_data as $index => $product): 
                                                $total_quantity += $product['quantity'];
                                                $total_revenue += $product['revenue'];
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <span class="font-weight-bold"><?php echo $product['name']; ?></span>
                                                        <div class="small text-muted">Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $product['quantity']; ?></span>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success">
                                                        <?php echo number_format($product['revenue'], 0, ',', '.'); ?> VNĐ
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="2" class="text-right">Tổng cộng:</td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary"><?php echo $total_quantity; ?></span>
                                                </td>
                                                <td class="text-right text-danger">
                                                    <?php echo number_format($total_revenue, 0, ',', '.'); ?> VNĐ
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const ctx = document.getElementById('productChart').getContext('2d');
                                    
                                    // Lấy top 10 sản phẩm để hiển thị biểu đồ
                                    const productData = <?php echo json_encode(array_slice($product_data, 0, 10)); ?>;
                                    const productNames = productData.map(product => product.name);
                                    const productQuantities = productData.map(product => product.quantity);
                                    const productRevenues = productData.map(product => product.revenue);
                                    
                                    const productChart = new Chart(ctx, {
                                        type: 'horizontalBar',
                                        data: {
                                            labels: productNames,
                                            datasets: [
                                                {
                                                    label: 'Doanh thu (VNĐ)',
                                                    data: productRevenues,
                                                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                                    borderColor: 'rgba(75, 192, 192, 1)',
                                                    borderWidth: 1,
                                                    yAxisID: 'y-axis-revenue'
                                                },
                                                {
                                                    label: 'Số lượng đã bán',
                                                    data: productQuantities,
                                                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                                                    borderColor: 'rgba(255, 159, 64, 1)',
                                                    borderWidth: 1,
                                                    yAxisID: 'y-axis-quantity'
                                                }
                                            ]
                                        },
                                        options: {
                                            responsive: true,
                                            title: {
                                                display: true,
                                                text: 'Top 10 sản phẩm bán chạy',
                                                fontSize: 16
                                            },
                                            tooltips: {
                                                mode: 'index',
                                                intersect: false,
                                                callbacks: {
                                                    label: function(tooltipItem, data) {
                                                        if (tooltipItem.datasetIndex === 0) {
                                                            return 'Doanh thu: ' + Number(tooltipItem.xLabel).toLocaleString('vi-VN') + ' VNĐ';
                                                        } else {
                                                            return 'Số lượng: ' + tooltipItem.xLabel;
                                                        }
                                                    }
                                                }
                                            },
                                            scales: {
                                                xAxes: [
                                                    {
                                                        id: 'x-axis-revenue',
                                                        type: 'linear',
                                                        position: 'bottom',
                                                        ticks: {
                                                            beginAtZero: true,
                                                            callback: function(value) {
                                                                if (value >= 1000000) {
                                                                    return (value / 1000000).toFixed(1) + 'M';
                                                                } else if (value >= 1000) {
                                                                    return (value / 1000).toFixed(1) + 'K';
                                                                }
                                                                return value;
                                                            }
                                                        }
                                                    }
                                                ],
                                                yAxes: [{
                                                    ticks: {
                                                        callback: function(value, index) {
                                                            // Cắt ngắn tên sản phẩm dài
                                                            const maxLength = 25;
                                                            return value.length > maxLength ? value.substr(0, maxLength) + '...' : value;
                                                        }
                                                    }
                                                }]
                                            },
                                            legend: {
                                                position: 'bottom'
                                            }
                                        }
                                    });
                                });
                                </script>
                            <?php endif; ?> 