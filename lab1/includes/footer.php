<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h3>Coffee Shop</h3>
            <p>Chúng tôi chuyên cung cấp các loại cà phê chất lượng cao, từ hạt Arabica, Robusta đến cà phê Chồn đặc
                biệt, mang đến trải nghiệm thưởng thức cà phê tuyệt vời.</p>
            <p>Hương vị độc đáo - Chất lượng đỉnh cao</p>
        </div>

        <div class="footer-section">
            <h3>Liên hệ</h3>
            <p><i class="fas fa-map-marker-alt"></i> 123 Đường Cà Phê, Quận 1, TP. Hồ Chí Minh</p>
            <p><i class="fas fa-phone"></i> Hotline: 1900-1234</p>
            <p><i class="fas fa-envelope"></i> Email: info@coffeeshop.com</p>
            <p><i class="fas fa-clock"></i> Giờ mở cửa: 7:00 - 22:00 (Hàng ngày)</p>
        </div>

        <div class="footer-section">
            <h3>Truy cập nhanh</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 8px;"><a href="index.php" style="color: #f1f1f1; text-decoration: none;"><i
                            class="fas fa-chevron-right"></i> Trang chủ</a></li>
                <li style="margin-bottom: 8px;"><a href="products.php" style="color: #f1f1f1; text-decoration: none;"><i
                            class="fas fa-chevron-right"></i> Sản phẩm</a></li>
                <li style="margin-bottom: 8px;"><a href="about.php" style="color: #f1f1f1; text-decoration: none;"><i
                            class="fas fa-chevron-right"></i> Giới thiệu</a></li>

                <li style="margin-bottom: 8px;"><a href="my-orders.php"
                        style="color: #f1f1f1; text-decoration: none;"><i class="fas fa-chevron-right"></i> Đơn hàng</a>
                </li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Đăng ký nhận thông tin</h3>
            <p>Nhận thông tin về sản phẩm mới và khuyến mãi đặc biệt.</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Nhập email của bạn" required>
                <button type="submit">Đăng ký</button>
            </form>

            <h3 style="margin-top: 25px;">Theo dõi chúng tôi</h3>
            <div class="social-links">
                <a href="facebook.com" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="instagram.com" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="twitter.com" title="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="youtube.com" title="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Coffee Shop. Tất cả quyền được bảo lưu | Thiết kế bởi <a href="#"
                style="color: #d4a373; text-decoration: none;">Company Name</a></p>
    </div>
</footer>

<style>
footer {
    background-color: #3c2f2f;
    color: #fff;
    padding: 40px 0 20px;
    margin-top: 60px;
}

.footer-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-section {
    flex: 1;
    min-width: 250px;
    margin-bottom: 25px;
    padding: 0 15px;
}

.footer-section h3 {
    color: #d4a373;
    margin-bottom: 20px;
    font-size: 18px;
    position: relative;
    padding-bottom: 10px;
}

.footer-section h3:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background-color: #d4a373;
}

.footer-section p {
    margin-bottom: 10px;
    font-size: 14px;
    line-height: 1.6;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 15px;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: #fff;
    transition: all 0.3s;
}

.social-links a:hover {
    background-color: #d4a373;
    transform: translateY(-3px);
}

.newsletter-form {
    display: flex;
    margin-top: 15px;
}

.newsletter-form input {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 4px 0 0 4px;
}

.newsletter-form button {
    padding: 10px 15px;
    background-color: #d4a373;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color 0.3s;
}

.newsletter-form button:hover {
    background-color: #c1864a;
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 30px;
    font-size: 13px;
}

@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
    }

    .footer-section {
        margin-bottom: 30px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</body>

</html>