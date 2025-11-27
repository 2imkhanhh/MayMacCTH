<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/models/ReviewProduct.php';

$db = (new Database())->getConnection();
$reviewModel = new ReviewProduct($db);

// Thư mục lưu ảnh
$uploadDir = __DIR__ . '/../../public/assets/images/upload/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadedImageNames = [];

// ============ 1. XỬ LÝ UPLOAD ẢNH (FormData) ============
if (!empty($_FILES['images']['name'][0])) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    foreach ($_FILES['images']['name'] as $key => $name) {
        $tmpName = $_FILES['images']['tmp_name'][$key];
        $size    = $_FILES['images']['size'][$key];
        $type    = $_FILES['images']['type'][$key];

        if (!in_array($type, $allowedTypes)) {
            echo json_encode(["success" => false, "message" => "Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP"]);
            exit;
        }
        if ($size > $maxSize) {
            echo json_encode(["success" => false, "message" => "Ảnh không quá 5MB"]);
            exit;
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = 'review_' . time() . '_' . $key . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = $uploadDir . $newName;

        if (move_uploaded_file($tmpName, $destination)) {
            $uploadedImageNames[] = $newName;
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi lưu ảnh"]);
            exit;
        }
    }
}

// ============ 2. LẤY DỮ LIỆU TỪ $_POST (vì dùng FormData) ============
$product_id     = $_POST['product_id'] ?? 0;
$customer_name  = trim($_POST['customer_name'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$rating         = (int)($_POST['rating'] ?? 0);
$size           = trim($_POST['size'] ?? '');
$color          = trim($_POST['color'] ?? '');
$content        = trim($_POST['content'] ?? '');

// Lấy tag_ids (có thể là mảng)
$tag_ids = [];
if (!empty($_POST['tag_ids'])) {
    $tag_ids = is_array($_POST['tag_ids']) ? $_POST['tag_ids'] : [$_POST['tag_ids']];
}
$tag_ids = array_map('intval', $tag_ids);

// Validate bắt buộc
if ($product_id <= 0 || empty($customer_name) || empty($phone) || $rating < 1 || $rating > 5 || empty($content)) {
    echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ thông tin bắt buộc"]);
    exit;
}

// Gán vào model
$reviewModel->product_id    = $product_id;
$reviewModel->customer_name = $customer_name;
$reviewModel->phone         = $phone;
$reviewModel->rating        = $rating;
$reviewModel->size          = $size;
$reviewModel->color         = $color;
$reviewModel->content       = $content;
$reviewModel->images        = $uploadedImageNames;
$reviewModel->tag_ids       = $tag_ids;

// ============ 3. TẠO ĐÁNH GIÁ ============
$reviewId = $reviewModel->create();

if ($reviewId) {
    echo json_encode([
        "success" => true,
        "message" => "Đánh giá được gửi thành công! Cảm ơn bạn đã tin tưởng và ủng hộ ❤️",
        "data" => ["review_id" => $reviewId]
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gửi đánh giá thất bại. Vui lòng thử lại!"
    ], JSON_UNESCAPED_UNICODE);
}
?>