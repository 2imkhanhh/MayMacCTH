<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/News.php";

class NewsController
{
    private $news;
    private $uploadDir = __DIR__ . '/../../public/assets/images/upload/';

    public function __construct($db)
    {
        $this->news = new News($db);
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    private function handleThumbnailUpload($oldThumbnail = null)
    {
        if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
            return $oldThumbnail; 
        }

        $file = $_FILES['thumbnail'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed) || $file['size'] > 5 * 1024 * 1024) {
            return $oldThumbnail; 
        }

        $filename = 'thumb_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $dest = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            if ($oldThumbnail && file_exists($this->uploadDir . ltrim($oldThumbnail, '/'))) {
                @unlink($this->uploadDir . ltrim($oldThumbnail, '/'));
            }
            return 'public/assets/images/upload/' . $filename;
        }

        return $oldThumbnail; 
    }

    public function get($id = null)
    {
        if ($id) {
            $article = $this->news->getById($id);
            if (!$article) return ["success" => false, "message" => "Không tìm thấy bài viết", "status" => 404];
            return ["success" => true, "data" => $article];
        } else {
            return ["success" => true, "data" => $this->news->get()];
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed", "status" => 405];
        }

        $required = ['title', 'slug', 'content'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return ["success" => false, "message" => "$field không được để trống"];
            }
        }

        $this->news->title         = trim($_POST['title']);
        $this->news->slug          = trim($_POST['slug']);
        $this->news->author        = trim($_POST['author'] ?? 'Admin CTH');
        $this->news->is_published  = isset($_POST['is_published']) ? 1 : 0;
        $this->news->content       = $_POST['content'];
        $this->news->new_category_id   = (int)($_POST['new_category_id'] ?? 0) ?: null;
        $this->news->is_featured   = isset($_POST['is_featured']) ? 1 : 0;

        $this->news->thumbnail = $this->handleThumbnailUpload();

        return $this->news->create()
            ? ["success" => true, "message" => "Tạo bài viết thành công!"]
            : ["success" => false, "message" => "Lỗi khi tạo bài viết (slug trùng?)"];
    }

    public function update($id)
    {
        $article = $this->news->getById($id);
        if (!$article) {
            return ["success" => false, "message" => "Không tìm thấy bài viết", "status" => 404];
        }

        $this->news->id            = $id;
        $this->news->title         = trim($_POST['title'] ?? $article['title']);
        $this->news->slug          = trim($_POST['slug'] ?? $article['slug']);
        $this->news->author        = trim($_POST['author'] ?? $article['author']);
        $this->news->is_published  = isset($_POST['is_published']) ? 1 : 0;
        $this->news->content       = $_POST['content'] ?? $article['content'];
        $this->news->thumbnail     = $this->handleThumbnailUpload($article['thumbnail']);
        $this->news->new_category_id = !empty($_POST['new_category_id']) 
            ? (int)$_POST['new_category_id'] 
            : ($article['new_category_id'] ?? null);
        $this->news->is_featured = isset($_POST['is_featured']) ? 1 : ($article['is_featured'] ?? 0);

        if ($this->news->update()) {
            return ["success" => true, "message" => "Cập nhật thành công!"];
        } else {
            return ["success" => false, "message" => "Cập nhật thất bại: Slug đã tồn tại hoặc có lỗi xảy ra!"];
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return ["success" => false, "message" => "Method not allowed", "status" => 405];
        }

        $article = $this->news->getById($id);
        if ($article && !empty($article['thumbnail'])) {
            $path = $this->uploadDir . ltrim($article['thumbnail'], '/');
            if (file_exists($path)) @unlink($path);
        }

        return $this->news->delete($id)
            ? ["success" => true, "message" => "Xóa bài viết thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }

    public function getNewsModel() {
        return $this->news;
    }
}
