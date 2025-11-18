<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/BannerController.php';

$database = new Database();
$db = $database->getConnection();
$controller = new BannerController($db);
$response = $controller->create();
http_response_code($response['status'] ?? 200);
echo json_encode(["message" => $response['message'], "success" => $response['success']]);
?>