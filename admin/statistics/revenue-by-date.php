                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0"><i class="fas fa-chart-line mr-2"></i>Thống kê doanh thu theo ngày</h5>
                        </div>
                        <div class="card-body">
                            <!-- Form lọc theo khoảng thời gian -->
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
                                            <button type="submit" class="btn btn-primary btn-sm d-block w-100">
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
                            <?php elseif (empty($daily_revenue)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>Không có đơn hàng nào trong khoảng thời gian từ <?php echo date('d/m/Y', strtotime($date_from)); ?> đến <?php echo date('d/m/Y', strtotime($date_to)); ?>.
                                </div>
                            <?php else: ?>
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card shadow-sm">
                                            <div class="card-body">
                                                <canvas id="revenueChart" height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center" width="10%">STT</th>
                                                <th width="25%">Ngày</th>
                                                <th class="text-center" width="25%">Số đơn hàng</th>
                                                <th class="text-right" width="40%">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_revenue = 0;
                                            $total_orders = 0;
                                            foreach ($daily_revenue as $index => $data): 
                                                $total_revenue += $data['revenue'];
                                                $total_orders += $data['order_count'];
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $index + 1; ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($data['date'])); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?php echo $data['order_count']; ?></span>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success">
                                                        <?php echo number_format($data['revenue'], 0, ',', '.'); ?> VNĐ
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="2" class="text-right">Tổng cộng:</td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary"><?php echo $total_orders; ?></span>
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
                                        const ctx = document.getElementById('revenueChart').getContext('2d');
                                        
                                        // Dữ liệu từ PHP
                                        const dates = <?php echo json_encode(array_column($daily_revenue, 'date')); ?>;
                                        const revenues = <?php echo json_encode(array_column($daily_revenue, 'revenue')); ?>;
                                        const orderCounts = <?php echo json_encode(array_column($daily_revenue, 'order_count')); ?>;
                                        
                                        // Format lại ngày hiển thị
                                        const formattedDates = dates.map(date => {
                                            const d = new Date(date);
                                            return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear();
                                        });
                                        
                                        const revenueChart = new Chart(ctx, {
                                            type: 'bar',
                                            data: {
                                                labels: formattedDates,
                                                datasets: [
                                                    {
                                                        label: 'Doanh thu (VNĐ)',
                                                        data: revenues,
                                                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                                        borderColor: 'rgba(54, 162, 235, 1)',
                                                        borderWidth: 1,
                                                        yAxisID: 'y-axis-revenue'
                                                    },
                                                    {
                                                        label: 'Số đơn hàng',
                                                        data: orderCounts,
                                                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                                        borderColor: 'rgba(255, 99, 132, 1)',
                                                        borderWidth: 1,
                                                        type: 'line',
                                                        yAxisID: 'y-axis-orders'
                                                    }
                                                ]
                                            },
                                            options: {
                                                responsive: true,
                                                title: {
                                                    display: true,
                                                    text: 'Thống kê doanh thu và số đơn hàng theo ngày',
                                                    fontSize: 16
                                                },
                                                tooltips: {
                                                    mode: 'index',
                                                    intersect: false,
                                                    callbacks: {
                                                        label: function(tooltipItem, data) {
                                                            if (tooltipItem.datasetIndex === 0) {
                                                                return 'Doanh thu: ' + Number(tooltipItem.yLabel).toLocaleString('vi-VN') + ' VNĐ';
                                                            } else {
                                                                return 'Số đơn hàng: ' + tooltipItem.yLabel;
                                                            }
                                                        }
                                                    }
                                                },
                                                scales: {
                                                    yAxes: [
                                                        {
                                                            id: 'y-axis-revenue',
                                                            type: 'linear',
                                                            position: 'left',
                                                            ticks: {
                                                                beginAtZero: true,
                                                                callback: function(value) {
                                                                    return value.toLocaleString('vi-VN') + ' VNĐ';
                                                                }
                                                            },
                                                            scaleLabel: {
                                                                display: true,
                                                                labelString: 'Doanh thu (VNĐ)'
                                                            }
                                                        },
                                                        {
                                                            id: 'y-axis-orders',
                                                            type: 'linear',
                                                            position: 'right',
                                                            ticks: {
                                                                beginAtZero: true,
                                                                stepSize: 1
                                                            },
                                                            scaleLabel: {
                                                                display: true,
                                                                labelString: 'Số đơn hàng'
                                                            }
                                                        }
                                                    ]
                                                }
                                            }
                                        });
                                    });
                                </script>
                            <?php endif; ?> 