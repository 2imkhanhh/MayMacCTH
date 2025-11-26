<?php
class ReviewProduct {
    private $conn;
    private $table = "product_reviews";
    private $images_table = "product_review_images";
    private $links_table = "review_tag_links";

    // Các thuộc tính
    public $review_id;
    public $product_id;
    public $customer_name;
    public $phone;
    public $rating;
    public $size;
    public $color;
    public $content;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tạo đánh giá + ảnh + tag (transaction an toàn)
    public function create() {
        $this->conn->beginTransaction();

        try {
            // 1. Thêm đánh giá
            $query = "INSERT INTO {$this->table} 
                     (product_id, customer_name, phone, rating, size, color, content, created_at) 
                     VALUES (:product_id, :customer_name, :phone, :rating, :size, :color, :content, NOW())";

            $stmt = $this->conn->prepare($query);
            $this->sanitize();
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':rating', $this->rating);
            $stmt->bindParam(':size', $this->size);
            $stmt->bindParam(':color', $this->color);
            $stmt->bindParam(':content', $this->content);

            if (!$stmt->execute()) {
                throw new Exception("Lỗi tạo đánh giá");
            }

            $review_id = $this->conn->lastInsertId();

            // 2. Lưu ảnh (nếu có)
            if (!empty($this->images)) {
                $img_query = "INSERT INTO {$this->images_table} (review_id, image) VALUES (:review_id, :image)";
                $img_stmt = $this->conn->prepare($img_query);
                foreach ($this->images as $img) {
                    $img_stmt->execute(['review_id' => $review_id, 'image' => $img]);
                }
            }

            // 3. Lưu tag (nếu có)
            if (!empty($this->tag_ids)) {
                $tag_query = "INSERT IGNORE INTO {$this->links_table} (review_id, review_tag_id) VALUES (:review_id, :tag_id)";
                $tag_stmt = $this->conn->prepare($tag_query);
                foreach ($this->tag_ids as $tag_id) {
                    $tag_id = (int)$tag_id;
                    // Chỉ lưu tag đang active
                    $check = $this->conn->prepare("SELECT 1 FROM review_tags WHERE review_tag_id = ? AND is_active = 1");
                    $check->execute([$tag_id]);
                    if ($check->rowCount() > 0) {
                        $tag_stmt->execute(['review_id' => $review_id, 'tag_id' => $tag_id]);
                    }
                }
            }

            $this->conn->commit();
            return $review_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Review create error: " . $e->getMessage());
            return false;
        }
    }

    private function sanitize() {
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->size = htmlspecialchars(strip_tags($this->size));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->product_id = (int)$this->product_id;
        $this->rating = (int)$this->rating;
    }

    public $images = [];
    public $tag_ids = [];
}
?>