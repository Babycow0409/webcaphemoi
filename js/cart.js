// Hàm thêm sản phẩm vào giỏ hàng
function addProductToCart(id, name, price, image, quantity) {
    // Đảm bảo quantity là số
    quantity = parseInt(quantity) || 1;
    
    // Chuyển hướng đến trang add-to-cart.php với tham số GET
    window.location.href = "add-to-cart.php?id=" + encodeURIComponent(id) + 
                           "&name=" + encodeURIComponent(name) + 
                           "&price=" + encodeURIComponent(price) + 
                           "&image=" + encodeURIComponent(image) + 
                           "&quantity=" + encodeURIComponent(quantity);
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