<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/connect.php';

try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT new_category_id, name FROM news_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "data" => $categories]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Lỗi server"]);
}
?>