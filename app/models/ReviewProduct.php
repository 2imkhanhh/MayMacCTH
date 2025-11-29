<?php
class ReviewProduct
{
    private $conn;
    private $table = "product_reviews";
    private $images_table = "product_review_images";
    private $links_table = "review_tag_links";
    public $review_id;
    public $product_id;
    public $customer_name;
    public $phone;
    public $rating;
    public $size;
    public $color;
    public $content;
    public $created_at;

    public $images = [];   
    public $tag_ids = [];   

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        try {
            $this->conn->beginTransaction();
            $query = "INSERT INTO {$this->table} 
                     (product_id, customer_name, phone, rating, size, color, content, created_at) 
                     VALUES (:product_id, :customer_name, :phone, :rating, :size, :color, :content, NOW())";

            $stmt = $this->conn->prepare($query);
            $this->sanitize();

            $stmt->bindParam(':product_id', $this->product_id, PDO::PARAM_INT);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':rating', $this->rating, PDO::PARAM_INT);
            $stmt->bindParam(':size', $this->size);
            $stmt->bindParam(':color', $this->color);
            $stmt->bindParam(':content', $this->content);

            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                error_log("SQL INSERT ERROR: " . print_r($error, true));
                throw new Exception("Lỗi SQL: " . $error[2]);
            }

            $review_id = $this->conn->lastInsertId();

            if (!empty($this->images) && is_array($this->images)) {
                $img_query = "INSERT INTO {$this->images_table} (review_id, image) VALUES (:review_id, :image)";
                $img_stmt = $this->conn->prepare($img_query);
                $img_stmt->bindParam(':review_id', $review_id, PDO::PARAM_INT);

                foreach ($this->images as $image) {
                    $image = trim(htmlspecialchars(strip_tags($image)));
                    if ($image !== '') {
                        $img_stmt->bindParam(':image', $image);
                        $img_stmt->execute();
                    }
                }
            }

            if (!empty($this->tag_ids) && is_array($this->tag_ids)) {
                $tag_query = "INSERT IGNORE INTO {$this->links_table} (review_id, review_tag_id) 
                              VALUES (:review_id, :tag_id)";
                $tag_stmt = $this->conn->prepare($tag_query);
                $tag_stmt->bindParam(':review_id', $review_id, PDO::PARAM_INT);

                foreach ($this->tag_ids as $tag_id) {
                    $tag_id = (int)$tag_id;
                    if ($tag_id > 0) {
                        $check = $this->conn->prepare("SELECT 1 FROM review_tags WHERE review_tag_id = ? AND is_active = 1");
                        $check->execute([$tag_id]);
                        if ($check->rowCount() > 0) {
                            $tag_stmt->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
                            $tag_stmt->execute();
                        }
                    }
                }
            }

            $this->conn->commit();
            return $review_id; 

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("ReviewProduct create error: " . $e->getMessage());
            return false;
        }
    }

    private function sanitize()
    {
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->phone         = htmlspecialchars(strip_tags($this->phone));
        $this->size          = htmlspecialchars(strip_tags($this->size));
        $this->color         = htmlspecialchars(strip_tags($this->color));
        $this->content       = htmlspecialchars(strip_tags($this->content));
        $this->product_id    = (int)$this->product_id;
        $this->rating        = (int)$this->rating;
    }

    public function getByProductId($product_id)
    {
        $query = "SELECT pr.*, 
                         GROUP_CONCAT(ri.image) as images,
                         GROUP_CONCAT(rtl.review_tag_id) as tag_ids
                  FROM {$this->table} pr
                  LEFT JOIN {$this->images_table} ri ON pr.review_id = ri.review_id
                  LEFT JOIN {$this->links_table} rtl ON pr.review_id = rtl.review_id
                  WHERE pr.product_id = :product_id
                  GROUP BY pr.review_id
                  ORDER BY pr.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getByProductIdWithDetails($product_id, $limit = 10, $offset = 0)
    {
        $query = "SELECT 
                r.review_id,
                r.customer_name,
                r.phone,
                r.rating,
                r.content,
                r.size,
                r.color,
                r.created_at,
                GROUP_CONCAT(DISTINCT ri.image) AS image_list,
                GROUP_CONCAT(DISTINCT rt.content) AS tag_list
              FROM {$this->table} r
              LEFT JOIN {$this->images_table} ri ON ri.review_id = r.review_id
              LEFT JOIN {$this->links_table} rtl ON rtl.review_id = r.review_id
              LEFT JOIN review_tags rt ON rtl.review_tag_id = rt.review_tag_id AND rt.is_active = 1
              WHERE r.product_id = :product_id
              GROUP BY r.review_id
              ORDER BY r.created_at DESC
              LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit',      $limit,      PDO::PARAM_INT);
        $stmt->bindParam(':offset',     $offset,     PDO::PARAM_INT);

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reviews = [];
        foreach ($rows as $row) {
            $reviews[] = [
                "review_id"     => $row['review_id'],
                "customer_name" => $row['customer_name'] ?? 'Khách hàng',
                "phone"         => $row['phone'] ?? '',
                "rating"        => (int)($row['rating'] ?? 0),
                "content"       => $row['content'] ?? '',
                "size"          => $row['size'] ?? '',
                "color"         => $row['color'] ?? '',
                "created_at"    => $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '',
                "images"        => $row['image_list'] ? array_filter(explode(',', $row['image_list'])) : [],
                "tags"          => $row['tag_list'] ? array_filter(explode(',', $row['tag_list'])) : []
            ];
        }

        return $reviews;
    }

    public function getTotalReviewsByProductId($product_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE product_id = ?");
        $stmt->execute([$product_id]);
        return (int)$stmt->fetchColumn();
    }

    public function getAverageRating($product_id)
    {
        $stmt = $this->conn->prepare("SELECT AVG(rating) FROM {$this->table} WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $avg = $stmt->fetchColumn();
        return $avg ? (float)$avg : 0;
    }

    public function getAllReviewsWithProductName()
    {
        $query = "
            SELECT 
                r.review_id,
                r.product_id,
                r.customer_name,
                r.phone,
                r.rating,
                r.size,
                r.color,
                r.content,
                r.created_at,
                p.name AS product_name,
                GROUP_CONCAT(DISTINCT ri.image) AS images,
                GROUP_CONCAT(DISTINCT rt.content) AS tag_list
            FROM {$this->table} r
            LEFT JOIN products p ON p.product_id = r.product_id
            LEFT JOIN {$this->images_table} ri ON ri.review_id = r.review_id
            LEFT JOIN {$this->links_table} rtl ON rtl.review_id = r.review_id
            LEFT JOIN review_tags rt 
                ON rtl.review_tag_id = rt.review_tag_id 
                AND rt.is_active = 1
            GROUP BY r.review_id
            ORDER BY r.review_id DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $reviews = [];

        foreach ($rows as $row) {
            $reviews[] = [
                "review_id"     => $row['review_id'],
                "product_id"    => $row['product_id'],
                "product_name"  => $row['product_name'] ?? '',
                "customer_name" => $row['customer_name'] ?? 'Khách hàng',
                "phone"         => $row['phone'] ?? '',
                "rating"        => (int)($row['rating'] ?? 0),
                "content"       => $row['content'] ?? '',
                "size"          => $row['size'] ?? '',
                "color"         => $row['color'] ?? '',
                "created_at"    => $row['created_at']
                    ? date('d/m/Y H:i', strtotime($row['created_at']))
                    : '',
                "images"        => $row['images']
                    ? array_filter(explode(',', $row['images']))
                    : [],
                "tags"          => $row['tag_list']
                    ? array_filter(explode(',', $row['tag_list']))
                    : []
            ];
        }

        return $reviews;
    }

    public function getReviewById($review_id)
    {
        $query = "SELECT review_id FROM {$this->table} WHERE review_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $review_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        $imgStmt = $this->conn->prepare("SELECT image FROM {$this->images_table} WHERE review_id = :id");
        $imgStmt->bindParam(':id', $review_id, PDO::PARAM_INT);
        $imgStmt->execute();

        $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN); 

        return [
            'review_id' => $review_id,
            'images'    => is_array($images) ? $images : []
        ];
    }

    public function delete($review_id)
    {
        try {
            $this->conn->beginTransaction();

            $query1 = "DELETE FROM {$this->links_table} WHERE review_id = :id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(':id', $review_id, PDO::PARAM_INT);
            $stmt1->execute();

            $query2 = "DELETE FROM {$this->images_table} WHERE review_id = :id";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':id', $review_id, PDO::PARAM_INT);
            $stmt2->execute();

            $query3 = "DELETE FROM {$this->table} WHERE review_id = :id";
            $stmt3 = $this->conn->prepare($query3);
            $stmt3->bindParam(':id', $review_id, PDO::PARAM_INT);
            $stmt3->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("ReviewProduct delete error: " . $e->getMessage());
            return false;
        }
    }
}
