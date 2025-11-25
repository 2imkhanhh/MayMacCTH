<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/Order.php';

$db = (new Database())->getConnection();
$order = new Order($db);

$stmt = $db->prepare("SELECT * FROM orders ORDER BY order_id DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "data" => $orders]);
?>