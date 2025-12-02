<?php
require_once __DIR__ . '/../models/Account.php';

class AccountController {
    private $accountModel;

    public function __construct() {
        $this->accountModel = new Account();
    }

    public function adminLogin($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Vui lòng nhập đầy đủ'];
        }

        $user = $this->accountModel->findByName($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Tên đăng nhập không tồn tại'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu sai'];
        }

        $_SESSION['admin_id'] = $user['id'] ?? 1;
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();

        return [
            'success' => true,
            'message' => 'Đăng nhập thành công!'
        ];
    }
}