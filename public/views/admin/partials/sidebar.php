<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-chevron-left"></i>
    </div>
    <div class="logo">
        <img src="../../assets/images/logo2.png" alt="Logo">
    </div>
    <nav class="mt-4">
        <a href="dashboard.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2" style="margin-right: 5px;"></i><span>Dashboard</span>
        </a>
        <a href="banners.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'banners.php' ? 'active' : ''; ?>">
            <i class="bi bi-images" style="margin-right: 5px;"></i><span>Banner</span>
        </a>
        <a href="products.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'products.php' ? 'active' : ''; ?>">
            <i class="bi bi-box" style="margin-right: 5px;"></i><span>Sản phẩm</span>
        </a>
        <a href="categories.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'categories.php' ? 'active' : ''; ?>">
            <i class="bi bi-tags" style="margin-right: 5px;"></i><span>Danh mục</span>
        </a>
        <a href="about-us.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'about-us.php' ? 'active' : ''; ?>">
            <i class="bi bi-info-circle" style="margin-right: 5px;"></i><span>Về chúng tôi</span>
        </a>
        <a href="guides.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'guides.php' ? 'active' : ''; ?>">
            <i class="bi bi-book" style="margin-right: 5px;"></i><span>Hướng dẫn</span>
        </a>
        <a href="contacts.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'contacts.php' ? 'active' : ''; ?>">
            <i class="bi bi-envelope" style="margin-right: 5px;"></i><span>Liên hệ</span>
        </a>
        <a href="orders.php"
            class="nav-link d-flex align-items-center <?php echo in_array($currentPage, ['orders.php', 'order-detail.php']) ? 'active' : ''; ?>">
            <i class="bi bi-cart-check" style="margin-right: 5px;"></i><span>Đơn hàng</span>
            <span class="badge bg-danger ms-auto" style="margin-right: 5px;" id="pendingOrderCount"></span>
        </a>
        <a href="review-tags.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'review-tags.php' ? 'active' : ''; ?>">
            <i class="bi bi-star" style="margin-right: 5px;"></i><span>Đánh giá</span>
        </a>
        <a href="news.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>">
            <i class="bi bi-newspaper" style="margin-right: 5px;"></i><span>Tin tức</span>
        </a>
        <a href="inventory.php"
            class="nav-link d-flex align-items-center <?php echo $currentPage == 'inventory.php' ? 'active' : ''; ?>">
            <i class="bi bi-box-seam" style="margin-right: 5px;"></i><span>Kho hàng</span>
        </a>
    </nav>
</div>

<script>
    function updatePendingOrderCount() {
        fetch('/MayMacCTH/api/order/get_pending_orders_count.php')
            .then(response => response.text())
            .then(count => {
                const badge = document.getElementById('pendingOrderCount');
                count = parseInt(count);
                if (count > 0) {
                    badge.innerHTML = count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => {
                console.error('Lỗi load số đơn hàng:', err);
            });
    }

    updatePendingOrderCount();
    setInterval(updatePendingOrderCount, 15000);

    const toggleBtn = document.getElementById('sidebarToggle');

    if (localStorage.getItem('sidebar') === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
    }

    toggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');

        localStorage.setItem(
            'sidebar',
            document.body.classList.contains('sidebar-collapsed')
                ? 'collapsed'
                : 'expanded'
        );
    });
</script>