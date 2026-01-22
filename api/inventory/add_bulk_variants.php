<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/connect.php';
require_once '../../app/models/Inventory.php';

$db = (new Database())->getConnection();
$inventory = new Inventory($db);

$variant_ids = json_decode($_POST['variant_ids'] ?? '[]', true);
$quantity = (int)($_POST['quantity'] ?? 0);
$warehouse_id = (int)($_POST['warehouse_id'] ?? 1);

if (empty($variant_ids) || !is_array($variant_ids)) {
    echo json_encode(["success" => false, "message" => "Không có variant nào được chọn"]);
    exit;
}

$success_count = 0;
foreach ($variant_ids as $variant_id) {
    $variant_id = (int)$variant_id;
    if ($variant_id <= 0) continue;

    $stmt = $db->prepare("SELECT inventory_id FROM product_inventory WHERE variant_id = ? AND warehouse_id = ?");
    $stmt->execute([$variant_id, $warehouse_id]);
    if ($stmt->fetch()) continue; // đã có

    $created = $inventory->createForVariant($variant_id, $warehouse_id, $quantity, 10);
    if ($created) $success_count++;
}

echo json_encode(["success" => true, "message" => "Đã thêm $success_count variant vào kho"]);
?>