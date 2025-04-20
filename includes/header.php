<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php">Cà Phê Đậm Đà</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php">Sản phẩm</a></li>
                    <li><a href="arabica.php">Arabica</a></li>
                    <li><a href="robusta.php">Robusta</a></li>
                    <li><a href="chon.php">Chồn</a></li>
                    <li><a href="cart.php">Giỏ hàng</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="dropdown user-logged-in">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['fullname']); ?> <i class="fas fa-caret-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="profile.php">Tài khoản của tôi</a></li>
                                <li><a href="my-orders.php">Đơn hàng của tôi</a></li>
                                <li><a href="address-book.php">Sổ địa chỉ</a></li>
                                <li><a href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php">Đăng nhập</a></li>
                        <li><a href="register.php">Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="login-status">
    <div class="container">
        <p>
            Xin chào <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong> | 
            <a href="profile.php">Tài khoản</a> | 
            <a href="my-orders.php">Đơn hàng</a> | 
            <a href="logout.php">Đăng xuất</a>
        </p>
    </div>
</div>
<?php endif; ?> 