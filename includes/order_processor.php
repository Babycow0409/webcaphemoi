<?php
/**
 * File xử lý đơn hàng với khuyến mãi và tích điểm
 */

require_once __DIR__ . '/../classes/Promotion.php';
require_once __DIR__ . '/../classes/LoyaltyPoint.php';
require_once __DIR__ . '/../classes/Customer.php';

/**
 * Xử lý đơn hàng với đầy đủ tính năng
 */
class OrderProcessor {
    private $conn;
    private $promotion;
    private $loyaltyPoint;
    private $customer;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->loyaltyPoint = new LoyaltyPoint($conn);
    }

    /**
     * Tính toán giá đơn hàng với khuyến mãi và điểm tích lũy
     */
    public function calculateOrderTotal($cart, $promotionCode = null, $pointsToUse = 0, $userId = null) {
        // Tính tổng tiền ban đầu
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discountAmount = 0;
        $promotionId = null;
        $pointsDiscount = 0;

        // Áp dụng khuyến mãi nếu có
        if ($promotionCode) {
            $promotion = new Promotion($this->conn);
            if ($promotion->loadByCode($promotionCode)) {
                // Kiểm tra khuyến mãi hợp lệ
                $validation = $promotion->isValid($userId, $subtotal, $cart);
                
                if ($validation['valid']) {
                    $discountAmount = $promotion->calculateDiscount($subtotal);
                    $promotionId = $promotion->getId();
                }
            }
        }

        // Áp dụng điểm tích lũy nếu có
        if ($pointsToUse > 0 && $userId) {
            $availablePoints = $this->loyaltyPoint->getAvailablePoints($userId);
            
            if ($pointsToUse <= $availablePoints) {
                // 100 điểm = 10,000 VNĐ
                $pointsDiscount = ($pointsToUse / 100) * 10000;
                
                // Không được vượt quá 50% giá trị đơn hàng sau giảm giá khuyến mãi
                $orderAfterPromotion = $subtotal - $discountAmount;
                $maxPointsDiscount = $orderAfterPromotion * 0.5;
                
                if ($pointsDiscount > $maxPointsDiscount) {
                    $pointsDiscount = $maxPointsDiscount;
                    // Tính lại số điểm cần dùng
                    $pointsToUse = ($pointsDiscount / 10000) * 100;
                }
            } else {
                // Nếu điểm không đủ, không sử dụng điểm
                $pointsToUse = 0;
                $pointsDiscount = 0;
            }
        }

        // Tính tổng cuối cùng
        $total = $subtotal - $discountAmount - $pointsDiscount;
        
        // Đảm bảo tổng không âm
        if ($total < 0) {
            $total = 0;
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'points_used' => $pointsToUse,
            'points_discount' => $pointsDiscount,
            'total' => $total,
            'promotion_id' => $promotionId
        ];
    }

    /**
     * Xử lý đặt hàng với đầy đủ tính năng
     */
    public function processOrder($orderData, $cart, $userId) {
        try {
            $this->conn->begin_transaction();

            // Tính toán giá đơn hàng
            $promotionCode = $orderData['promotion_code'] ?? null;
            $pointsToUse = isset($orderData['points_to_use']) ? (int)$orderData['points_to_use'] : 0;
            
            $orderTotal = $this->calculateOrderTotal($cart, $promotionCode, $pointsToUse, $userId);

            // Tạo mã đơn hàng
            $orderNumber = $this->generateOrderNumber();

            // Lưu đơn hàng
            $orderId = $this->createOrder($orderData, $orderNumber, $orderTotal, $userId);

            // Lưu chi tiết đơn hàng và cập nhật tồn kho
            $this->createOrderItems($orderId, $cart);

            // Áp dụng khuyến mãi nếu có
            if ($orderTotal['promotion_id']) {
                $promotion = new Promotion($this->conn);
                $promotion->loadPromotion($orderTotal['promotion_id']);
                $promotion->applyPromotion(
                    $userId, 
                    $orderId, 
                    $orderTotal['subtotal'], 
                    $orderTotal['discount_amount']
                );
            }

            // Sử dụng điểm tích lũy nếu có
            if ($orderTotal['points_used'] > 0) {
                $this->loyaltyPoint->usePoints(
                    $userId, 
                    $orderId, 
                    $orderTotal['points_used'], 
                    $orderTotal['points_discount']
                );
            }

            // Tích điểm cho khách hàng
            $pointsEarned = $this->loyaltyPoint->earnPoints(
                $userId, 
                $orderId, 
                $orderTotal['total'],
                "Tích điểm từ đơn hàng #{$orderNumber}"
            );

            // Cập nhật thông tin khách hàng
            $customer = new Customer($this->conn, $userId);
            $customer->updateTotalSpent($orderTotal['total']);

            // Commit transaction
            $this->conn->commit();

            return [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total' => $orderTotal['total'],
                'points_earned' => $pointsEarned,
                'points_used' => $orderTotal['points_used']
            ];

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Tạo mã đơn hàng
     */
    private function generateOrderNumber() {
        $timestamp = date('YmdHis');
        $random = mt_rand(100, 999);
        return "ORDER" . $timestamp . $random;
    }

    /**
     * Tạo đơn hàng
     */
    private function createOrder($orderData, $orderNumber, $orderTotal, $userId) {
        $stmt = $this->conn->prepare("
            INSERT INTO orders 
            (order_number, user_id, shipping_name, shipping_address, shipping_city, shipping_phone, 
             payment_method, payment_status, total_amount, discount_amount, promotion_id, points_used, points_earned, 
             status, order_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $pointsEarned = floor($orderTotal['total'] / 1000); // 1 điểm cho mỗi 1000 VNĐ

        $paymentStatus = $orderData['payment_status'] ?? 'pending';
        $promotionId = $orderTotal['promotion_id'] ?? null;
        $pointsUsed = (int)($orderTotal['points_used'] ?? 0);

        $stmt->bind_param(
            "sissssssddiii",
            $orderNumber,
            $userId,
            $orderData['fullname'],
            $orderData['address'],
            $orderData['city'],
            $orderData['phone'],
            $orderData['payment'],
            $paymentStatus,
            $orderTotal['total'],
            $orderTotal['discount_amount'],
            $promotionId,
            $pointsUsed,
            $pointsEarned
        );

        $stmt->execute();
        return $this->conn->insert_id;
    }

    /**
     * Tạo chi tiết đơn hàng và cập nhật tồn kho
     */
    private function createOrderItems($orderId, $cart) {
        foreach ($cart as $item) {
            // Lưu chi tiết đơn hàng
            $stmt = $this->conn->prepare("
                INSERT INTO order_items (order_id, product_name, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isid", $orderId, $item['name'], $item['quantity'], $item['price']);
            $stmt->execute();

            // Cập nhật tồn kho
            if (isset($item['id'])) {
                $updateStmt = $this->conn->prepare("
                    UPDATE products 
                    SET stock = stock - ? 
                    WHERE id = ? AND stock >= ?
                ");
                $updateStmt->bind_param("iii", $item['quantity'], $item['id'], $item['quantity']);
                
                if (!$updateStmt->execute()) {
                    throw new Exception("Không đủ tồn kho cho sản phẩm: " . $item['name']);
                }
                
                // Kiểm tra số hàng bị ảnh hưởng
                if ($updateStmt->affected_rows == 0) {
                    throw new Exception("Không đủ tồn kho cho sản phẩm: " . $item['name']);
                }
            }
        }
    }
}

