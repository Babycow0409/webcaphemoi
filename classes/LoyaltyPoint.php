<?php
/**
 * Class LoyaltyPoint - Quản lý hệ thống tích điểm
 */
class LoyaltyPoint {
    private $conn;
    
    // Cấu hình tích điểm
    const POINTS_PER_1000_VND = 1; // 1 điểm cho mỗi 1000 VNĐ
    const POINTS_EXPIRY_MONTHS = 12; // Điểm hết hạn sau 12 tháng
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Tích điểm cho khách hàng khi mua hàng
     */
    public function earnPoints($userId, $orderId, $orderAmount, $description = null) {
        // Tính số điểm tích lũy: 1 điểm cho mỗi 1000 VNĐ
        $points = floor($orderAmount / 1000) * self::POINTS_PER_1000_VND;
        
        if ($points <= 0) {
            return false;
        }
        
        // Tính ngày hết hạn (12 tháng sau)
        $expiryDate = date('Y-m-d', strtotime('+' . self::POINTS_EXPIRY_MONTHS . ' months'));
        
        // Lưu điểm tích lũy
        $stmt = $this->conn->prepare("
            INSERT INTO loyalty_points 
            (user_id, points, points_available, order_id, transaction_type, description, expiry_date)
            VALUES (?, ?, ?, ?, 'earned', ?, ?)
        ");
        $stmt->bind_param("iiiiss", $userId, $points, $points, $orderId, $description, $expiryDate);
        
        if ($stmt->execute()) {
            // Cập nhật tổng điểm trong bảng users
            $this->updateUserTotalPoints($userId, $points);
            
            // Cập nhật points_earned trong đơn hàng
            $updateStmt = $this->conn->prepare("UPDATE orders SET points_earned = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $points, $orderId);
            $updateStmt->execute();
            
            return $points;
        }
        
        return false;
    }

    /**
     * Sử dụng điểm để giảm giá
     */
    public function usePoints($userId, $orderId, $pointsToUse, $discountAmount) {
        // Kiểm tra điểm có đủ không
        $availablePoints = $this->getAvailablePoints($userId);
        
        if ($availablePoints < $pointsToUse) {
            return false;
        }
        
        // Lưu giao dịch sử dụng điểm
        $stmt = $this->conn->prepare("
            INSERT INTO loyalty_points 
            (user_id, points_used, points_available, order_id, transaction_type, description)
            VALUES (?, ?, ?, ?, 'used', ?)
        ");
        
        // Tính điểm available sau khi sử dụng (âm để trừ)
        $negativePoints = -$pointsToUse;
        $description = "Sử dụng {$pointsToUse} điểm để giảm " . number_format($discountAmount, 0, ',', '.') . " VNĐ";
        
        $stmt->bind_param("iiiis", $userId, $pointsToUse, $negativePoints, $orderId, $description);
        
        if ($stmt->execute()) {
            // Cập nhật tổng điểm trong bảng users
            $this->updateUserTotalPoints($userId, -$pointsToUse);
            
            // Cập nhật points_used trong đơn hàng
            $updateStmt = $this->conn->prepare("UPDATE orders SET points_used = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $pointsToUse, $orderId);
            $updateStmt->execute();
            
            return true;
        }
        
        return false;
    }

    /**
     * Lấy số điểm có sẵn của khách hàng
     */
    public function getAvailablePoints($userId) {
        // Lấy điểm từ bảng users (tổng điểm)
        $stmt = $this->conn->prepare("SELECT total_points FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['total_points'];
        }
        
        return 0;
    }

    /**
     * Lấy lịch sử tích điểm
     */
    public function getPointHistory($userId, $limit = 20) {
        $stmt = $this->conn->prepare("
            SELECT lp.*, o.order_number
            FROM loyalty_points lp
            LEFT JOIN orders o ON lp.order_id = o.id
            WHERE lp.user_id = ?
            ORDER BY lp.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }

    /**
     * Cập nhật tổng điểm trong bảng users
     */
    private function updateUserTotalPoints($userId, $pointsChange) {
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET total_points = GREATEST(0, total_points + ?)
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $pointsChange, $userId);
        $stmt->execute();
    }

    /**
     * Tính điểm có thể đổi thành tiền
     * 100 điểm = 10,000 VNĐ
     */
    public function convertPointsToMoney($points) {
        return ($points / 100) * 10000;
    }

    /**
     * Tính số điểm cần để đổi thành tiền
     */
    public function convertMoneyToPoints($amount) {
        return ($amount / 10000) * 100;
    }

    /**
     * Kiểm tra và xóa điểm đã hết hạn
     */
    public function expirePoints() {
        $stmt = $this->conn->prepare("
            SELECT user_id, SUM(points_available) as expired_points
            FROM loyalty_points
            WHERE expiry_date < CURDATE() 
              AND points_available > 0
              AND transaction_type = 'earned'
            GROUP BY user_id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Ghi lại điểm hết hạn
            $expireStmt = $this->conn->prepare("
                INSERT INTO loyalty_points 
                (user_id, points_used, points_available, transaction_type, description)
                VALUES (?, ?, ?, 'expired', ?)
            ");
            $expiredPoints = $row['expired_points'];
            $negativePoints = -$expiredPoints;
            $description = "Điểm hết hạn: {$expiredPoints} điểm";
            $expireStmt->bind_param("iiis", $row['user_id'], $expiredPoints, $negativePoints, $description);
            $expireStmt->execute();
            
            // Cập nhật tổng điểm
            $this->updateUserTotalPoints($row['user_id'], -$expiredPoints);
        }
    }
}

