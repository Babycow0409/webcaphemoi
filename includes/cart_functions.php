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
 * @param object $conn Kết nối cơ sở dữ liệu (tùy chọn)
 * @return array Mảng chứa giỏ hàng đã cập nhật và thông báo nếu có
 */
function addToCart($cart, $id, $name, $price, $image, $quantity = 1, $conn = null) {
    // Kiểm tra tính hợp lệ của dữ liệu đầu vào
    if (!is_numeric($id) || !is_numeric($price) || !is_numeric($quantity)) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    if ($quantity <= 0) {
        throw new Exception('Số lượng phải lớn hơn 0');
    }

    // Khởi tạo thông báo
    $message = '';
    $availableStock = PHP_INT_MAX; // Mặc định là không giới hạn nếu không có kết nối

    // Kiểm tra tồn kho nếu có kết nối database
    if ($conn !== null) {
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $availableStock = (int)$product['stock'];
        }
    }

    // Tính toán số lượng hiện có trong giỏ hàng
    $currentQuantity = 0;
    $found = false;
    foreach ($cart as $key => $item) {
        if (isset($item['id']) && (int)$item['id'] == (int)$id) {
            $currentQuantity = $item['quantity'];
            $found = true;
            break;
        }
    }

    // Tính tổng số lượng sau khi thêm
    $totalQuantity = $currentQuantity + $quantity;

    // Kiểm tra và điều chỉnh số lượng nếu vượt quá tồn kho
    if ($totalQuantity > $availableStock) {
        // Số lượng vượt quá tồn kho
        if ($availableStock <= 0) {
            $message = "Sản phẩm '{$name}' đã hết hàng!";
            // Không thêm vào giỏ hàng
            return ['cart' => $cart, 'message' => $message];
        } else {
            if ($found) {
                // Nếu đã có trong giỏ hàng, điều chỉnh về số lượng tối đa
                if ($currentQuantity < $availableStock) {
                    $quantity = $availableStock - $currentQuantity;
                    $message = "Số lượng sản phẩm '{$name}' đã được điều chỉnh về tối đa {$availableStock} do tồn kho không đủ.";
                } else {
                    $message = "Sản phẩm '{$name}' trong giỏ hàng đã đạt số lượng tối đa ({$availableStock}).";
                    return ['cart' => $cart, 'message' => $message];
                }
            } else {
                // Nếu chưa có trong giỏ, thêm với số lượng là tồn kho hiện có
                $quantity = $availableStock;
                $message = "Số lượng sản phẩm '{$name}' đã được điều chỉnh về tối đa {$availableStock} do tồn kho không đủ.";
            }
        }
    }

    // Thêm sản phẩm vào giỏ hàng
    if ($found) {
        foreach ($cart as $key => $item) {
            if (isset($item['id']) && (int)$item['id'] == (int)$id) {
                $cart[$key]['quantity'] += $quantity;
                break;
            }
        }
    } else {
        $cart[] = [
            'id' => (int)$id,
            'name' => $name,
            'price' => (float)$price,
            'image' => $image,
            'quantity' => (int)$quantity
        ];
    }

    // Đảm bảo chỉ số mảng liên tục
    return ['cart' => array_values($cart), 'message' => $message];
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
 * @param object $conn Kết nối cơ sở dữ liệu (tùy chọn)
 * @return array Mảng chứa giỏ hàng đã cập nhật và thông báo nếu có
 */
