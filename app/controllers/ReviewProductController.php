<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/ReviewProduct.php";

class ReviewProductController {
    private $review;

    public function __construct($db) {
        $this->review = new ReviewProduct($db);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        // Lấy dữ liệu từ JSON (frontend gửi JSON)
        $input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

        $required = ['product_id', 'customer_name', 'phone', 'rating', 'content'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin"];
            }
        }

        // Gán dữ liệu
        $this->review->product_id     = (int)$input['product_id'];
        $this->review->customer_name  = $input['customer_name'];
        $this->review->phone          = $input['phone'];
        $this->review->rating         = (int)$input['rating'];
        $this->review->size           = $input['size'] ?? '';
        $this->review->color          = $input['color'] ?? '';
        $this->review->content        = $input['content'];
        $this->review->images         = $input['images'] ?? [];       
        $this->review->tag_ids        = $input['tag_ids'] ?? [];     

        if ($this->review->create()) {
            return ["success" => true, "message" => "Gửi đánh giá thành công! Cảm ơn bạn "];
        } else {
            return ["success" => false, "message" => "Gửi đánh giá thất bại. Vui lòng thử lại!"];
        }
    }
}
?>