<?php
session_start();
require_once 'includes/header.php';
?>

<div class="about-container">
    <div class="about-header">
        <h1>Về Chúng Tôi</h1>
        <p class="subtitle">Khám phá câu chuyện và tầm nhìn của Coffee Shop</p>
    </div>

    <div class="about-content">
        <section class="about-section">
            <h2>Câu Chuyện Của Chúng Tôi</h2>
            <p>Được thành lập vào năm 2010, Coffee Shop đã phát triển từ một quán cà phê nhỏ thành một thương hiệu cà
                phê được yêu thích tại Việt Nam. Chúng tôi tự hào về việc mang đến những trải nghiệm cà phê chất lượng
                cao, kết hợp giữa truyền thống và hiện đại.</p>
        </section>

        <section class="about-section">
            <h2>Tầm Nhìn & Sứ Mệnh</h2>
            <p>Tầm nhìn của chúng tôi là trở thành địa điểm cà phê hàng đầu, nơi mọi người có thể tận hưởng những khoảnh
                khắc thư giãn tuyệt vời với tách cà phê chất lượng cao.</p>
            <p>Sứ mệnh của chúng tôi là:</p>
            <ul>
                <li>Cung cấp cà phê chất lượng cao từ nguồn nguyên liệu được chọn lọc kỹ lưỡng</li>
                <li>Tạo không gian thoải mái và thân thiện cho khách hàng</li>
                <li>Đóng góp vào sự phát triển bền vững của ngành cà phê Việt Nam</li>
            </ul>
        </section>

        <section class="about-section">
            <h2>Giá Trị Cốt Lõi</h2>
            <div class="values-grid">
                <div class="value-item">
                    <i class="fas fa-heart"></i>
                    <h3>Chất Lượng</h3>
                    <p>Cam kết mang đến những sản phẩm cà phê tốt nhất</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-leaf"></i>
                    <h3>Bền Vững</h3>
                    <p>Hỗ trợ nông dân và bảo vệ môi trường</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-users"></i>
                    <h3>Cộng Đồng</h3>
                    <p>Xây dựng cộng đồng yêu cà phê</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-star"></i>
                    <h3>Đổi Mới</h3>
                    <p>Không ngừng cải tiến và phát triển</p>
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2>Đội Ngũ Của Chúng Tôi</h2>
            <p>Đội ngũ của chúng tôi bao gồm những chuyên gia cà phê giàu kinh nghiệm, những barista tài năng và nhân
                viên nhiệt tình, luôn sẵn sàng phục vụ khách hàng với nụ cười thân thiện.</p>
        </section>
    </div>
</div>

<style>
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.about-header {
    text-align: center;
    margin-bottom: 50px;
}

.about-header h1 {
    color: #3c2f2f;
    font-size: 2.5em;
    margin-bottom: 15px;
}

.subtitle {
    color: #666;
    font-size: 1.2em;
}

.about-section {
    margin-bottom: 50px;
}

.about-section h2 {
    color: #d4a373;
    margin-bottom: 20px;
    font-size: 1.8em;
}

.about-section p {
    line-height: 1.6;
    color: #333;
    margin-bottom: 15px;
}

.about-section ul {
    list-style-type: disc;
    margin-left: 20px;
    margin-bottom: 20px;
}

.about-section li {
    margin-bottom: 10px;
    color: #333;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.value-item {
    text-align: center;
    padding: 20px;
    background-color: #f9f5f0;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.value-item:hover {
    transform: translateY(-5px);
}

.value-item i {
    font-size: 2em;
    color: #d4a373;
    margin-bottom: 15px;
}

.value-item h3 {
    color: #3c2f2f;
    margin-bottom: 10px;
}

.value-item p {
    color: #666;
    font-size: 0.9em;
}

@media (max-width: 768px) {
    .about-header h1 {
        font-size: 2em;
    }

    .values-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>