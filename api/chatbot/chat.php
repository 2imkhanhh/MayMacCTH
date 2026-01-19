<?php

error_reporting(0); 
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$conn = null;
try {
    require_once '../../config/connect.php';
    $database = new Database();
    $conn = $database->getConnection();
    if ($conn === null) throw new Exception("Lỗi kết nối NULL");
} catch (Exception $e) {
    echo json_encode(['reply' => 'Lỗi Server: ' . $e->getMessage()]);
    exit;
}

$apiKey = 'AIzaSyCVesYZw5lBt0AF3zjEe3gZ9MSjgAxuvuE'; 
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $apiKey;

$inputData = json_decode(file_get_contents('php://input'), true);
$userMessage = $inputData['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Dạ, bạn cần mình giúp tìm mẫu nào không ạ?']);
    exit;
}

$dbContext = "";

try {
    $stopWords = ['có', 'không', 'những', 'màu', 'gì', 'nào', 'của', 'em', 'shop', 'ơi', 'lấy', 'cho', 'mình', 'xem'];
    $cleanMessage = str_replace($stopWords, '', mb_strtolower($userMessage, 'UTF-8'));
    $keywords = explode(' ', trim(preg_replace('/\s+/', ' ', $cleanMessage))); 
    
    if (empty($keywords)) $keywords = [$userMessage];

    $sqlConditions = [];
    $params = [];
    
    foreach ($keywords as $index => $word) {
        if (mb_strlen($word) > 1) { 
            $keyName = ":word{$index}";
            $sqlConditions[] = "(p.name LIKE $keyName OR p.description LIKE $keyName)";
            $params[$keyName] = "%" . $word . "%";
        }
    }

    if (empty($sqlConditions)) {
        throw new Exception("Không có từ khóa tìm kiếm hợp lệ");
    }

    $sqlWhere = implode(' AND ', $sqlConditions); 

    $sql = "
        SELECT 
            p.product_id,
            p.name, 
            p.price, 
            p.description,
            p.star,
            GROUP_CONCAT(DISTINCT v.size ORDER BY v.size ASC SEPARATOR ', ') as list_sizes,
            GROUP_CONCAT(DISTINCT c.color_name SEPARATOR ', ') as list_colors
        FROM products p
        LEFT JOIN product_variants v ON p.product_id = v.product_id
        LEFT JOIN product_colors c ON p.product_id = c.product_id
        WHERE ($sqlWhere) 
        GROUP BY p.product_id
        LIMIT 5
    ";

    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) > 0) {
        $dbContext .= "Đây là danh sách sản phẩm tìm thấy trong kho (Dựa trên từ khóa: '{$userMessage}'):\n";
        foreach ($products as $p) {
            $price = number_format($p['price'], 0, ',', '.') . 'đ';
            
            $colors = !empty($p['list_colors']) ? $p['list_colors'] : "Đang cập nhật (Khách hỏi thì bảo check kho sau)";
            $sizes = !empty($p['list_sizes']) ? $p['list_sizes'] : "Freesize / Đang cập nhật";
            $star = ($p['star'] > 0) ? "{$p['star']} sao" : "Chưa có đánh giá";
            
            $dbContext .= "- Tên: {$p['name']} (Mã: {$p['product_id']})\n";
            $dbContext .= "  + Giá: {$price}\n";
            $dbContext .= "  + Màu sắc: {$colors}\n"; 
            $dbContext .= "  + Size: {$sizes}\n";
            $dbContext .= "  + Đánh giá: {$star}\n";
            
            $descShort = mb_substr(strip_tags($p['description']), 0, 100) . "..."; 
            $dbContext .= "  + Mô tả: {$descShort}\n\n";
        }
    } else {
        $sqlRand = "SELECT name, price FROM products ORDER BY product_id DESC LIMIT 3";
        $stmtRand = $conn->prepare($sqlRand);
        $stmtRand->execute();
        $suggested = $stmtRand->fetchAll(PDO::FETCH_ASSOC);
        
        $dbContext .= "Hệ thống không tìm thấy sản phẩm nào khớp hoàn toàn với câu: '{$userMessage}'.\n";
        $dbContext .= "Hãy khéo léo báo khách là chưa tìm thấy mẫu đó và gợi ý các mẫu HOT này:\n";
        foreach ($suggested as $p) {
             $price = number_format($p['price'], 0, ',', '.') . 'đ';
             $dbContext .= "- {$p['name']} ({$price})\n";
        }
    }

} catch (Exception $e) {
    $dbContext = "Lỗi hệ thống khi tìm kiếm: " . $e->getMessage();
}

$systemInstruction = "
Bạn là trợ lý ảo của shop 'May Mặc CTH'.
DỮ LIỆU KHO HÀNG THỰC TẾ:
----------------
$dbContext
----------------

Yêu cầu:
1. Trả lời khách dựa trên dữ liệu trên.
2. Nếu sản phẩm có thông tin về Size và Màu, hãy liệt kê ra để khách chọn. (Ví dụ: 'Mẫu này bên em có màu Trắng, Đen và đủ size S, M đấy ạ').
3. Giọng điệu tự nhiên, ngắn gọn, chốt đơn khéo léo.
4. Không cần phải viết ra ID của sản phẩm
";

$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $systemInstruction . "\n\nKhách hỏi: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['reply' => 'Lỗi kết nối: ' . curl_error($ch)]);
} else {
    $json = json_decode($response, true);
    if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
        echo json_encode(['reply' => $json['candidates'][0]['content']['parts'][0]['text']]);
    } else {
        $err = $json['error']['message'] ?? 'Lỗi lạ';
        echo json_encode(['reply' => 'Hệ thống đang bảo trì chút xíu: ' . $err]);
    }
}
curl_close($ch);
?>