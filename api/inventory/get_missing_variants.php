<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/connect.php';

$db = (new Database())->getConnection();

$query = "
    SELECT 
        pv.variant_id,
        p.name AS product_name,
        pc.color_name,
        pc.color_code,
        pv.size
    FROM product_variants pv
    JOIN product_colors pc ON pv.color_id = pc.color_id
    JOIN products p ON pv.product_id = p.product_id
    LEFT JOIN product_inventory i ON pv.variant_id = i.variant_id
    WHERE i.inventory_id IS NULL
    ORDER BY p.name, pc.color_name, pv.size
";

$stmt = $db->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "data" => $data]);
?>