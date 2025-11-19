<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Category.php";

class CategoryController {
    private $category;

    public function __construct($db) {
        $this->category = new Category($db);
    }

    public function get() {
        $categories = $this->category->get();
        return [
            "success" => true,
            "message" => "Danh sách danh mục",
            "data" => $categories
        ];
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed", "status" => 405];
        }

        if (empty($_POST['name'])) {
            return ["success" => false, "message" => "Tên danh mục không được để trống", "status" => 400];
        }

        $this->category->name = $_POST['name'];

        if ($this->category->create()) {
            return ["success" => true, "message" => "Thêm danh mục thành công!"];
        } else {
            return ["success" => false, "message" => "Tên danh mục đã tồn tại hoặc lỗi hệ thống"];
        }
    }

    public function update($id) {
        $cat = $this->category->getById($id);
        if (!$cat) {
            return ["success" => false, "message" => "Không tìm thấy danh mục", "status" => 404];
        }

        if (empty($_POST['name'])) {
            return ["success" => false, "message" => "Tên danh mục không được để trống", "status" => 400];
        }

        $this->category->category_id = $id;
        $this->category->name = $_POST['name'];

        return $this->category->update()
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại hoặc tên đã tồn tại"];
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return ["success" => false, "message" => "Method not allowed", "status" => 405];
        }

        $cat = $this->category->getById($id);
        if (!$cat) {
            return ["success" => false, "message" => "Không tìm thấy danh mục"];
        }

        if (!$this->category->delete($id)) {
            return ["success" => false, "message" => "Không thể xóa! Danh mục đang chứa sản phẩm."];
        }

        return ["success" => true, "message" => "Xóa danh mục thành công!"];
    }
}