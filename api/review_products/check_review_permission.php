<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/ReviewProduct.php';
require_once __DIR__ . '/../../app/models/Order.php';

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);

$phone = trim($_GET['phone'] ?? '');
$product_id = $_GET['product_id'] ?? '';

if (empty($phone) || empty($product_id)) {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin"]);
    exit;
}

$cleanPhone = preg_replace('/\D/', '', $phone);
if (substr($cleanPhone, 0, 2) === '84') {
    $cleanPhone = '0' . substr($cleanPhone, 2);
}

if ($order->checkCustomerBoughtProduct($cleanPhone, $product_id)) {
    echo json_encode([
        "success"    => true,
        "can_review" => true,
        "customer_name" => $order->name ?: 'Khách hàng'
    ]);
} else {
    echo json_encode([
        "success"    => true,
        "can_review" => false,
        "message"    => "Bạn chỉ có thể đánh giá sản phẩm đã mua và đã nhận hàng thành công."
    ]);
}