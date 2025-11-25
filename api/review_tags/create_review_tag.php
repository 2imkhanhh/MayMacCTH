<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/ReviewTagController.php';

$db = (new Database())->getConnection();
$controller = new ReviewTagController($db);

$response = $controller->create();
echo json_encode($response);
?>