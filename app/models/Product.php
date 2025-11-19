<?php
class Product {
    private $conn;
    private $table = "products";

    public $product_id;
    public $category_id;
    public $name;
    public $color;
    public $size;
    public $price;
    public $image;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách + hỗ trợ tìm kiếm & lọc danh mục
    public function get($category_id = null, $name = null) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE 1=1";

        if ($category_id) {
            $query .= " AND p.category_id = :category_id";
        }
        if ($name) {
            $query .= " AND p.name LIKE :name";
        }

        $query .= " ORDER BY p.product_id DESC";

        $stmt = $this->conn->prepare($query);

        if ($category_id) $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        if ($name) $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy 1 sản phẩm theo ID
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table . " p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  SET category_id = :category_id,
                      name = :name,
                      color = :color,
                      size = :size,
                      price = :price,
                      image = :image";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->size = htmlspecialchars(strip_tags($this->size));
        $this->price = $this->price;
        $this->image = htmlspecialchars(strip_tags($this->image));

        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':size', $this->size);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image', $this->image);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET category_id = :category_id,
                      name = :name,
                      color = :color,
                      size = :size,
                      price = :price,
                      image = :image
                  WHERE product_id = :product_id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->size = htmlspecialchars(strip_tags($this->size));
        $this->price = $this->price;
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));

        $stmt->bindParam(':product_id', $this->product_id, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':size', $this->size);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image', $this->image);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>