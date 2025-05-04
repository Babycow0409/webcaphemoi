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
                <?php
                // Lấy danh sách danh mục từ database
                $categories_query = "SELECT * FROM categories ORDER BY name";
                $categories_result = $conn->query($categories_query);
                if ($categories_result && $categories_result->num_rows > 0) {
                    while ($cat = $categories_result->fetch_assoc()) {
                        $selected = (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '';
                        echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="category" value="<?php echo isset($categoryId) ? $categoryId : (isset($currentCategory) ? $currentCategory : ''); ?>">
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