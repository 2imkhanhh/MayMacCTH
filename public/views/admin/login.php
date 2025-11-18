<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/login.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-login-body">

<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="../../assets/images/logo.png" alt="Logo" class="admin-logo">
            <h3 class="mt-3 fw-bold text-danger">QUẢN TRỊ HỆ THỐNG</h3>
        </div>

        <form id="loginForm">
            <div class="mb-3">
                <label class="form-label">Email hoặc Số điện thoại</label>
                <input type="text" class="form-control" id="username" required placeholder="Nhập email hoặc số điện thoại">
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" required placeholder="Nhập mật khẩu">
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit" class="btn btn-danger w-100 fw-bold" id="btnLogin">
                <span class="btn-text">ĐĂNG NHẬP</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>

        <div class="mt-4 text-center">
            <!-- <small class="text-muted">May mặc CTH - Admin Panel</small> -->
            <!-- <small class="text-muted">© May mặc CTH - Admin Panel</small> -->
        </div>
    </div>
</div>

<script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../js/admin-login.js"></script>
</body>
</html>