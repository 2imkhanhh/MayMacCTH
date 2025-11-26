<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/ReviewProduct.php';

$db = (new Database())->getConnection();
$reviewModel = new ReviewProduct($db);

$product_id = $_GET['id'] ?? 0;
if ($product_id <= 0) {
    echo json_encode(["success" => false, "message" => "Thiếu ID sản phẩm"]);
    exit;
}

// Lấy tất cả đánh giá của sản phẩm
$reviews = $reviewModel->getByProductId($product_id);

// Tính điểm trung bình và tổng số đánh giá
$total = count($reviews);
$avgRating = $total > 0 ? round(array_sum(array_column($reviews, 'rating')) / $total, 1) : 0;

echo json_encode([
    "success" => true,
    "data" => [
        "average_rating" => $avgRating,
        "total_reviews" => $total,
        "reviews" => $reviews
    ]
]);
?>