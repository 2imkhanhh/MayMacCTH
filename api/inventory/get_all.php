<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/connect.php';
require_once '../../app/controllers/InventoryController.php';

try {
    $db = (new Database())->getConnection();
    $ctrl = new InventoryController($db);

    $response = $ctrl->getAll();

    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server: " . $e->getMessage()
    ]);
}