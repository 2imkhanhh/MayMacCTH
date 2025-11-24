<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/connect.php';

$id = $_GET['id'] ?? 0;
$db = (new Database())->getConnection();

$order = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
$order->execute([$id]);
$order = $order->fetch(PDO::FETCH_ASSOC);

$items = $db->prepare("
    SELECT oi.*, p.name as product_name 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$items->execute([$id]);
$items = $items->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "order" => $order,
    "items" => $items
]);