<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../../controllers/ReviewProductController.php";

$controller = new ReviewProductController($pdo);
$response = $controller->create();

echo json_encode($response);
?>