function updateCartItemQuantity($cart, $id, $quantity, $conn = null) {
    if (!is_numeric($id) || !is_numeric($quantity)) {
        throw new Exception('Dữ liệu không hợp lệ');
    }
    
    if($quantity <= 0) {
        return ['cart' => removeFromCart($cart, $id), 'message' => ''];
    }
    
    // Khởi tạo thông báo
    $message = '';
    $availableStock = PHP_INT_MAX; // Mặc định là không giới hạn nếu không có kết nối
    $productName = '';
    
    // Lấy tên sản phẩm
    foreach($cart as $item) {
        if(isset($item['id']) && $item['id'] == $id) {
            $productName = $item['name'];
            break;
        }
    }
    
    // Kiểm tra tồn kho nếu có kết nối database
    if ($conn !== null) {
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $availableStock = (int)$product['stock'];
        }
    }
    
    // Kiểm tra và điều chỉnh số lượng nếu vượt quá tồn kho
    if ($quantity > $availableStock) {
        if ($availableStock <= 0) {
            // Nếu hết hàng, xóa khỏi giỏ hàng
            $message = "Sản phẩm '{$productName}' đã hết hàng và đã được xóa khỏi giỏ hàng.";
            return ['cart' => removeFromCart($cart, $id), 'message' => $message];
        } else {
            // Điều chỉnh về số lượng tồn kho tối đa
            $quantity = $availableStock;
            $message = "Số lượng sản phẩm '{$productName}' đã được điều chỉnh về tối đa {$availableStock} do tồn kho không đủ.";
        }
    }
    
    // Cập nhật số lượng trong giỏ hàng
    foreach($cart as $key => $item) {
        if(isset($item['id']) && $item['id'] == $id) {
            $cart[$key]['quantity'] = $quantity;
            break;
        }
    }
    
    return ['cart' => $cart, 'message' => $message];
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

/**
 * Kiểm tra và lọc bỏ các sản phẩm không tồn tại trong giỏ hàng
 * 
 * @param array &$cart Giỏ hàng cần kiểm tra (tham chiếu)
 * @param object $conn Kết nối cơ sở dữ liệu
 * @return array Danh sách tên các sản phẩm đã bị xóa
 */
function validateCartProducts(&$cart, $conn) {
    if(empty($cart) || !is_array($cart)) {
        return [];
    }
    
    $validCart = [];
    $removedProducts = [];
    
    foreach($cart as $item) {
        if(!isset($item['id'])) {
            continue;
        }
        
        // Kiểm tra sản phẩm có tồn tại trong database không
        $stmt = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && $result->num_rows > 0) {
            // Sản phẩm vẫn tồn tại, giữ lại trong giỏ hàng
            $validCart[] = $item;
        } else {
            // Sản phẩm đã bị xóa, ghi lại thông tin
            $removedProducts[] = $item['name'];
        }
    }
    
    // Cập nhật lại giỏ hàng
    $cart = $validCart;
    
    return $removedProducts;
}

/**
 * Kiểm tra và điều chỉnh số lượng trong giỏ hàng dựa trên tồn kho
 * 
 * @param array &$cart Giỏ hàng cần kiểm tra (tham chiếu)
 * @param object $conn Kết nối cơ sở dữ liệu
 * @return array Mảng thông báo về các sản phẩm đã được điều chỉnh
 */
function validateCartStock(&$cart, $conn) {
    if(empty($cart) || !is_array($cart)) {
        return [];
    }
    
    $adjustedProducts = [];
    
    foreach($cart as $key => $item) {
        if(!isset($item['id'])) {
            continue;
        }
        
        // Kiểm tra tồn kho của sản phẩm trong database
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $availableStock = (int)$product['stock'];
            
            // Nếu số lượng trong giỏ hàng lớn hơn tồn kho
            if(isset($item['quantity']) && $item['quantity'] > $availableStock) {
                // Ghi lại thông tin về sản phẩm cần điều chỉnh
                $adjustedProducts[] = [
                    'name' => $item['name'],
                    'requested' => $item['quantity'],
                    'available' => $availableStock
                ];
                
                // Điều chỉnh số lượng về tối đa có thể
                if($availableStock > 0) {
                    $cart[$key]['quantity'] = $availableStock;
                } else {
                    // Nếu hết hàng, xóa khỏi giỏ hàng
                    unset($cart[$key]);
                }
            }
        }
    }
    
    // Đảm bảo chỉ số mảng liên tục
    $cart = array_values($cart);
    
    return $adjustedProducts;
} 