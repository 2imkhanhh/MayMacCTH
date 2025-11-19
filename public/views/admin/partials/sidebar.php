<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="logo">
        <img src="../../assets/images/logo2.png" alt="Logo">
    </div>
    <nav class="mt-4">
        <a href="dashboard.php" 
           class="nav-link d-flex align-items-center <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
        <a href="banners.php" 
           class="nav-link d-flex align-items-center <?php echo $currentPage == 'banners.php' ? 'active' : ''; ?>">
            <i class="bi bi-images"></i><span>Banner</span>
        </a>
        <a href="orders.php" 
           class="nav-link d-flex align-items-center <?php echo in_array($currentPage, ['orders.php', 'order-detail.php']) ? 'active' : ''; ?>">
            <i class="bi bi-cart-check"></i><span>Đơn hàng</span>
            <span class="badge bg-danger ms-auto">147</span>
        </a>
        <a href="customers.php" 
           class="nav-link d-flex align-items-center <?php echo $currentPage == 'customers.php' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i><span>Khách hàng</span>
        </a>
        <a href="news.php" 
           class="nav-link d-flex align-items-center <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>">
            <i class="bi bi-newspaper"></i><span>Tin tức</span>
        </a>
        <a href="login.php" 
           class="nav-link d-flex align-items-center <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>">
            <i class="bi bi-box-arrow-right"></i><span>Đăng xuất</span>
        </a>
    </nav>
</div>
