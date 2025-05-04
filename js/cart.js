// Hàm thêm sản phẩm vào giỏ hàng
function addProductToCart(id, name, price, image, quantity = 1) {
    // Chuyển hướng đến trang add-to-cart.php với các tham số
    window.location.href = `add-to-cart.php?id=${encodeURIComponent(id)}&name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&image=${encodeURIComponent(image)}&quantity=${encodeURIComponent(quantity)}`;
}

// Cập nhật số lượng sản phẩm trong giỏ hàng
function updateCartCount() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const cartCount = document.getElementById("cartCount");
    if (cartCount) {
        cartCount.textContent = cart.length;
        console.log("Đã cập nhật hiển thị giỏ hàng: " + cart.length + " sản phẩm");
    }
    return cart.length;
}

// Khi trang được tải, cập nhật số lượng sản phẩm trong giỏ hàng
document.addEventListener("DOMContentLoaded", function() {
    updateCartCount();
    
    // Lắng nghe sự kiện thay đổi localStorage
    window.addEventListener('storage', function(e) {
        if (e.key === 'cart') {
            console.log('Phát hiện thay đổi ở localStorage, cập nhật lại giỏ hàng');
            updateCartCount();
        }
    });
    
    // Đồng bộ giỏ hàng từ localStorage và session
    syncCartWithSession();
});

// Hàm đồng bộ giỏ hàng giữa localStorage và session
function syncCartWithSession() {
    fetch('check_cart_session.php')
        .then(response => response.json())
        .then(data => {
            const localCart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            if (data.hasSession && data.count > 0) {
                console.log('Đã có giỏ hàng trong session:', data.count, 'sản phẩm');
                // Có thể thực hiện các hành động khác nếu cần
            } else if (localCart.length > 0) {
                console.log('Đồng bộ', localCart.length, 'sản phẩm từ localStorage vào session');
                syncCartToServer(localCart);
            }
        })
        .catch(error => {
            console.error('Lỗi kiểm tra session:', error);
        });
}

// Gửi dữ liệu giỏ hàng lên server
function syncCartToServer(cart) {
    fetch('sync_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'cart_data=' + encodeURIComponent(JSON.stringify(cart))
    })
    .then(response => response.json())
    .then(data => {
        console.log('Kết quả đồng bộ:', data.message);
        updateCartCount();
    })
    .catch(error => {
        console.error('Lỗi đồng bộ giỏ hàng:', error);
    });
}

// Xử lý thêm vào giỏ hàng thông qua AJAX
function addToCart(id, name, price, image, quantity = 1) {
    // Đảm bảo ID là số nguyên
    id = parseInt(id, 10);
    
    console.log("Thêm sản phẩm vào giỏ hàng:", id, name, price, "Kiểu dữ liệu ID:", typeof id);
    
    if (isNaN(id) || id <= 0) {
        console.error("ID sản phẩm không hợp lệ:", id);
        alert("ID sản phẩm không hợp lệ");
        return;
    }
    
    // Gửi dữ liệu thông qua AJAX
    fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&image=${encodeURIComponent(image)}&quantity=${encodeURIComponent(quantity)}&ajax=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log("Kết quả thêm vào giỏ hàng:", data);
            // Hiển thị thông tin ID của tất cả sản phẩm trong giỏ hàng
            console.log("Danh sách ID sản phẩm trong giỏ hàng:");
            data.cart.forEach((item, index) => {
                console.log(`Sản phẩm ${index + 1}: ID=${item.id} (${typeof item.id}), Tên=${item.name}`);
            });
            
            // Cập nhật localStorage
            localStorage.setItem("cart", JSON.stringify(data.cart));
            // Cập nhật số lượng trong biểu tượng giỏ hàng
            updateCartCount();
            // Hiển thị thông báo
            alert(`${name} đã được thêm vào giỏ hàng!`);
            
            // Không chuyển hướng đến trang giỏ hàng, ở lại trang hiện tại
            // Hiển thị thông báo thành công nếu có phần tử thông báo
            const messageElement = document.getElementById('cart-message');
            if (messageElement) {
                messageElement.textContent = `${name} đã được thêm vào giỏ hàng!`;
                messageElement.style.display = 'block';
                // Ẩn thông báo sau 3 giây
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 3000);
            }
        } else {
            console.error("Lỗi thêm vào giỏ hàng:", data.message);
            alert("Có lỗi xảy ra: " + data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        alert("Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.");
    });
}

// Hàm tăng số lượng
function increaseQuantity(inputId) {
    var input = document.getElementById(inputId || 'quantity');
    var value = parseInt(input.value);
    if(value < 99) {
        input.value = value + 1;
    }
}

// Hàm giảm số lượng
function decreaseQuantity(inputId) {
    var input = document.getElementById(inputId || 'quantity');
    var value = parseInt(input.value);
    if(value > 1) {
        input.value = value - 1;
    }
} 