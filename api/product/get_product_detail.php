<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ? AND p.is_active = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

$product = $result->fetch_assoc();

// Xử lý ảnh phụ
$product['images_list'] = $product['images'] ? json_decode($product['images'], true) : [];
if ($product['primary_image'] && !in_array($product['primary_image'], $product['images_list'])) {
    array_unshift($product['images_list'], $product['primary_image']);
}

echo json_encode([
    'success' => true,
    'data' => $product
]);