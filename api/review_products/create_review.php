<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Kết nối DB (giống hệt create_review_tag.php)
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ReviewProductController.php';

$db = (new Database())->getConnection();
$controller = new ReviewProductController($db);

// Gọi hàm create và trả về JSON
$response = $controller->create();
echo json_encode($response);
?>