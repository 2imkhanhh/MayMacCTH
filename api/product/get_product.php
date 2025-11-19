<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ProductController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new ProductController($db);

if (isset($_GET['id'])) {
    $response = $controller->getById($_GET['id']);
} else {
    $response = $controller->get();
}

http_response_code($response['status'] ?? 200);
echo json_encode([
    "success" => $response['success'],
    "message" => $response['message'],
    "data" => $response['data'] ?? []
]);
?>