<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ContactController.php';

$id = $_GET['id'] ?? $_POST['contact_id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Thiếu ID liên hệ"]);
    exit;
}

$db = (new Database())->getConnection();
$controller = new ContactController($db);

$response = $controller->update($id);

http_response_code($response['success'] ? 200 : 400);
echo json_encode($response);
?>