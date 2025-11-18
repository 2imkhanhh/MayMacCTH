<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Banner.php";

class BannerController {
    private $banner;

    public function __construct($db) {
        $this->banner = new Banner($db);
    }

    public function get() {
        $banners = $this->banner->get();
        return [
            "message" => "Danh sách banner",
            "success" => true,
            "status" => 200,
            "data" => $banners
        ];
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            return ["message" => "Method not allowed.", "success" => false, "status" => 405];
        }

        if (empty($_POST['title'])) {
            return ["message" => "Tiêu đề không được để trống.", "success" => false, "status" => 400];
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
            return ["message" => "Vui lòng chọn ảnh banner.", "success" => false, "status" => 400];
        }

        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ["message" => "Không thể tạo thư mục upload.", "success" => false, "status" => 500];
            }
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'banner_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $uploadPath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            return ["message" => "Upload ảnh thất bại.", "success" => false, "status" => 500];
        }

        $this->banner->title = htmlspecialchars(strip_tags($_POST['title']));
        $this->banner->image = $imageName;
        $this->banner->is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($this->banner->create()) {
            return ["message" => "Thêm banner thành công.", "success" => true, "status" => 201];
        }

        return ["message" => "Thêm banner thất bại.", "success" => false, "status" => 500];
    }


    public function update() {
        $id = $_GET['id'] ?? null;
        if (!$id) return ["message" => "ID banner không hợp lệ.", "success" => false, "status" => 400];

        $banner = $this->banner->getById($id);
        if (!$banner) return ["message" => "Không tìm thấy banner.", "success" => false, "status" => 404];

        if (empty($_POST['title'])) {
            return ["message" => "Tiêu đề không được để trống.", "success" => false, "status" => 400];
        }

        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageName = $banner['image']; // giữ ảnh cũ nếu không upload mới

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            // Xóa ảnh cũ
            $oldPath = $uploadDir . $banner['image'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }

            // Upload ảnh mới
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'banner_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $uploadPath = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                return ["message" => "Upload ảnh mới thất bại.", "success" => false, "status" => 500];
            }
        }

        $this->banner->banner_id = $id;
        $this->banner->title = htmlspecialchars(strip_tags($_POST['title']));
        $this->banner->image = $imageName;
        $this->banner->is_active = isset($_POST['is_active']) ? 1 : 0;

        return $this->banner->update()
            ? ["message" => "Cập nhật banner thành công.", "success" => true, "status" => 200]
            : ["message" => "Cập nhật banner thất bại.", "success" => false, "status" => 500];
    }


    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== "DELETE") return ["message"=>"Method not allowed","success"=>false,"status"=>405];

        $id = $_GET['id'] ?? null;
        if (!$id) return ["message"=>"ID banner không hợp lệ","success"=>false,"status"=>400];

        $banner = $this->banner->getById($id);
        if (!$banner) return ["message"=>"Không tìm thấy banner","success"=>false,"status"=>404];

        $uploadDir = __DIR__ . '/../../public/assets/images/';
        if (file_exists($uploadDir . $banner['image'])) @unlink($uploadDir . $banner['image']);

        return $this->banner->delete($id)
            ? ["message"=>"Xóa banner thành công","success"=>true,"status"=>200]
            : ["message"=>"Xóa thất bại","success"=>false,"status"=>500];
    }
}
