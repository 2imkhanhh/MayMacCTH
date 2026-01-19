<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = :id AND p.is_active = 1"; 

try {
    $stmt = $conn->prepare($sql);
    
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $images_decoded = isset($product['images']) ? json_decode($product['images'], true) : [];
    $product['images_list'] = is_array($images_decoded) ? $images_decoded : [];

    if (!empty($product['primary_image']) && !in_array($product['primary_image'], $product['images_list'])) {
        array_unshift($product['images_list'], $product['primary_image']);
    }

    echo json_encode([
        'success' => true,
        'data' => $product
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi truy vấn: ' . $e->getMessage()]);
}
?>