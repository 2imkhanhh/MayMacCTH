<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/ReviewProduct.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["success" => false, "message" => "Phương thức không được hỗ trợ"]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $review_id = $input['review_id'] ?? null;

    if (!$review_id || !is_numeric($review_id)) {
        echo json_encode(["success" => false, "message" => "ID đánh giá không hợp lệ"]);
        exit;
    }

    $db = (new Database())->getConnection();
    $reviewModel = new ReviewProduct($db);

    $review = $reviewModel->getReviewById($review_id); 

    if (!$review) {
        echo json_encode(["success" => false, "message" => "Không tìm thấy đánh giá"]);
        exit;
    }

    $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
    if (!empty($review['images'])) {
        foreach ($review['images'] as $image) {
            $filePath = $uploadDir . $image;
            if (file_exists($filePath)) {
                @unlink($filePath); 
            }
        }
    }

    $deleted = $reviewModel->delete($review_id); 

    if ($deleted) {
        echo json_encode([
            "success" => true,
            "message" => "Xóa đánh giá thành công!"
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Xóa đánh giá thất bại. Vui lòng thử lại."
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>