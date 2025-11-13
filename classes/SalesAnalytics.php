<?php
/**
 * Class SalesAnalytics - Phân tích doanh số bán hàng
 */
class SalesAnalytics {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Lấy doanh thu theo khoảng thời gian
     */
    public function getRevenueByDateRange($dateFrom, $dateTo) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE(order_date) as date,
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                SUM(discount_amount) as total_discount,
                SUM(total_amount - discount_amount) as net_revenue,
                COUNT(DISTINCT user_id) as total_customers
            FROM orders
            WHERE order_date BETWEEN ? AND ?
              AND status != 'cancelled'
            GROUP BY DATE(order_date)
            ORDER BY date ASC
        ");
        $stmt->bind_param("ss", $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Lấy top sản phẩm bán chạy
     */
    public function getTopProducts($dateFrom = null, $dateTo = null, $limit = 10) {
        $sql = "
            SELECT 
                p.id,
                p.name,
                p.price,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.price * oi.quantity) as total_revenue,
                COUNT(DISTINCT o.id) as order_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_name = p.name
            WHERE o.status != 'cancelled'
        ";
        
        $params = [];
        $types = "";
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND o.order_date BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $types .= "ss";
        }
        
        $sql .= "
            GROUP BY p.id, p.name, p.price
            ORDER BY total_quantity DESC, total_revenue DESC
            LIMIT ?
        ";
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }

    /**
     * Lấy top khách hàng theo doanh số
     */
    public function getTopCustomers($dateFrom = null, $dateTo = null, $limit = 10) {
        $sql = "
            SELECT 
                u.id,
                u.fullname,
                u.email,
                u.customer_level,
                COUNT(DISTINCT o.id) as total_orders,
                SUM(o.total_amount - COALESCE(o.discount_amount, 0)) as total_spent,
                AVG(o.total_amount - COALESCE(o.discount_amount, 0)) as avg_order_value
            FROM users u
            JOIN orders o ON u.id = o.user_id
            WHERE o.status != 'cancelled'
        ";
        
        $params = [];
        $types = "";
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND o.order_date BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $types .= "ss";
        }
        
        $sql .= "
            GROUP BY u.id, u.fullname, u.email, u.customer_level
            ORDER BY total_spent DESC
            LIMIT ?
        ";
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        
        return $customers;
    }

    /**
     * Lấy thống kê tổng quan
     */
    public function getSummaryStats($dateFrom = null, $dateTo = null) {
        $sql = "
            SELECT 
                COUNT(*) as total_orders,
                COUNT(DISTINCT user_id) as total_customers,
                SUM(total_amount) as total_revenue,
                SUM(COALESCE(discount_amount, 0)) as total_discount,
                SUM(total_amount - COALESCE(discount_amount, 0)) as net_revenue,
                AVG(total_amount - COALESCE(discount_amount, 0)) as avg_order_value
            FROM orders
            WHERE status != 'cancelled'
        ";
        
        $params = [];
        $types = "";
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND order_date BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $types .= "ss";
        }
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Lấy doanh thu theo tháng
     */
    public function getRevenueByMonth($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                MONTH(order_date) as month,
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                SUM(COALESCE(discount_amount, 0)) as total_discount,
                SUM(total_amount - COALESCE(discount_amount, 0)) as net_revenue
            FROM orders
            WHERE YEAR(order_date) = ?
              AND status != 'cancelled'
            GROUP BY MONTH(order_date)
            ORDER BY month ASC
        ");
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Lấy doanh thu theo danh mục
     */
    public function getRevenueByCategory($dateFrom = null, $dateTo = null) {
        $sql = "
            SELECT 
                c.id,
                c.name,
                COUNT(DISTINCT o.id) as total_orders,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.price * oi.quantity) as total_revenue
            FROM categories c
            JOIN products p ON c.id = p.category_id
            JOIN order_items oi ON p.name = oi.product_name
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled'
        ";
        
        $params = [];
        $types = "";
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND o.order_date BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $types .= "ss";
        }
        
        $sql .= "
            GROUP BY c.id, c.name
            ORDER BY total_revenue DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }
}

