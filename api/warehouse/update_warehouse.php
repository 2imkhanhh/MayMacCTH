<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/connect.php';
require_once '../../app/controllers/WarehouseController.php';

try {
    $db = (new Database())->getConnection();
    $controller = new WarehouseController($db);
    echo json_encode($controller->update());
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>