<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/CategoryController.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Thiếu ID"]);
    exit;
}

$db = (new Database())->getConnection();
$controller = new CategoryController($db);

$response = $controller->update($id);

http_response_code($response['status'] ?? 200);
echo json_encode($response);
?>