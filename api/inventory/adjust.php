<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/connect.php';
require_once '../../app/controllers/InventoryController.php';

$db = (new Database())->getConnection();
$ctrl = new InventoryController($db);
$response = $ctrl->adjust();

http_response_code($response['success'] ? 200 : 400);
echo json_encode($response);