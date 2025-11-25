<?php
class ReviewTag {
    private $conn;
    private $table = "review_tags";

    public $review_tag_id;
    public $content;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT review_tag_id, content, is_active FROM " . $this->table . " ORDER BY review_tag_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET content = :content, is_active = :is_active";
        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->is_active = $this->is_active ? 1 : 0;

        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':is_active', $this->is_active);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET content = :content, is_active = :is_active WHERE review_tag_id = :id";
        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->is_active = $this->is_active ? 1 : 0;
        $this->review_tag_id = (int)$this->review_tag_id;

        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->review_tag_id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE review_tag_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE review_tag_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>