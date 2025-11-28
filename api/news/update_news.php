<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/NewsController.php';

$db = (new Database())->getConnection();
$controller = new NewsController($db);

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo json_encode(["success" => false, "message" => "ID không hợp lệ"]);
    exit;
}

$result = $controller->update($id);
echo json_encode($result);
?>