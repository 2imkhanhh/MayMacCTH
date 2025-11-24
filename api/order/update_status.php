<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$order_id = $input['order_id'] ?? 0;
$order_status = $input['order_status'] ?? '';
$payment_status = $input['payment_status'] ?? '';

if (!$order_id || !in_array($order_status, ['pending','confirmed','completed','cancelled']) || !in_array($payment_status, ['unpaid','paid'])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

$db = (new Database())->getConnection();
$stmt = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE order_id = ?");
$success = $stmt->execute([$order_status, $payment_status, $order_id]);

echo json_encode([
    "success" => $success,
    "message" => $success ? "Cập nhật thành công" : "Cập nhật thất bại"
]);