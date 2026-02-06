<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Product.php";

class ProductController
{
    private $product;

    public function __construct($db)
    {
        $this->product = new Product($db);
    }

    public function get()
    {
        $category_id = $_GET['category_id'] ?? null;
        $name = $_GET['name'] ?? null;
        $products = $this->product->get($category_id, $name);

        return [
            "success" => true,
            "message" => "Danh sách sản phẩm",
            "data" => $products
        ];
    }

    public function getById($id)
    {
        $product = $this->product->getById($id);
        if (!$product) {
            return ["success" => false, "message" => "Không tìm thấy", "status" => 404];
        }
        return ["success" => true, "data" => [$product]];
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        $data = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price'       => (int)($_POST['price'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'is_active'   => (int)($_POST['is_active'] ?? 0)
        ];

        if (empty($data['name']) || empty($data['category_id'])) {
            return ["success" => false, "message" => "Thiếu tên hoặc danh mục"];
        }

        $colors = [];
        $hasAnySize = false;

        foreach ($_POST['colors'] ?? [] as $cIdx => $c) {
            if (!empty($c['name'])) {
                $colorEntry = [
                    'name'     => trim($c['name']),
                    'code'     => $c['code'] ?? '#000000',
                    'variants' => []
                ];

                if (!empty($c['variants']) && is_array($c['variants'])) {
                    foreach ($c['variants'] as $v) {
                        if (!empty($v['size'])) {
                            $colorEntry['variants'][] = [
                                'size'                => trim(strtoupper($v['size'])),
                                'initial_qty'         => (int)($v['initial_qty'] ?? 0),
                                'low_stock_threshold' => (int)($v['low_stock_threshold'] ?? 10)
                            ];
                            $hasAnySize = true;
                        }
                    }
                }

                elseif (!empty($c['sizes'])) {
                    $sizes = is_array($c['sizes']) ? $c['sizes'] : explode(',', $c['sizes']);
                    foreach ($sizes as $size) {
                        $trimmed = trim(strtoupper($size));
                        if ($trimmed) {
                            $colorEntry['variants'][] = [
                                'size'                => $trimmed,
                                'initial_qty'         => 0,
                                'low_stock_threshold' => 10
                            ];
                            $hasAnySize = true;
                        }
                    }
                }

                if (!empty($colorEntry['variants'])) {
                    $colors[] = $colorEntry;
                }
            }
        }

        if (empty($colors) && !$hasAnySize) {
            return ["success" => false, "message" => "Phải có ít nhất một màu sắc hoặc một kích thước"];
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

        $primaryIndex = (int)($_POST['primary_image'] ?? 0);

        $result = $this->product->create($data, $colors, $uploadedFiles, $primaryIndex);
        return $result
            ? ["success" => true, "message" => "Thêm sản phẩm thành công!"]
            : ["success" => false, "message" => "Thêm thất bại!"];
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        $data = [
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price'       => (int)($_POST['price'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'is_active'   => (int)($_POST['is_active'] ?? 0)
        ];

        if (empty($data['name']) || empty($data['category_id'])) {
            return ["success" => false, "message" => "Thiếu thông tin bắt buộc"];
        }

        $existingImages = $_POST['existing_images'] ?? [];
        if (is_string($existingImages)) $existingImages = [$existingImages];
        $existingImages = array_map('intval', $existingImages);

        $colors = [];
        $hasAnySize = false;

        foreach ($_POST['colors'] ?? [] as $cIdx => $c) {
            if (!empty($c['name'])) {
                $variants = $c['variants'] ?? [];
                $hasVariants = !empty($variants) && is_array($variants);

                $colorEntry = [
                    'name'     => trim($c['name']),
                    'code'     => $c['code'] ?? '#000000',
                    'variants' => $hasVariants ? $variants : []
                ];

                if (!$hasVariants && !empty($c['sizes'])) {
                    $sizes = is_array($c['sizes']) ? $c['sizes'] : explode(',', $c['sizes']);
                    foreach ($sizes as $size) {
                        $colorEntry['variants'][] = [
                            'size'                => trim($size),
                            'initial_qty'         => 0,
                            'low_stock_threshold' => 10
                        ];
                    }
                }

                $colors[] = $colorEntry;

                if ($hasVariants || !empty($c['sizes'])) {
                    $hasAnySize = true;
                }
            }
        }

        if (empty($colors) && !$hasAnySize) {
            return ["success" => false, "message" => "Phải có ít nhất một màu sắc hoặc một kích thước"];
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

        $primaryImageId = null;
        $primaryImageIndex = null;

        if (isset($_POST['primary_image_id']) && is_numeric($_POST['primary_image_id']) && $_POST['primary_image_id'] > 0) {
            $primaryImageId = (int)$_POST['primary_image_id'];
        } elseif (isset($_POST['primary_image_index']) && is_numeric($_POST['primary_image_index'])) {
            $primaryImageIndex = (int)$_POST['primary_image_index'];
        }

        $result = $this->product->update(
            $id,
            $data,
            $colors,
            $uploadedFiles,
            $primaryImageIndex,
            $existingImages,
            $primaryImageId
        );

        return $result
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id)
    {
        $result = $this->product->delete($id);
        return $result
            ? ["success" => true, "message" => "Xóa thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }

    public function getAll()
    {
        $category_id = $_GET['category_id'] ?? $_GET['category'] ?? null;
        $name = $_GET['name'] ?? $_GET['q'] ?? null;

        $products = $this->product->getAll($category_id, $name);

        return [
            "success" => true,
            "message" => "Danh sách tất cả sản phẩm",
            "data" => $products
        ];
    }
}
