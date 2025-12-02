<?php
header("Content-Type: application/json");

session_set_cookie_params([
    'lifetime' => 0,           
    'path'     => '/',
    'domain'   => '',              
    'secure'   => true,        
    'httponly' => true,       
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/../../app/controllers/AccountController.php';

$controller = new AccountController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    $result = $controller->adminLogin($username, $password);
    if ($result['success']) {
        $_SESSION['last_activity'] = time();
    }

    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}