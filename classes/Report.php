<?php
require_once __DIR__ . '/SalesAnalytics.php';

/**
 * Class Report - Tạo và quản lý báo cáo
 */
class Report {
    private $conn;
    private $analytics;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->analytics = new SalesAnalytics($conn);
    }

    /**
     * Tạo báo cáo doanh thu hàng ngày
     */
    public function generateDailyReport($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $dateFrom = $date . ' 00:00:00';
        $dateTo = $date . ' 23:59:59';
        
        $stats = $this->analytics->getSummaryStats($dateFrom, $dateTo);
        $topProducts = $this->analytics->getTopProducts($dateFrom, $dateTo, 5);
        
        // Lưu vào bảng sales_reports
        $stmt = $this->conn->prepare("
            INSERT INTO sales_reports 
            (report_date, report_type, total_orders, total_revenue, total_discount, net_revenue, total_customers, top_product_id, top_product_name, top_product_quantity)
            VALUES (?, 'daily', ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                total_orders = VALUES(total_orders),
                total_revenue = VALUES(total_revenue),
                total_discount = VALUES(total_discount),
                net_revenue = VALUES(net_revenue),
                total_customers = VALUES(total_customers),
                top_product_id = VALUES(top_product_id),
                top_product_name = VALUES(top_product_name),
                top_product_quantity = VALUES(top_product_quantity),
                updated_at = NOW()
        ");
        
        $topProductId = !empty($topProducts) ? $topProducts[0]['id'] : null;
        $topProductName = !empty($topProducts) ? $topProducts[0]['name'] : null;
        $topProductQuantity = !empty($topProducts) ? $topProducts[0]['total_quantity'] : 0;
        
        $stmt->bind_param(
            "siidddiiis",
            $date,
            $stats['total_orders'],
            $stats['total_revenue'],
            $stats['total_discount'],
            $stats['net_revenue'],
            $stats['total_customers'],
            $topProductId,
            $topProductName,
            $topProductQuantity
        );
        
        $stmt->execute();
        
        return [
            'date' => $date,
            'stats' => $stats,
            'top_products' => $topProducts
        ];
    }

    /**
     * Xuất báo cáo ra Excel (CSV format)
     */
    public function exportToCSV($dateFrom, $dateTo, $reportType = 'revenue') {
        $filename = "report_" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM để hiển thị tiếng Việt đúng trong Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if ($reportType == 'revenue') {
            // Header
            fputcsv($output, ['Ngày', 'Số đơn hàng', 'Doanh thu', 'Giảm giá', 'Doanh thu thuần', 'Số khách hàng']);
            
            // Data
            $data = $this->analytics->getRevenueByDateRange($dateFrom, $dateTo);
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['date'],
                    $row['total_orders'],
                    number_format($row['total_revenue'], 0, ',', '.'),
                    number_format($row['total_discount'], 0, ',', '.'),
                    number_format($row['net_revenue'], 0, ',', '.'),
                    $row['total_customers']
                ]);
            }
        } elseif ($reportType == 'products') {
            // Header
            fputcsv($output, ['STT', 'Tên sản phẩm', 'Giá', 'Số lượng bán', 'Doanh thu', 'Số đơn hàng']);
            
            // Data
            $products = $this->analytics->getTopProducts($dateFrom, $dateTo, 100);
            $index = 1;
            foreach ($products as $product) {
                fputcsv($output, [
                    $index++,
                    $product['name'],
                    number_format($product['price'], 0, ',', '.'),
                    $product['total_quantity'],
                    number_format($product['total_revenue'], 0, ',', '.'),
                    $product['order_count']
                ]);
            }
        } elseif ($reportType == 'customers') {
            // Header
            fputcsv($output, ['STT', 'Khách hàng', 'Email', 'Cấp độ', 'Số đơn', 'Tổng chi tiêu', 'Giá trị đơn TB']);
            
            // Data
            $customers = $this->analytics->getTopCustomers($dateFrom, $dateTo, 100);
            $index = 1;
            foreach ($customers as $customer) {
                fputcsv($output, [
                    $index++,
                    $customer['fullname'],
                    $customer['email'],
                    $customer['customer_level'],
                    $customer['total_orders'],
                    number_format($customer['total_spent'], 0, ',', '.'),
                    number_format($customer['avg_order_value'], 0, ',', '.')
                ]);
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Lấy báo cáo từ bảng sales_reports
     */
    public function getReport($reportDate, $reportType = 'daily') {
        $stmt = $this->conn->prepare("
            SELECT * FROM sales_reports 
            WHERE report_date = ? AND report_type = ?
        ");
        $stmt->bind_param("ss", $reportDate, $reportType);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}

