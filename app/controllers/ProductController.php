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
            return ["success" => false, "message" => "Không tìm thấy", "status" => 404];
        }
        return ["success" => true, "data" => [$product]];
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        // ĐÃ THÊM PRICE VÀO ĐÂY – QUAN TRỌNG NHẤT!
        $data = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price'       => (int)($_POST['price'] ?? 0),        // THÊM DÒNG NÀY
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0
        ];

        if (empty($data['name']) || empty($data['category_id'])) {
            return ["success" => false, "message" => "Thiếu tên hoặc danh mục"];
        }

        $colors = [];
        foreach ($_POST['colors'] ?? [] as $c) {
            if (!empty($c['name'])) {
                $colors[] = [
                    'name'  => $c['name'],
                    'code'  => $c['code'] ?? '#000000',
                    'sizes' => $c['sizes'] ?? ''
                ];
            }
        }

        $primaryIndex = (int)($_POST['primary_image'] ?? 0);

        if (empty($colors)) {
            return ["success" => false, "message" => "Phải có ít nhất 1 màu"];
        }

        $uploadedFiles = [];
        if (isset($_FILES['images']['tmp_name'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
                if ($_FILES['images']['error'][$key] === 0) {
                    $uploadedFiles[] = [
                        'name'     => $_FILES['images']['name'][$key],
                        'tmp_name' => $tmp,
                        'error'    => 0
                    ];
                }
            }
        }

        if (empty($uploadedFiles)) {
            return ["success" => false, "message" => "Phải có ít nhất 1 ảnh"];
        }

        $result = $this->product->create($data, $colors, $uploadedFiles, $primaryIndex);
        return $result
            ? ["success" => true, "message" => "Thêm sản phẩm thành công!"]
            : ["success" => false, "message" => "Thêm thất bại!"];
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        // ĐÃ THÊM PRICE VÀO CẬP NHẬT
        $data = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price'       => (int)($_POST['price'] ?? 0),       
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0
        ];

        if (empty($data['name']) || empty($data['category_id'])) {
            return ["success" => false, "message" => "Thiếu thông tin bắt buộc"];
        }

        $existingImages = $_POST['existing_images'] ?? [];
        if (is_string($existingImages)) $existingImages = [$existingImages];
        $existingImages = array_map('intval', $existingImages);

        $colors = [];
        foreach ($_POST['colors'] ?? [] as $c) {
            if (!empty($c['name'])) {
                $colors[] = [
                    'name'  => $c['name'],
                    'code'  => $c['code'] ?? '#000000',
                    'sizes' => $c['sizes'] ?? ''
                ];
            }
        }

        $primaryIndex = (int)($_POST['primary_image'] ?? 0);

        $uploadedFiles = [];
        if (isset($_FILES['images']['tmp_name'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
                if ($_FILES['images']['error'][$key] === 0) {
                    $uploadedFiles[] = [
                        'name'     => $_FILES['images']['name'][$key],
                        'tmp_name' => $tmp,
                        'error'    => 0
                    ];
                }
            }
        }

        $result = $this->product->update($id, $data, $colors, $uploadedFiles, $primaryIndex, $existingImages);
        return $result
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id) {
        $result = $this->product->delete($id);
        return $result
            ? ["success" => true, "message" => "Xóa thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }

    public function getAll() {
        $category_id = $_GET['category_id'] ?? null;
        $name = $_GET['name'] ?? null;
        
        $products = $this->product->getAll($category_id, $name);

        return [
            "success" => true,
            "message" => "Danh sách tất cả sản phẩm",
            "data" => $products
        ];
    }
}