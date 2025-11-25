<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ReviewTagController.php';

$id = $_GET['id'] ?? 0;
if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID không hợp lệ"]);
    exit;
}

$db = (new Database())->getConnection();
$controller = new ReviewTagController($db);

$response = $controller->delete($id);
echo json_encode($response);
?>