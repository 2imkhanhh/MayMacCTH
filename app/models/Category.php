<?php
class Category {
    private $conn;
    private $table = "categories";

    public $category_id;
    public $name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT c.*, 
                         COUNT(p.product_id) as product_count
                  FROM " . $this->table . " c
                  LEFT JOIN products p ON c.category_id = p.category_id
                  GROUP BY c.category_id
                  ORDER BY c.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET name = :name";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));

        $stmt->bindParam(':name', $this->name);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET name = :name WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':category_id', $this->category_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $check = "SELECT COUNT(*) FROM products WHERE category_id = :id";
        $stmt = $this->conn->prepare($check);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return false; 
        }

        $query = "DELETE FROM " . $this->table . " WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>