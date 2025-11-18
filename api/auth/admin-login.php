<?php
header("Content-Type: application/json");

require_once '../../app/controllers/AccountController.php';

$controller = new AccountController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    $result = $controller->adminLogin($username, $password);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}