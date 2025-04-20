<?php
// Đảm bảo đã có session và database
if (!isset($_SESSION)) {
    session_start();
}

// Xử lý các hành động thông qua URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    
    // Tạo một script để thực thi một lần duy nhất
    echo '<script>
    (function() {
        var cartData = localStorage.getItem("cart") || "[]";
        var cart = JSON.parse(cartData);
        var updated = false;
        
        ';
    
    if ($action == 'increase') {
        echo '
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].id == "'.$id.'") {
                cart[i].quantity++;
                updated = true;
                break;
            }
        }
        ';
    } elseif ($action == 'decrease') {
        echo '
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].id == "'.$id.'") {
                if (cart[i].quantity > 1) {
                    cart[i].quantity--;
                } else {
                    cart.splice(i, 1);
                }
                updated = true;
                break;
            }
        }
        ';
    } elseif ($action == 'remove') {
        echo '
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].id == "'.$id.'") {
                cart.splice(i, 1);
                updated = true;
                break;
            }
        }
        ';
    } elseif ($action == 'update') {
        echo '
        var newQty = '.$qty.';
        if (newQty > 0) {
            for (var i = 0; i < cart.length; i++) {
                if (cart[i].id == "'.$id.'") {
                    cart[i].quantity = newQty;
                    updated = true;
                    break;
                }
            }
        }
        ';
    }
    
    echo '
        // Chỉ cập nhật localStorage nếu có thay đổi
        if (updated) {
            localStorage.setItem("cart", JSON.stringify(cart));
        }
        
        // Đặt URL lại để tránh lặp lại action khi refresh
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, "cart.php");
        }
    })();
    </script>';
}
?>

<!-- Hiển thị và xử lý giỏ hàng -->
<script>
// Chạy ngay khi script được tải
(function() {
    // Hiển thị giỏ hàng
    function displayCart() {
        try {
            // Lấy dữ liệu giỏ hàng từ localStorage
            var cartData = localStorage.getItem("cart") || "[]";
            var cart = JSON.parse(cartData);
            
            // Kiểm tra và sửa hình ảnh đặc biệt
            var needUpdate = false;
            for (var i = 0; i < cart.length; i++) {
                if (cart[i].name && cart[i].name.includes("Robusta Ấn Độ")) {
                    cart[i].image = "images/robusta-india.jpg";
                    needUpdate = true;
                }
            }
            
            if (needUpdate) {
                localStorage.setItem("cart", JSON.stringify(cart));
            }
            
            // Xây dựng HTML giỏ hàng
            var cartContent = document.getElementById("cart-static");
            
            if (!cartContent) {
                console.error("Không tìm thấy phần tử có id 'cart-static'");
                return;
            }
            
            if (cart.length === 0) {
                cartContent.innerHTML = `
                    <div class="cart-empty">
                        <i>🛒</i>
                        <h3>Giỏ hàng của bạn đang trống</h3>
                        <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                        <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                    </div>
                `;
                return;
            }
            
            // Tính tổng đơn hàng
            var total = 0;
            for (var i = 0; i < cart.length; i++) {
                total += cart[i].price * cart[i].quantity;
            }
            
            var tableHTML = `
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            for (var i = 0; i < cart.length; i++) {
                var item = cart[i];
                var imagePath = item.image || "images/default-product.jpg";
                
                // Đảm bảo hiển thị hình ảnh đúng cho Robusta Ấn Độ
                if (item.name && item.name.includes("Robusta Ấn Độ")) {
                    imagePath = "images/robusta-india.jpg";
                }
                
                tableHTML += `
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <img src="${imagePath}" alt="${item.name}" 
                                     style="width: 80px; height: 80px; object-fit: cover;"
                                     onerror="this.src='images/default-product.jpg';">
                                <span style="margin-left: 15px;">${item.name}</span>
                            </div>
                        </td>
                        <td>${new Intl.NumberFormat("vi-VN").format(item.price)} VNĐ</td>
                        <td>
                            <div class="quantity-control">
                                <a href="cart.php?action=decrease&id=${item.id}" class="quantity-btn" style="display:inline-block; text-align:center; line-height:30px; text-decoration:none;">-</a>
                                <input type="number" value="${item.quantity}" min="1" 
                                       style="width: 50px; height: 35px; text-align: center; margin: 0 8px; border: 1px solid #d4a373; border-radius: 5px;"
                                       onchange="window.location.href='cart.php?action=update&id=${item.id}&qty='+this.value">
                                <a href="cart.php?action=increase&id=${item.id}" class="quantity-btn" style="display:inline-block; text-align:center; line-height:30px; text-decoration:none;">+</a>
                            </div>
                        </td>
                        <td>${new Intl.NumberFormat("vi-VN").format(item.price * item.quantity)} VNĐ</td>
                        <td>
                            <a href="cart.php?action=remove&id=${item.id}" class="remove-btn" style="display:inline-block; text-align:center; text-decoration:none; padding: 8px 12px;">Xóa</a>
                        </td>
                    </tr>
                `;
            }
            
            tableHTML += `
                    </tbody>
                </table>
            `;
            
            var summaryHTML = `
                <div class="cart-summary">
                    <h3>Tổng đơn hàng</h3>
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span>${new Intl.NumberFormat("vi-VN").format(total)} VNĐ</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>Miễn phí</span>
                    </div>
                    <div class="summary-row">
                        <span>Tổng cộng:</span>
                        <span>${new Intl.NumberFormat("vi-VN").format(total)} VNĐ</span>
                    </div>
                    
                    <a href="checkout.php" class="btn checkout-btn">Tiến hành thanh toán</a>
                    <a href="products.php" class="continue-shopping">← Tiếp tục mua sắm</a>
                </div>
            `;
            
            cartContent.innerHTML = tableHTML + summaryHTML;
            
        } catch (e) {
            console.error("Lỗi hiển thị giỏ hàng:", e);
            document.getElementById("cart-static").innerHTML = `
                <div class="cart-empty">
                    <h3>Đã xảy ra lỗi</h3>
                    <p>Không thể hiển thị giỏ hàng. Vui lòng thử lại sau.</p>
                    <a href="products.php" class="btn">Quay lại cửa hàng</a>
                </div>
            `;
        }
    }
    
    // Đảm bảo hiển thị giỏ hàng chỉ khi DOM đã sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', displayCart);
    } else {
        displayCart();
    }
})();
</script> 