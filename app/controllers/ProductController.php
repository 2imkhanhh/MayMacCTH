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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return ["success" => false, "message" => "Method not allowed"];

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category_id' => $_POST['category_id'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if (empty($data['name']) || empty($data['category_id'])) {
            return ["success" => false, "message" => "Thiếu thông tin bắt buộc"];
        }

        // Lấy màu từ form: colors[0][name], colors[0][sizes], ...
        $colors = [];
        foreach ($_POST['colors'] ?? [] as $c) {
            if (!empty($c['name'])) {
                $colors[] = [
                    'name' => $c['name'],
                    'code' => $c['code'] ?? '#000000',
                    'sizes' => $c['sizes'] ?? ''
                ];
            }
        }

        $primaryIndex = (int)($_POST['primary_image'] ?? 0);

        if (empty($colors) || empty($_FILES['images']['name'][0] ?? null)) {
            return ["success" => false, "message" => "Phải có ít nhất 1 màu và 1 ảnh"];
        }

        $uploadedFiles = [];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            $uploadedFiles[] = [
                'name' => $_FILES['images']['name'][$key],
                'tmp_name' => $tmp,
                'error' => $_FILES['images']['error'][$key]
            ];
        }

        return $this->product->create($data, $colors, $uploadedFiles, $primaryIndex)
            ? ["success" => true, "message" => "Thêm sản phẩm thành công!"]
            : ["success" => false, "message" => "Thêm thất bại. Vui lòng thử lại."];
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category_id' => $_POST['category_id'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if (empty($data['name']) || empty($data['category_id'])) {
            return ["success" => false, "message" => "Thiếu thông tin bắt buộc"];
        }

        // Lấy danh sách ảnh hiện tại muốn giữ lại (image_id)
        $existingImages = [];
        if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
            $existingImages = array_map('intval', $_POST['existing_images']);
        }

        // Xử lý màu
        $colors = [];
        foreach ($_POST['colors'] ?? [] as $c) {
            if (!empty($c['name'])) {
                $colors[] = [
                    'name' => $c['name'],
                    'code' => $c['code'] ?? '#000000',
                    'sizes' => $c['sizes'] ?? ''
                ];
            }
        }

        $primaryIndex = (int)($_POST['primary_image'] ?? 0);

        // Xử lý file ảnh mới
        $uploadedFiles = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
                if ($_FILES['images']['error'][$key] === 0) {
                    $uploadedFiles[] = [
                        'name' => $_FILES['images']['name'][$key],
                        'tmp_name' => $tmp,
                        'error' => 0
                    ];
                }
            }
        }

        return $this->product->update($id, $data, $colors, $uploadedFiles, $primaryIndex, $existingImages)
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id) {
        return $this->product->delete($id)
            ? ["success" => true, "message" => "Xóa thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }
}