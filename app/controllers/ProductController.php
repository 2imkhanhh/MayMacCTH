<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Product.php";

class ProductController {
    private $product;

    public function __construct($db) {
        $this->product = new Product($db);
    }

    public function get() {
        $category_id = $_GET['category_id'] ?? null;
        $name = $_GET['name'] ?? null;

        $products = $this->product->get($category_id, $name);

        return [
            "success" => true,
            "message" => "Danh sách sản phẩm",
            "data" => $products
        ];
    }

    public function getById($id) {
        $product = $this->product->getById($id);
        if (!$product) {
            return ["success" => false, "message" => "Không tìm thấy sản phẩm", "status" => 404];
        }
        return ["success" => true, "message" => "Chi tiết sản phẩm", "data" => [$product]];
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed", "status" => 405];
        }

        if (empty($_POST['name']) || empty($_POST['category_id']) || empty($_POST['price'])) {
            return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin bắt buộc", "status" => 400];
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
            return ["success" => false, "message" => "Vui lòng chọn ảnh sản phẩm", "status" => 400];
        }

        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'product_' . time() . '_' . rand(1000,9999) . '.' . strtolower($ext);
        $uploadPath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            return ["success" => false, "message" => "Upload ảnh thất bại", "status" => 500];
        }

        $this->product->category_id = (int)$_POST['category_id'];
        $this->product->name = $_POST['name'];
        $this->product->color = $_POST['color'] ?? null;
        $this->product->size = $_POST['size'] ?? null;
        $this->product->price = $_POST['price'];
        $this->product->image = $imageName;
        $this->product->is_active = isset($_POST['is_active']) ? 1 : 0;

        return $this->product->create()
            ? ["success" => true, "message" => "Thêm sản phẩm thành công!"]
            : ["success" => false, "message" => "Thêm sản phẩm thất bại"];
    }

    public function update($id) {
        $product = $this->product->getById($id);
        if (!$product) {
            return ["success" => false, "message" => "Không tìm thấy sản phẩm", "status" => 404];
        }

        if (empty($_POST['name']) || empty($_POST['category_id']) || empty($_POST['price'])) {
            return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin bắt buộc", "status" => 400];
        }

        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
        $imageName = $product['image']; // giữ ảnh cũ

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            // Xóa ảnh cũ
            $oldPath = $uploadDir . $product['image'];
            if (file_exists($oldPath)) @unlink($oldPath);

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'product_' . time() . '_' . rand(1000,9999) . '.' . strtolower($ext);
            $uploadPath = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                return ["success" => false, "message" => "Upload ảnh mới thất bại"];
            }
        }

        $this->product->product_id = $id;
        $this->product->category_id = (int)$_POST['category_id'];
        $this->product->name = $_POST['name'];
        $this->product->color = $_POST['color'] ?? null;
        $this->product->size = $_POST['size'] ?? null;
        $this->product->price = $_POST['price'];
        $this->product->image = $imageName;
        $this->product->is_active = isset($_POST['is_active']) ? 1 : 0;

        return $this->product->update()
            ? ["success" => true, "message" => "Cập nhật sản phẩm thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return ["success" => false, "message" => "Method not allowed", "status" => 405];
        }

        $product = $this->product->getById($id);
        if (!$product) {
            return ["success" => false, "message" => "Không tìm thấy sản phẩm"];
        }

        // Xóa ảnh
        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
        $imagePath = $uploadDir . $product['image'];
        if (file_exists($imagePath)) @unlink($imagePath);

        return $this->product->delete($id)
            ? ["success" => true, "message" => "Xóa sản phẩm thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }
}