<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ContactController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$db = (new Database())->getConnection();
$controller = new ContactController($db);

$response = $controller->create();

http_response_code($response['success'] ? 201 : 400);
echo json_encode($response);
?>