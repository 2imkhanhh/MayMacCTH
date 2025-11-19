<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ProductController.php';

$db = (new Database())->getConnection();
$controller = new ProductController($db);

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID không hợp lệ"]);
    exit;
}

$response = $controller->getById($id);

http_response_code($response['status'] ?? 200);
echo json_encode($response);