<?php
// app/controllers/AccountController.php

require_once '../models/Account.php';

class AccountController {
    private $accountModel;

    public function __construct() {
        $this->accountModel = new Account();
    }

    // Đăng nhập dành riêng cho Admin
    public function adminLogin($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin'];
        }

        $user = $this->accountModel->findByUsernameOrPhone($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại'];
        }

        // Kiểm tra vai trò admin (giả sử cột role = 1 là admin)
        if ($user['role'] != 1) {
            return ['success' => false, 'message' => 'Bạn không có quyền truy cập khu vực quản trị'];
        }

        // Kiểm tra mật khẩu
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu không chính xác'];
        }

        // Đăng nhập thành công → tạo session
        session_start();
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_name']      = $user['name'] ?? $user['email'];
        $_SESSION['admin_email']     = $user['email'];
        $_SESSION['admin_logged_in'] = true;

        return [
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'redirect' => '../public/views/admin/dashboard.html' // bạn tạo sau
        ];
    }

    // Đăng xuất
    public function logout() {
        session_start();
        session_destroy();
        header('Location: ../../public/views/admin/login.html');
        exit;
    }
}