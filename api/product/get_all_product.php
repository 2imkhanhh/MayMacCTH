<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ProductController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new ProductController($db);

try {
    $category_id = $_GET['category_id'] ?? null;
    $name = $_GET['name'] ?? null;

    $response = $controller->getAll();

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server: " . $e->getMessage(),
        "data" => []
    ]);
}
?>