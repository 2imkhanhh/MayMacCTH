<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: *");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/BannerController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new BannerController($db);
$response = $controller->update();
http_response_code($response['status'] ?? 200);
echo json_encode(["message" => $response['message'], "success" => $response['success']]);