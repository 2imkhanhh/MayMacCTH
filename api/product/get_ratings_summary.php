<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';

try {
    $db = (new Database())->getConnection();
    $idsParam = $_GET['ids'] ?? '';

    if (empty($idsParam)) {
        echo json_encode([
            "success" => false,
            "message" => "Thiếu tham số ids"
        ]);
        exit;
    }

    $ids = array_filter(array_map('intval', explode(',', $idsParam)));
    if (empty($ids)) {
        echo json_encode([
            "success" => false,
            "message" => "Không có ID hợp lệ"
        ]);
        exit;
    }

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $sql = "
        SELECT 
            product_id,
            ROUND(COALESCE(AVG(rating), 0), 1) AS average_rating,
            COUNT(*) AS review_count
        FROM product_reviews 
        WHERE product_id IN ($placeholders) 
          AND rating > 0
        GROUP BY product_id
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($ids);

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[$row['product_id']] = [
            'average_rating' => (float)$row['average_rating'],
            'review_count'   => (int)$row['review_count']
        ];
    }

    echo json_encode([
        "success" => true,
        "data"    => $result
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Lỗi server: " . $e->getMessage()
    ]);
}
?>