<?php
class Guide {
    private $conn;
    private $table = "guides";

    public $guide_id;
    public $title;
    public $catalog;
    public $content;
    public $image;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY catalog, guide_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE guide_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET title = :title, catalog = :catalog, content = :content, image = :image";
        $stmt = $this->conn->prepare($query);

        $this->title    = htmlspecialchars(strip_tags($this->title));
        $this->catalog  = $this->catalog;
        $this->content  = htmlspecialchars($this->content);
        $this->image    = htmlspecialchars(strip_tags($this->image));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':catalog', $this->catalog);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image', $this->image);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, catalog = :catalog, content = :content, image = :image 
                  WHERE guide_id = :guide_id";
        $stmt = $this->conn->prepare($query);

        $this->title    = htmlspecialchars(strip_tags($this->title));
        $this->catalog  = $this->catalog;
        $this->content  = htmlspecialchars($this->content);
        $this->image    = htmlspecialchars(strip_tags($this->image));
        $this->guide_id = $this->guide_id;

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':catalog', $this->catalog);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':guide_id', $this->guide_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE guide_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>