<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/ReviewProduct.php';

try {
    $db = (new Database())->getConnection();
    $review = new ReviewProduct($db);

    $data = $review->getAllReviewsWithProductName();

    echo json_encode([
        "success" => true,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
