<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/ReviewTag.php";

class ReviewTagController {
    private $tag;

    public function __construct($db) {
        $this->tag = new ReviewTag($db);
    }

    public function get() {
        $data = $this->tag->getAll();
        return ["success" => true, "data" => $data];
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        $content = trim($_POST['content'] ?? '');
        if (empty($content)) {
            return ["success" => false, "message" => "Nội dung tag không được để trống"];
        }

        $this->tag->content = $content;
        $this->tag->is_active = isset($_POST['is_active']) ? 1 : 0;

        return $this->tag->create()
            ? ["success" => true, "message" => "Thêm tag thành công!"]
            : ["success" => false, "message" => "Thêm thất bại, có thể tag đã tồn tại"];
    }

    public function update($id) {
        $id = (int)$id;
        $item = $this->tag->getById($id);
        if (!$item) {
            return ["success" => false, "message" => "Không tìm thấy tag"];
        }

        $content = trim($_POST['content'] ?? '');
        if (empty($content)) {
            return ["success" => false, "message" => "Nội dung không được để trống"];
        }

        $this->tag->review_tag_id = $id;
        $this->tag->content = $content;
        $this->tag->is_active = isset($_POST['is_active']) ? 1 : 0;

        return $this->tag->update()
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id) {
        $id = (int)$id;
        $item = $this->tag->getById($id);
        if (!$item) {
            return ["success" => false, "message" => "Không tìm thấy tag"];
        }

        return $this->tag->delete($id)
            ? ["success" => true, "message" => "Xóa tag thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }
}
?>