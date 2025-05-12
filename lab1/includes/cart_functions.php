<?php
/**
 * Tập tin chứa các hàm xử lý giỏ hàng
 */

/**
 * Thêm sản phẩm vào giỏ hàng
 * 
 * @param array $cart Giỏ hàng hiện tại
 * @param int $id ID sản phẩm
 * @param string $name Tên sản phẩm
 * @param float $price Giá sản phẩm
 * @param string $image Đường dẫn hình ảnh
 * @param int $quantity Số lượng
 * @return array Giỏ hàng đã cập nhật
 */
function addToCart($cart, $id, $name, $price, $image, $quantity = 1) {
    // Kiểm tra tính hợp lệ của dữ liệu đầu vào
    if (!is_numeric($id) || !is_numeric($price) || !is_numeric($quantity)) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    if ($quantity <= 0) {
        throw new Exception('Số lượng phải lớn hơn 0');
    }
    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $found = false;
    foreach($cart as $key => $item) {
        if(isset($item['id']) && (int)$item['id'] == (int)$id) {
            $cart[$key]['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    // Nếu chưa có trong giỏ hàng, thêm mới
    if(!$found) {
        $cart[] = [
            'id' => (int)$id,
            'name' => $name,
            'price' => (float)$price,
            'image' => $image,
            'quantity' => (int)$quantity
        ];
    }
    // Đảm bảo chỉ số mảng liên tục
    return array_values($cart);
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 * 
 * @param array $cart Giỏ hàng hiện tại
 * @param int $id ID sản phẩm cần xóa
 * @return array Giỏ hàng đã cập nhật
 */
function removeFromCart($cart, $id) {
    foreach($cart as $key => $item) {
        if(isset($item['id']) && (int)$item['id'] == (int)$id) {
            unset($cart[$key]);
            // Không break để xóa tất cả các sản phẩm có cùng ID
        }
    }
    
    // Đảm bảo chỉ số mảng liên tục
    return array_values($cart);
}

/**
 * Cập nhật số lượng sản phẩm trong giỏ hàng
 * 
 * @param array $cart Giỏ hàng hiện tại
 * @param int $id ID sản phẩm cần cập nhật
 * @param int $quantity Số lượng mới
 * @return array Giỏ hàng đã cập nhật
 */
function updateCartItemQuantity($cart, $id, $quantity) {
    if (!is_numeric($id) || !is_numeric($quantity)) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    if($quantity <= 0) {
        return removeFromCart($cart, $id);
    }
    foreach($cart as $key => $item) {
        if(isset($item['id']) && $item['id'] == $id) {
            $cart[$key]['quantity'] = $quantity;
            break;
        }
    }
    return $cart;
}

/**
 * Tính tổng giá trị giỏ hàng
 * 
 * @param array $cart Giỏ hàng
 * @return float Tổng giá trị
 */
function calculateCartTotal($cart) {
    $total = 0;
    foreach($cart as $item) {
        if(isset($item['price']) && isset($item['quantity'])) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

/**
 * Đồng bộ giỏ hàng từ LocalStorage vào Session
 * 
 * @param string $jsonCart Chuỗi JSON từ localStorage
 * @return array Giỏ hàng đã đồng bộ
 */
function syncCartFromLocalStorage($jsonCart) {
    $cartArray = json_decode($jsonCart, true);
    
    if(is_array($cartArray) && !empty($cartArray)) {
        return array_values($cartArray);
    }
    
    return [];
}

/**
 * Xử lý đường dẫn hình ảnh sản phẩm
 * 
 * @param string $image Đường dẫn hình ảnh gốc
 * @return string Đường dẫn hình ảnh đã xử lý
 */
function processProductImage($image) {
    if(empty($image)) {
        return 'images/default-product.jpg';
    }
    
    // Nếu đường dẫn không có tiền tố uploads/ hoặc images/
    if(strpos($image, 'uploads/') === false && strpos($image, 'images/') === false) {
        return 'uploads/products/' . $image;
    }
    
    return $image;
} 