<div class="search-container">
    <form action="search.php" method="get" class="advanced-search">
        <div class="filter-section">
            <label for="searchInput"><i class="fas fa-search"></i> Tìm kiếm:</label>
            <input type="text" id="searchInput" name="q" placeholder="Nhập tên sản phẩm..." 
                   class="search-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        </div>
        
        <?php if (!isset($hideCategory)): ?>
        <div class="filter-section">
            <label for="categorySelect"><i class="fas fa-coffee"></i> Loại cà phê:</label>
            <select name="category" id="categorySelect">
                <option value="">Tất cả loại</option>
                <option value="arabica" <?php echo (isset($_GET['category']) && $_GET['category'] == 'arabica') ? 'selected' : ''; ?>>Arabica</option>
                <option value="robusta" <?php echo (isset($_GET['category']) && $_GET['category'] == 'robusta') ? 'selected' : ''; ?>>Robusta</option>
                <option value="chon" <?php echo (isset($_GET['category']) && $_GET['category'] == 'chon') ? 'selected' : ''; ?>>Chồn</option>
                <option value="other" <?php echo (isset($_GET['category']) && $_GET['category'] == 'other') ? 'selected' : ''; ?>>Khác</option>
            </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="category" value="<?php echo $currentCategory; ?>">
        <?php endif; ?>
        
        <div class="filter-section">
            <label for="priceRange"><i class="fas fa-tag"></i> Khoảng giá:</label>
            <select name="price_range" id="priceRange">
                <option value="">Tất cả giá</option>
                <option value="0-100000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '0-100000') ? 'selected' : ''; ?>>Dưới 100.000đ</option>
                <option value="100000-300000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '100000-300000') ? 'selected' : ''; ?>>100.000đ - 300.000đ</option>
                <option value="300000-500000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '300000-500000') ? 'selected' : ''; ?>>300.000đ - 500.000đ</option>
                <option value="500000-1000000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '500000-1000000') ? 'selected' : ''; ?>>500.000đ - 1.000.000đ</option>
                <option value="1000000-0" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '1000000-0') ? 'selected' : ''; ?>>Trên 1.000.000đ</option>
            </select>
        </div>
        
        <button type="submit" class="btn">Tìm Kiếm</button>
    </form>
</div> 