<?php
session_start();

// Xử lý cả GET và POST
$action = '';
$id = '';
$quantity = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
}

// Thực hiện thao tác giỏ hàng với JavaScript
echo '
<!DOCTYPE html>
<html>
<head>
    <title>Đang xử lý giỏ hàng...</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3c2f2f;
            width: 40px;
            height: 40px;
            margin: 20px auto;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <h2>Đang xử lý giỏ hàng...</h2>
    <div class="loader"></div>
    <p>Vui lòng đợi trong giây lát.</p>
    
    <script>
        // Xử lý giỏ hàng
        try {
            // Lấy giỏ hàng
            let cart = [];
            const cartData = localStorage.getItem("cart");
            if (cartData) {
                cart = JSON.parse(cartData);
            }
            
            // Xử lý theo hành động
            if ("' . $action . '" === "remove") {
                // Tìm và xóa sản phẩm
                for (let i = 0; i < cart.length; i++) {
                    if (cart[i] && cart[i].id === "' . $id . '") {
                        cart.splice(i, 1);
                        break;
                    }
                }
                
                // Lưu lại giỏ hàng
                localStorage.setItem("cart", JSON.stringify(cart));
            } else if ("' . $action . '" === "update") {
                // Cập nhật số lượng
                const qty = ' . $quantity . ';
                if (!isNaN(qty) && qty > 0) {
                    for (let i = 0; i < cart.length; i++) {
                        if (cart[i] && cart[i].id === "' . $id . '") {
                            cart[i].quantity = qty;
                            break;
                        }
                    }
                    
                    // Lưu lại giỏ hàng
                    localStorage.setItem("cart", JSON.stringify(cart));
                }
            }
        } catch (e) {
            console.error("Lỗi khi xử lý giỏ hàng:", e);
        }
        
        // Chuyển hướng về trang giỏ hàng sau 0.5 giây
        setTimeout(function() {
            window.location.href = "cart.php";
        }, 500);
    </script>
</body>
</html>
';
exit;
?> 