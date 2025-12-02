<?php
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/Order.php';

$db = (new Database())->getConnection();
$order = new Order($db);

$count = $order->countPendingOrders();

echo $count; 
?>