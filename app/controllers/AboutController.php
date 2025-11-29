<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/About.php";

class AboutController {
    private $about;

    public function __construct($db) {
        $this->about = new About($db);
    }

    public function get() {
        $data = $this->about->getAll();
        return ["success" => true, "data" => $data];
    }

    private function handleImageUpload($oldImage = null) {
        $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return $oldImage;
        }

        if ($oldImage) {
            $oldPath = $uploadDir . basename($oldImage);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            throw new Exception("Chỉ chấp nhận file ảnh jpg, png, gif, webp");
        }

        $filename = 'about_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            return '../../assets/images/upload/' . $filename; 
        }

        return $oldImage; 
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        if (empty(trim($_POST['title'] ?? ''))) {
            return ["success" => false, "message" => "Tiêu đề không được để trống"];
        }

        try {
            $imagePath = $this->handleImageUpload(); 

            $this->about->title         = trim($_POST['title']);
            $this->about->content       = $_POST['content'] ?? '';
            $this->about->image         = $imagePath;
            $this->about->display_order = (int)($_POST['display_order'] ?? 999);
            $this->about->section_type  = $_POST['section_type'] ?? 'grid_item';

            return $this->about->create()
                ? ["success" => true, "message" => "Thêm nội dung thành công!"]
                : ["success" => false, "message" => "Thêm thất bại, vui lòng thử lại"];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function update($id) {
        $id = (int)$id;
        $item = $this->about->getById($id);
        if (!$item) {
            return ["success" => false, "message" => "Không tìm thấy nội dung"];
        }

        if (empty(trim($_POST['title'] ?? ''))) {
            return ["success" => false, "message" => "Tiêu đề không được để trống"];
        }

        try {
            $imagePath = $this->handleImageUpload($item['image']); 

            $this->about->about_id      = $id;
            $this->about->title         = trim($_POST['title']);
            $this->about->content       = $_POST['content'] ?? $item['content'];
            $this->about->image         = $imagePath;
            $this->about->display_order = (int)($_POST['display_order'] ?? $item['display_order']);
            $this->about->section_type  = $_POST['section_type'] ?? $item['section_type'];

            return $this->about->update()
                ? ["success" => true, "message" => "Cập nhật thành công!"]
                : ["success" => false, "message" => "Cập nhật thất bại"];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function delete($id) {
        $id = (int)$id;
        $item = $this->about->getById($id);
        if (!$item) {
            return ["success" => false, "message" => "Không tìm thấy nội dung"];
        }

        if ($item['image']) {
            $path = __DIR__ . '/../../public/assets/images/upload/' . basename($item['image']);
            if (file_exists($path)) @unlink($path);
        }

        return $this->about->delete($id)
            ? ["success" => true, "message" => "Xóa thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }
}