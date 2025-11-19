<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Quán Mì Cay 7 Cấp Độ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin-style.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.css">
</head>
<body>

<!-- Sidebar -->
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div>
            <h1 class="page-title">DASHBOARD QUẢN TRỊ</h1>
            <!-- <small class="text-muted">Chào mừng trở lại, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong></small> -->
        </div>
        <button class="btn logout-btn text-white" id="logoutBtnTop">
            <i class="bi bi-box-arrow-right"></i> Đăng xuất
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="card card-stat bg-primary text-white">
                <div class="card-body">
                    <h3>Tổng đơn hàng</h3>
                    <p class="mb-0">2,845</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-stat bg-success text-white">
                <div class="card-body">
                    <h3>Doanh thu tháng</h3>
                    <p class="mb-0">₫148,520,000</p>
                </div>
            </div>
        </div>
        <!-- <div class="col-lg-3 col-md-6">
            <div class="card card-stat bg-warning text-white">
                <div class="card-body">
                    <h3>892</h3>
                    <p class="mb-0">Khách hàng mới</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-stat bg-danger text-white">
                <div class="card-body">
                    <h3>12</h3>
                    <p class="mb-0">Sản phẩm sắp hết</p>
                </div>
            </div>
        </div> -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Đăng xuất
    document.querySelectorAll('#logoutBtn, #logoutBtnTop').forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                window.location.href = 'logout.php'; // mình làm luôn file này cho bạn dưới đây
            }
        });
    });
</script>
</body>
</html>