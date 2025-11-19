<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/CategoryController.php';

$db = (new Database())->getConnection();
$controller = new CategoryController($db);

$response = $controller->create();

http_response_code($response['status'] ?? ($response['success'] ? 201 : 400));
echo json_encode($response);
?>