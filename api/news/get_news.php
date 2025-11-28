<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../app/controllers/NewsController.php';

$db = (new Database())->getConnection();
$controller = new NewsController($db);

$news = $controller->getNewsModel();

$slug = $_GET['slug'] ?? null;
if ($slug) {
    $article = $news->getBySlug($slug);
    if ($article) {
        echo json_encode(["success" => true, "data" => $article]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Không tìm thấy bài viết với slug: $slug"]);
    }
    exit;
}

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    $result = $controller->get($id);
} else {
    $result = $controller->get();
}

echo json_encode($result);
?>