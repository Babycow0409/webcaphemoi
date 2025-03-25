<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { padding-top: 100px; line-height: 1.6; }
        header { background-color: #3c2f2f; color: white; padding: 1rem; position: fixed; width: 100%; top: 0; z-index: 1000; }
        nav { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.8em; padding: 10px; }
        .nav-links { display: flex; flex-wrap: wrap; align-items: center; padding: 10px; }
        nav a { color: white; text-decoration: none; margin: 10px 15px; font-weight: bold; }
        nav a:hover { color: #d4a373; }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #3c2f2f;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s, transform 0.3s;
        }
        .dropdown:hover .dropdown-content {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #d4a373;
        }
        h1, h2 { font-family: 'Playfair Display', serif; color: #3c2f2f; text-align: center; margin: 40px 0 20px; }
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .cart-empty {
            text-align: center;
            padding: 50px 0;
            color: #555;
        }
        .cart-empty p {
            margin-bottom: 30px;
            font-size: 1.2em;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .cart-table th, .cart-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .cart-table th {
            background-color: #f8f3eb;
            font-weight: 700;
            color: #3c2f2f;
        }
        .cart-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .quantity-control {
            display: flex;
            align-items: center;
        }
        .quantity-control button {
            width: 30px;
            height: 30px;
            background-color: #f8f3eb;
            border: 1px solid #ddd;
            cursor: pointer;
            font-size: 1.2em;
        }
        .quantity-control input {
            width: 50px;
            height: 30px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ddd;
        }
        .remove-btn {
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .remove-btn:hover {
            background-color: #d32f2f;
        }
        .cart-total {
            text-align: right;
            margin-bottom: 30px;
        }
        .cart-total h3 {
            font-size: 1.5em;
            color: #3c2f2f;
            margin-bottom: 10px;
        }
        .cart-total p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .btn { 
            padding: 12px 30px; 
            background-color: #d4a373; 
            color: white; 
            text-decoration: none; 
            border: none; 
            border-radius: 50px; 
            cursor: pointer; 
            transition: all 0.3s; 
            display: inline-block;
        }
        .btn:hover { background-color: #8b4513; transform: scale(1.05); }
        .btn-primary { background-color: #3c2f2f; }
        .btn-primary:hover { background-color: #594a4a; }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) { 
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .cart-table, .cart-table thead, .cart-table tbody, .cart-table th, .cart-table td, .cart-table tr { 
                display: block; 
            }
            .cart-table thead tr { 
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .cart-table tr { margin-bottom: 20px; border: 1px solid #ddd; }
            .cart-table td { 
                border: none;
                border-bottom: 1px solid #ddd; 
                position: relative;
                padding-left: 50%; 
                text-align: right;
            }
            .cart-table td:before { 
                position: absolute;
                top: 15px;
                left: 15px;
                width: 45%; 
                padding-right: 10px; 
                white-space: nowrap;
                font-weight: bold;
                text-align: left;
            }
            .cart-table td:nth-of-type(1):before { content: "Sản phẩm"; }
            .cart-table td:nth-of-type(2):before { content: "Giá"; }
            .cart-table td:nth-of-type(3):before { content: "Số lượng"; }
            .cart-table td:nth-of-type(4):before { content: "Thành tiền"; }
            .cart-table td:nth-of-type(5):before { content: "Xóa"; }
            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }
            .action-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cà Phê Đậm Đà</div>
            <div class="nav-links">
                <a href="index.php">Trang chủ</a>
                <div class="dropdown">
                    <a href="products.php">Sản phẩm</a>
                    <div class="dropdown-content">
                        <a href="products.php">Tất cả</a>
                        <a href="arabica.php">Arabica</a>
                        <a href="robusta.php">Robusta</a>
                        <a href="chon.php">Chồn</a>
                        <a href="Khac.php">Khác</a>
                    </div>
                </div>
                <a href="#about">Giới thiệu</a>
                <a href="#contact">Liên hệ</a>
                <a href="cart.php">Giỏ hàng</a>
            </div>
        </nav>
    </header>

    <div class="cart-container">
        <h1>Giỏ hàng của bạn</h1>
        
        <div id="cartContent">
            <!-- JS sẽ render nội dung giỏ hàng vào đây -->
        </div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        const cartContent = document.getElementById('cartContent');

        function renderCart() {
            if (cart.length === 0) {
                cartContent.innerHTML = `
                    <div class="cart-empty">
                        <p>Giỏ hàng của bạn đang trống</p>
                        <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                    </div>
                `;
                return;
            }

            let tableHTML = `
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let total = 0;
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                tableHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${new Intl.NumberFormat('vi-VN').format(item.price)} VNĐ</td>
                        <td>
                            <div class="quantity-control">
                                <button onclick="decreaseQuantity(${index})">-</button>
                                <input type="number" value="${item.quantity}" min="1" onchange="updateQuantity(${index}, this.value)">
                                <button onclick="increaseQuantity(${index})">+</button>
                            </div>
                        </td>
                        <td>${new Intl.NumberFormat('vi-VN').format(itemTotal)} VNĐ</td>
                        <td><button class="remove-btn" onclick="removeItem(${index})">Xóa</button></td>
                    </tr>
                `;
            });

            tableHTML += `
                    </tbody>
                </table>
                <div class="cart-total">
                    <h3>Tổng tiền</h3>
                    <p>${new Intl.NumberFormat('vi-VN').format(total)} VNĐ</p>
                </div>
                <div class="action-buttons">
                    <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                    <a href="checkout.php" class="btn btn-primary">Thanh toán</a>
                </div>
            `;

            cartContent.innerHTML = tableHTML;
        }

        function increaseQuantity(index) {
            cart[index].quantity += 1;
            saveCartAndRender();
        }

        function decreaseQuantity(index) {
            if (cart[index].quantity > 1) {
                cart[index].quantity -= 1;
                saveCartAndRender();
            }
        }

        function updateQuantity(index, value) {
            const quantity = parseInt(value);
            if (quantity > 0) {
                cart[index].quantity = quantity;
                saveCartAndRender();
            }
        }

        function removeItem(index) {
            cart.splice(index, 1);
            saveCartAndRender();
        }

        function saveCartAndRender() {
            localStorage.setItem("cart", JSON.stringify(cart));
            renderCart();
        }

        // Render giỏ hàng khi trang được tải
        renderCart();
    </script>
</body>
</html> 