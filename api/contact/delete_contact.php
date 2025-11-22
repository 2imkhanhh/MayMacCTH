<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ContactController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

parse_str(file_get_contents("php://input"), $input);
$id = $_GET['id'] ?? $input['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Thiếu ID liên hệ"]);
    exit;
}

$db = (new Database())->getConnection();
$controller = new ContactController($db);

$response = $controller->delete($id);

http_response_code($response['success'] ? 200 : 400);
echo json_encode($response);
?>