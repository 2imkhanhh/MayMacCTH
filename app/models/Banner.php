<?php
require_once __DIR__ . '/../../config/connect.php';
class Banner {
    private $conn;
    private $table_name = "banners";

    public $banner_id;
    public $title;
    public $image;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, image=:image, is_active=:is_active";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->is_active = $this->is_active ? 1 : 0;

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_active", $this->is_active);

        return $stmt->execute();
    }

    public function get() {
        $query = "SELECT banner_id, title, image, is_active FROM " . $this->table_name . " ORDER BY banner_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE banner_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, image=:image, is_active=:is_active 
                  WHERE banner_id=:banner_id";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->is_active = $this->is_active ? 1 : 0;
        $this->banner_id = htmlspecialchars(strip_tags($this->banner_id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":banner_id", $this->banner_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE banner_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>