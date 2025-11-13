<?php
/**
 * Class Customer - Quản lý thông tin khách hàng (CRM)
 */
class Customer {
    private $conn;
    private $id;
    private $username;
    private $email;
    private $fullname;
    private $phone;
    private $address;
    private $city;
    private $role;
    private $totalPoints;
    private $totalSpent;
    private $customerLevel;
    private $lastOrderDate;
    private $active;
    private $createdAt;

    public function __construct($conn, $userId = null) {
        $this->conn = $conn;
        if ($userId) {
            $this->loadCustomer($userId);
        }
    }

    /**
     * Load thông tin khách hàng từ database
     */
    public function loadCustomer($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->fullname = $row['fullname'];
            $this->phone = $row['phone'] ?? '';
            $this->address = $row['address'] ?? '';
            $this->city = $row['city'] ?? '';
            $this->role = $row['role'];
            $this->totalPoints = $row['total_points'] ?? 0;
            $this->totalSpent = $row['total_spent'] ?? 0;
            $this->customerLevel = $row['customer_level'] ?? 'bronze';
            $this->lastOrderDate = $row['last_order_date'] ?? null;
            $this->active = $row['active'] ?? 1;
            $this->createdAt = $row['created_at'];
            return true;
        }
        return false;
    }

    /**
     * Lấy lịch sử mua hàng
     */
    public function getOrderHistory($limit = 10, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT o.*, 
                   COUNT(oi.id) as item_count,
                   SUM(oi.quantity) as total_quantity
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.order_date DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $this->id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        return $orders;
    }

    /**
     * Đếm tổng số đơn hàng
     */
    public function getTotalOrders() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Cập nhật cấp độ khách hàng dựa trên tổng chi tiêu
     */
    public function updateCustomerLevel() {
        $level = 'bronze';
        
        if ($this->totalSpent >= 5000000) {
            $level = 'platinum';
        } elseif ($this->totalSpent >= 2000000) {
            $level = 'gold';
        } elseif ($this->totalSpent >= 500000) {
            $level = 'silver';
        }
        
        if ($this->customerLevel != $level) {
            $stmt = $this->conn->prepare("UPDATE users SET customer_level = ? WHERE id = ?");
            $stmt->bind_param("si", $level, $this->id);
            $stmt->execute();
            $this->customerLevel = $level;
        }
        
        return $level;
    }

    /**
     * Cập nhật tổng chi tiêu sau khi có đơn hàng mới
     */
    public function updateTotalSpent($amount) {
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET total_spent = total_spent + ?, 
                last_order_date = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("di", $amount, $this->id);
        $stmt->execute();
        $this->totalSpent += $amount;
        $this->updateCustomerLevel();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getFullname() { return $this->fullname; }
    public function getPhone() { return $this->phone; }
    public function getTotalPoints() { return $this->totalPoints; }
    public function getTotalSpent() { return $this->totalSpent; }
    public function getCustomerLevel() { return $this->customerLevel; }
    public function getCustomerLevelName() {
        $levels = [
            'bronze' => 'Đồng',
            'silver' => 'Bạc',
            'gold' => 'Vàng',
            'platinum' => 'Bạch Kim'
        ];
        return $levels[$this->customerLevel] ?? 'Đồng';
    }
}

