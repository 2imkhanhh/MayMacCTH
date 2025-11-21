<?php
class About {
    private $conn;
    private $table = "about_contents";

    public $about_id;
    public $title;
    public $content;
    public $image;
    public $display_order;
    public $section_type;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY display_order ASC, about_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET title = :title, content = :content, image = :image, 
                      display_order = :display_order, section_type = :section_type";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars($this->content ?? '');
        $this->image = htmlspecialchars(strip_tags($this->image ?? ''));
        $this->display_order = (int)$this->display_order;
        $this->section_type = $this->section_type;

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':section_type', $this->section_type);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, content = :content, image = :image, 
                      display_order = :display_order, section_type = :section_type
                  WHERE about_id = :about_id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars($this->content ?? '');
        $this->image = htmlspecialchars(strip_tags($this->image ?? ''));
        $this->display_order = (int)$this->display_order;
        $this->section_type = $this->section_type;
        $this->about_id = (int)$this->about_id;

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':section_type', $this->section_type);
        $stmt->bindParam(':about_id', $this->about_id);

        return $stmt->execute();
    }

    public function delete($id) {
        // Xóa ảnh cũ nếu có
        $old = $this->getById($id);
        if ($old && $old['image']) {
            $path = __DIR__ . '/../../public/assets/images/upload/' . $old['image'];
            if (file_exists($path)) @unlink($path);
        }

        $query = "DELETE FROM " . $this->table . " WHERE about_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE about_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>