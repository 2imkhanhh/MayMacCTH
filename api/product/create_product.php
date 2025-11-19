<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ProductController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new ProductController($db);

$response = $controller->create();

http_response_code($response['status'] ?? 200);
echo json_encode([
    "success" => $response['success'],
    "message" => $response['message']
]);
?>