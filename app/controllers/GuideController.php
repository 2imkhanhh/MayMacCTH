<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Guide.php";

class GuideController {
    private $guide;

    public function __construct($db) {
        $this->guide = new Guide($db);
    }

    public function getAll() {
        $guides = $this->guide->getAll();
        return ["success" => true, "data" => $guides];
    }

    public function create() {
        if (empty($_POST['title']) || empty($_POST['catalog'])) {
            return ["success" => false, "message" => "Tiêu đề và loại hướng dẫn không được để trống"];
        }

        $this->guide->title    = $_POST['title'];
        $this->guide->catalog  = $_POST['catalog'];
        $this->guide->content  = $_POST['content'] ?? '';
        $this->guide->image    = $_POST['image'] ?? '';

        return $this->guide->create()
            ? ["success" => true, "message" => "Thêm hướng dẫn thành công!"]
            : ["success" => false, "message" => "Thêm thất bại"];
    }

    public function update($id) {
        $guide = $this->guide->getById($id);
        if (!$guide) return ["success" => false, "message" => "Không tìm thấy"];

        $this->guide->guide_id = $id;
        $this->guide->title    = $_POST['title'] ?? $guide['title'];
        $this->guide->catalog  = $_POST['catalog'] ?? $guide['catalog'];
        $this->guide->content  = $_POST['content'] ?? $guide['content'];
        $this->guide->image    = $_POST['image'] ?? $guide['image'];

        return $this->guide->update()
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id) {
        if ($this->guide->delete($id)) {
            return ["success" => true, "message" => "Xóa thành công!"];
        }
        return ["success" => false, "message" => "Xóa thất bại"];
    }
}
?>