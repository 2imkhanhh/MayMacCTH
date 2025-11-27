<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/ReviewProduct.php';

try {
    $db = (new Database())->getConnection();
    $reviewModel = new ReviewProduct($db);

    $product_id = $_GET['id'] ?? 0;
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $limit      = (int)($_GET['limit'] ?? 3);
    $limit      = $limit > 0 ? $limit : 3;  // Không cho limit <= 0
    $offset     = ($page - 1) * $limit;

    if ($product_id <= 0) {
        echo json_encode(["success" => false, "message" => "Thiếu ID sản phẩm"]);
        exit;
    }

    $reviews      = $reviewModel->getByProductIdWithDetails($product_id, $limit, $offset);
    $totalReviews = $reviewModel->getTotalReviewsByProductId($product_id);
    $avgRating    = $totalReviews > 0 ? round($reviewModel->getAverageRating($product_id), 1) : 0;

    echo json_encode([
        "success" => true,
        "data" => [
            "average_rating" => $avgRating,
            "total_reviews"   => $totalReviews,
            "total_pages"     => ceil($totalReviews / $limit),  
            "current_page"    => $page,
            "reviews"         => $reviews
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}
?>