<?php
/**
 * Class Promotion - Quản lý khuyến mãi
 */
class Promotion {
    private $conn;
    private $id;
    private $code;
    private $name;
    private $description;
    private $discountType;
    private $discountValue;
    private $minOrderAmount;
    private $maxDiscountAmount;
    private $usageLimit;
    private $usedCount;
    private $userLimit;
    private $startDate;
    private $endDate;
    private $status;
    private $applicableProducts;
    private $applicableCategories;

    public function __construct($conn, $promotionId = null) {
        $this->conn = $conn;
        if ($promotionId) {
            $this->loadPromotion($promotionId);
        }
    }

    /**
     * Load thông tin khuyến mãi từ database
     */
    public function loadPromotion($promotionId) {
        $stmt = $this->conn->prepare("SELECT * FROM promotions WHERE id = ?");
        $stmt->bind_param("i", $promotionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->code = $row['code'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->discountType = $row['discount_type'];
            $this->discountValue = $row['discount_value'];
            $this->minOrderAmount = $row['min_order_amount'];
            $this->maxDiscountAmount = $row['max_discount_amount'];
            $this->usageLimit = $row['usage_limit'];
            $this->usedCount = $row['used_count'];
            $this->userLimit = $row['user_limit'];
            $this->startDate = $row['start_date'];
            $this->endDate = $row['end_date'];
            $this->status = $row['status'];
            $this->applicableProducts = json_decode($row['applicable_products'] ?? '[]', true);
            $this->applicableCategories = json_decode($row['applicable_categories'] ?? '[]', true);
            return true;
        }
        return false;
    }

    /**
     * Load khuyến mãi theo mã code
     */
    public function loadByCode($code) {
        $stmt = $this->conn->prepare("SELECT * FROM promotions WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $this->loadPromotion($row['id']);
        }
        return false;
    }

    /**
     * Kiểm tra khuyến mãi có hợp lệ không
     */
    public function isValid($userId = null, $orderAmount = 0, $cartItems = []) {
        // Kiểm tra trạng thái
        if ($this->status !== 'active') {
            return ['valid' => false, 'message' => 'Mã khuyến mãi không còn hiệu lực'];
        }

        // Kiểm tra thời gian
        $now = date('Y-m-d H:i:s');
        if ($now < $this->startDate || $now > $this->endDate) {
            return ['valid' => false, 'message' => 'Mã khuyến mãi đã hết hạn hoặc chưa có hiệu lực'];
        }

        // Kiểm tra đơn hàng tối thiểu
        if ($orderAmount < $this->minOrderAmount) {
            return [
                'valid' => false, 
                'message' => 'Đơn hàng tối thiểu ' . number_format($this->minOrderAmount, 0, ',', '.') . ' VNĐ'
            ];
        }

        // Kiểm tra giới hạn sử dụng
        if ($this->usageLimit && $this->usedCount >= $this->usageLimit) {
            return ['valid' => false, 'message' => 'Mã khuyến mãi đã hết lượt sử dụng'];
        }

        // Kiểm tra giới hạn sử dụng cho user
        if ($userId && $this->userLimit) {
            $userUsageCount = $this->getUserUsageCount($userId);
            if ($userUsageCount >= $this->userLimit) {
                return ['valid' => false, 'message' => 'Bạn đã sử dụng hết lượt khuyến mãi này'];
            }
        }

        // Kiểm tra sản phẩm áp dụng
        if (!empty($this->applicableProducts) || !empty($this->applicableCategories)) {
            $applicable = false;
            foreach ($cartItems as $item) {
                if (in_array($item['id'], $this->applicableProducts)) {
                    $applicable = true;
                    break;
                }
                // Kiểm tra category (cần query thêm)
            }
            if (!$applicable) {
                return ['valid' => false, 'message' => 'Mã khuyến mãi không áp dụng cho sản phẩm trong giỏ hàng'];
            }
        }

        return ['valid' => true, 'message' => 'Mã khuyến mãi hợp lệ'];
    }

    /**
     * Tính số tiền giảm giá
     */
    public function calculateDiscount($orderAmount) {
        $discount = 0;

        switch ($this->discountType) {
            case 'percentage':
                $discount = ($orderAmount * $this->discountValue) / 100;
                if ($this->maxDiscountAmount && $discount > $this->maxDiscountAmount) {
                    $discount = $this->maxDiscountAmount;
                }
                break;
                
            case 'fixed':
                $discount = $this->discountValue;
                if ($discount > $orderAmount) {
                    $discount = $orderAmount;
                }
                break;
                
            case 'free_shipping':
                // Miễn phí vận chuyển - xử lý riêng
                $discount = 0; // Sẽ trừ vào phí ship
                break;
        }

        return round($discount, 2);
    }

    /**
     * Áp dụng khuyến mãi cho đơn hàng
     */
    public function applyPromotion($userId, $orderId, $orderAmount, $discountAmount) {
        // Lưu lịch sử sử dụng
        $stmt = $this->conn->prepare("
            INSERT INTO promotion_usage 
            (promotion_id, user_id, order_id, discount_amount, order_amount)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiidd", $this->id, $userId, $orderId, $discountAmount, $orderAmount);
        
        if ($stmt->execute()) {
            // Cập nhật số lần sử dụng
            $updateStmt = $this->conn->prepare("
                UPDATE promotions 
                SET used_count = used_count + 1
                WHERE id = ?
            ");
            $updateStmt->bind_param("i", $this->id);
            $updateStmt->execute();
            
            // Cập nhật đơn hàng
            $orderUpdateStmt = $this->conn->prepare("
                UPDATE orders 
                SET promotion_id = ?, discount_amount = ?
                WHERE id = ?
            ");
            $orderUpdateStmt->bind_param("idi", $this->id, $discountAmount, $orderId);
            $orderUpdateStmt->execute();
            
            return true;
        }
        
        return false;
    }

    /**
     * Đếm số lần user đã sử dụng mã này
     */
    private function getUserUsageCount($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM promotion_usage 
            WHERE promotion_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $this->id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getCode() { return $this->code; }
    public function getName() { return $this->name; }
    public function getDiscountType() { return $this->discountType; }
    public function getDiscountValue() { return $this->discountValue; }
}

